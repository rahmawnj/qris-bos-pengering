<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Outlet;
use App\Models\QrisBillingPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class QrisBillingController extends Controller
{
    public function index(Request $request){return redirect()->route('partner.qris-billing.report',$request->query());}
    public function report(Request $request){$status=$request->input('status','paid');$search=$request->input('search');$period=$request->input('period');$outletIds=$this->accessibleOutletIds();$payments=QrisBillingPayment::with(['outlet.devices'])->whereIn('outlet_id',$outletIds)->when($status,fn($q)=>$q->where('status',$status))->when($period,fn($q)=>$q->whereRaw("DATE_FORMAT(period_end, '%Y-%m') = ?",[$period]))->when($search,function($q)use($search){$q->whereHas('outlet',fn($x)=>$x->where('outlet_name','like',"%{$search}%")->orWhere('code','like',"%{$search}%"));})->latest('paid_at')->get();$summary=['total'=>$payments->count(),'pending'=>$payments->where('status','pending')->count(),'paid'=>$payments->where('status','paid')->count(),'amount'=>$payments->sum('amount'),'paid_amount'=>$payments->where('status','paid')->sum('amount')];return view('partner.qris_billing.report',compact('payments','period','status','search','summary'));}
    public function show(Request $request,Outlet $outlet){$this->authorizeOutlet($outlet);if(!$outlet->qris_billing_enabled)return redirect()->route('partner.qris-billing.report')->with('error','Outlet ini tidak memiliki fitur perpanjangan QRIS.');$outlet=$this->decorateOutlet($outlet,now()->format('Y-m'));$outlet->load(['devices']);$paymentInstruction=$this->paymentInstruction();return view('partner.qris_billing.show',compact('outlet','paymentInstruction'));}
    public function showPayment(Request $request,QrisBillingPayment $payment){abort_unless(in_array($payment->outlet_id,$this->accessibleOutletIds()),403,'Akses outlet ditolak.');$payment->loadMissing(['outlet.devices']);$outlet=$this->outletFromBillingPayment($payment);abort_unless($outlet,404,'Data perpanjangan tidak ditemukan.');$paymentInstruction=$this->paymentInstruction();return view('partner.qris_billing.show',compact('outlet','paymentInstruction'));}
    public function upload(Request $request,Outlet $outlet){$this->authorizeOutlet($outlet);if(!$outlet->qris_billing_enabled)return redirect()->route('partner.qris-billing.report')->with('error','Outlet ini tidak memiliki fitur perpanjangan QRIS.');return redirect()->route('partner.qris-billing.show',$outlet);}

    public function createVirtualAccount(Request $request, Outlet $outlet)
    {
        $this->authorizeOutlet($outlet);
        if (!$outlet->qris_billing_enabled) {
            return back()->with('error', 'Outlet ini tidak memiliki fitur perpanjangan QRIS.');
        }

        $validated = $request->validate([
            'va_bank' => ['required', 'in:bca,bni,bri,permata,cimb'],
        ]);
        // QRIS billing VA dikunci ke Sandbox agar tidak membuat transaksi produksi.
        $serverKey = (string) config('services.midtrans.sandbox_server_key');
        if ($serverKey === '') {
            return back()->with('error', 'Konfigurasi MIDTRANS_SANDBOX_SERVER_KEY belum tersedia.');
        }

        $outlet->loadMissing(['devices', 'qrisBillingPayments']);
        $summary = $outlet->qrisBillingUnpaidSummary();
        if (($summary['pending_count'] ?? 0) > 0) {
            $pending = $outlet->latestQrisBillingPayment('pending');
            if ($pending && $pending->payment_method === 'virtual_account' && $pending->va_number) {
                return redirect()->route('partner.qris-billing.show', $outlet)
                    ->with('success', 'Virtual Account yang masih aktif sudah tersedia.');
            }
            return back()->with('error', 'Masih ada pembayaran yang menunggu verifikasi. Selesaikan atau tunggu pemeriksaan pembayaran sebelumnya.');
        }
        if (empty($summary['periods']) || (int) ($summary['amount'] ?? 0) < 1) {
            return back()->with('error', 'Pembayaran perpanjangan belum tersedia atau nominalnya tidak valid.');
        }

        $period = $summary['periods'][0];
        $startDate = $period['status'] === 'prepay'
            ? $outlet->qris_billing_due_date->copy()->startOfDay()
            : now()->copy()->startOfDay();
        $endDate = $startDate->copy()->addMonthNoOverflow()->startOfDay();
        $orderId = 'QBP-' . $outlet->id . '-' . now()->format('ymdHis') . '-' . Str::upper(Str::random(5));

        $payment = QrisBillingPayment::create([
            'outlet_id' => $outlet->id,
            'period_start' => $startDate->toDateString(),
            'period_end' => $endDate->toDateString(),
            'amount' => (int) $summary['amount'],
            'status' => 'pending',
            'payment_method' => 'virtual_account',
            'payment_provider' => 'midtrans',
            'gateway_order_id' => $orderId,
            'va_bank' => $validated['va_bank'],
        ]);

        try {
            // Jangan mengikuti MIDTRANS_ENVIRONMENT untuk alur ini: selalu gunakan Sandbox.
            $environment = 'sandbox';
            $baseUrl = 'https://api.sandbox.midtrans.com';
            $payload = [
                'payment_type' => 'bank_transfer',
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $payment->amount,
                ],
                'bank_transfer' => ['bank' => $validated['va_bank']],
                'customer_details' => [
                    'first_name' => mb_substr((string) $outlet->outlet_name, 0, 50),
                ],
            ];
            \\Illuminate\\Support\\Facades\\Log::info('QRIS billing Midtrans VA request.', [
                'environment' => $environment,
                'url' => $baseUrl . '/v2/charge',
                'outlet_id' => $outlet->id,
                'payment_id' => $payment->id,
                'order_id' => $orderId,
                'amount' => (int) $payment->amount,
                'bank' => $validated['va_bank'],
            ]);

            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->asJson()
                ->timeout(20)
                ->post($baseUrl . '/v2/charge', $payload);

            if (!$response->successful()) {
                throw new \RuntimeException('Gateway menolak permintaan Virtual Account: ' . ($response->json('status_message') ?? $response->body()));
            }

            $result = $response->json();

            // Catat respons gateway agar format VA yang tidak terduga bisa ditelusuri.
            // Jangan log Server Key atau request Authorization.
            \Illuminate\Support\Facades\Log::info('QRIS billing Midtrans VA response.', [
                'outlet_id' => $outlet->id,
                'payment_id' => $payment->id,
                'http_status' => $response->status(),
                'response' => $result ?? $response->body(),
            ]);

            // Midtrans dapat mengirim HTTP 200 meskipun transaksi ditolak.
            // Periksa status_code di body sebelum menganggap charge berhasil.
            $gatewayStatusCode = (string) data_get($result, 'status_code', '');
            if ($gatewayStatusCode !== '' && $gatewayStatusCode !== '201') {
                throw new \RuntimeException(
                    'Midtrans menolak pembuatan VA. status_code: '
                    . $gatewayStatusCode
                    . '; status_message: '
                    . (data_get($result, 'status_message') ?? 'tidak tersedia')
                );
            }

            // Format respons VA dapat berbeda antarbank.
            $vaNumber = data_get($result, 'va_numbers.0.va_number')
                ?? data_get($result, 'permata_va_number')
                ?? data_get($result, 'va_number')
                ?? data_get($result, 'bill_key');
            $bankName = data_get($result, 'va_numbers.0.bank')
                ?? data_get($result, 'bank')
                ?? $validated['va_bank'];

            if (!$vaNumber) {
                throw new \RuntimeException(
                    'Gateway tidak mengembalikan nomor Virtual Account. HTTP '
                    . $response->status()
                    . '; response keys: '
                    . implode(', ', array_keys(is_array($result) ? $result : []))
                    . '; status_message: '
                    . (data_get($result, 'status_message') ?? 'tidak tersedia')
                );
            }

            $payment->update([
                'va_bank' => $bankName,
                'va_number' => (string) $vaNumber,
                'gateway_transaction_id' => data_get($result, 'transaction_id'),
                'expires_at' => data_get($result, 'expiry_time')
                    ? \Carbon\Carbon::parse(data_get($result, 'expiry_time'))
                    : now()->addDay(),
            ]);
        } catch (\Throwable $e) {
            $payment->update(['status' => 'failed']);
            \Illuminate\Support\Facades\Log::error('QRIS billing VA creation failed.', [
                'outlet_id' => $outlet->id,
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            $userMessage = str_contains($e->getMessage(), 'Payment channel is not activated')
                ? 'Kanal Virtual Account bank ini belum aktif di akun Midtrans untuk environment yang digunakan. Aktifkan kanal pembayaran di dashboard Midtrans atau pilih bank yang sudah aktif.'
                : 'Virtual Account gagal dibuat. Periksa log aplikasi untuk detailnya atau pilih bank lain.';
            return back()->with('error', $userMessage);
        }

        return redirect()->route('partner.qris-billing.show', $outlet)
            ->with('success', 'Virtual Account berhasil dibuat. Bayar sebelum masa berlaku berakhir.');
    }

    private function paymentInstruction():array{return ['bank_name'=>Setting::getValue('qris_billing_payment_bank_name','Bank BCA'),'account_number'=>Setting::getValue('qris_billing_payment_account_number','8290-xxxx-xxxx'),'account_holder'=>Setting::getValue('qris_billing_payment_account_holder','Bos Pengering')];}
    private function authorizeOutlet(Outlet $outlet):void{$outletIds=$this->accessibleOutletIds();if(empty($outletIds)&&\Illuminate\Support\Facades\Auth::guard('outlet')->check())$outletIds=[\Illuminate\Support\Facades\Auth::guard('outlet')->id()];abort_unless(in_array($outlet->id,$outletIds),403,'Akses outlet ditolak.');}
    private function accessibleOutletIds():array{$outletIds=getData()->getOutletIds();if(empty($outletIds)&&\Illuminate\Support\Facades\Auth::guard('outlet')->check())$outletIds=[\Illuminate\Support\Facades\Auth::guard('outlet')->id()];return $outletIds;}
    private function outletFromBillingPayment(QrisBillingPayment $payment):?Outlet
    {
        $outlet=$payment->outlet;
        if(!$outlet)return null;
        $start=$payment->period_start;
        $end=$payment->period_end;
        if($start&&$end&&$start->format('Y-m-d')!==$end->format('Y-m-d')){
            $period=$start->translatedFormat('d F Y').' - '.$end->translatedFormat('d F Y');
        }elseif($end||$start){
            $period=($end??$start)->translatedFormat('d F Y');
        }else{
            $period=now()->translatedFormat('F Y');
        }
        $outlet->billing_period=$period;
        $outlet->billing_payment=$payment;
        $outlet->billing_due_date=$end??$start;
        $outlet->billing_amount=$payment->amount;
        $outlet->billing_status=$payment->status;
        return $outlet;
    }
    private function decorateOutlet(Outlet $outlet,string $period):Outlet
    {
        $unpaidSummary=$outlet->qrisBillingUnpaidSummary();
        $payment=collect($unpaidSummary['periods'])->pluck('payment')->filter()->first()??$outlet->qrisBillingPaymentForTargetDate($period.'-01');
        $dueDate=$unpaidSummary['oldest_due_date']??$this->dueDateForPeriod($outlet,$period);
        $isPending=$unpaidSummary['pending_count']>0;
        $isDue=$unpaidSummary['due_count']>0;
        $isPrepay=collect($unpaidSummary['periods'])->contains('status','prepay');
        if($unpaidSummary['oldest_period']&&$unpaidSummary['latest_period']&&$unpaidSummary['oldest_period']!==$unpaidSummary['latest_period']){
            $billingPeriod=$unpaidSummary['oldest_period'].' - '.$unpaidSummary['latest_period'];
        }else{
            $billingPeriod=$unpaidSummary['oldest_period']??$period;
        }
        if($unpaidSummary['count']===0){
            $billingStatus='paid';
        }elseif($isPending){
            $billingStatus='pending';
        }elseif($isDue){
            $billingStatus='due';
        }elseif($isPrepay){
            $billingStatus='prepay';
        }else{
            $billingStatus='not_due';
        }
        $outlet->billing_period=$billingPeriod;
        $outlet->billing_action_period=$unpaidSummary['latest_period']??$period;
        $outlet->billing_payment=$payment;
        $outlet->billing_due_date=$dueDate;
        $outlet->billing_amount=$unpaidSummary['amount'];
        $outlet->billing_month_count=$unpaidSummary['count'];
        $outlet->billing_unpaid_periods=$unpaidSummary['periods'];
        $outlet->billing_status=$billingStatus;
        return $outlet;
    }
    private function dueDateForPeriod(Outlet $outlet,?string $period):?Carbon{return $outlet->qris_billing_due_date;}
}
