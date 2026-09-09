<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Outlet;
use App\Models\QrisBillingPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class QrisBillingController extends Controller
{
    public function index(Request $request){return redirect()->route('partner.qris-billing.report',$request->query());}
    public function report(Request $request){$status=$request->input('status','paid');$search=$request->input('search');$period=$request->input('period');$outletIds=$this->accessibleOutletIds();$payments=QrisBillingPayment::with(['outlet.devices'])->whereIn('outlet_id',$outletIds)->when($status,fn($q)=>$q->where('status',$status))->when($period,fn($q)=>$q->whereRaw("DATE_FORMAT(period_end, '%Y-%m') = ?",[$period]))->when($search,function($q)use($search){$q->whereHas('outlet',fn($x)=>$x->where('outlet_name','like',"%{$search}%")->orWhere('code','like',"%{$search}%"));})->latest('paid_at')->get();$summary=['total'=>$payments->count(),'pending'=>$payments->where('status','pending')->count(),'paid'=>$payments->where('status','paid')->count(),'amount'=>$payments->sum('amount'),'paid_amount'=>$payments->where('status','paid')->sum('amount')];return view('partner.qris_billing.report',compact('payments','period','status','search','summary'));}
    public function show(Request $request,Outlet $outlet){$this->authorizeOutlet($outlet);if(!$outlet->qris_billing_enabled)return redirect()->route('partner.qris-billing.report')->with('error','Outlet ini tidak memiliki fitur perpanjangan QRIS.');$outlet=$this->decorateOutlet($outlet,now()->format('Y-m'));$outlet->load(['devices']);$paymentInstruction=$this->paymentInstruction();return view('partner.qris_billing.show',compact('outlet','paymentInstruction'));}
    public function showPayment(Request $request,QrisBillingPayment $payment){abort_unless(in_array($payment->outlet_id,$this->accessibleOutletIds()),403,'Akses outlet ditolak.');$payment->loadMissing(['outlet.devices']);$outlet=$this->outletFromBillingPayment($payment);abort_unless($outlet,404,'Data perpanjangan tidak ditemukan.');$paymentInstruction=$this->paymentInstruction();return view('partner.qris_billing.show',compact('outlet','paymentInstruction'));}
    public function upload(Request $request,Outlet $outlet){$this->authorizeOutlet($outlet);if(!$outlet->qris_billing_enabled)return redirect()->route('partner.qris-billing.report')->with('error','Outlet ini tidak memiliki fitur perpanjangan QRIS.');$outlet=$this->decorateOutlet($outlet,now()->format('Y-m'));$outlet->load(['devices']);$period=$outlet->billing_action_period??now()->format('Y-m');$paymentInstruction=$this->paymentInstruction();return view('partner.qris_billing.upload',compact('outlet','period','paymentInstruction'));}
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
