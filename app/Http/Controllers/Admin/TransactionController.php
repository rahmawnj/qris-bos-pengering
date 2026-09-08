<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DeviceTransaction;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Support\TransactionFilters;
use App\Support\TransactionAccessScope;
use App\Support\ManualTransactionFilters;
use App\Support\QrisTransactionFilters;
use App\Services\Payments\Qris\QrisTransactionFinalizer;

class TransactionController extends Controller
{
    protected function isAdminContext(): bool
    {
        return TransactionAccessScope::isAdminContext();
    }

    protected function authorizeTransactionAccess(Transaction $transaction): void
    {
        if ($this->isAdminContext()) {
            return;
        }

        $user = Auth::user();

        if (!$user) {
            abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
        }

        if ($user->role === 'owner') {
            $user->loadMissing('owner');

            abort_unless(
                (int) $transaction->owner_id === (int) optional($user->owner)->id,
                403,
                'Anda tidak memiliki akses ke transaksi ini.'
            );

            return;
        }

        if ($user->role === 'cashier') {
            $user->loadMissing('cashier');

            abort_unless(
                (int) $transaction->outlet_id === (int) optional($user->cashier)->outlet_id,
                403,
                'Anda tidak memiliki akses ke transaksi ini.'
            );

            return;
        }

        $allowedOutletIds = array_map('intval', getData()->getOutletIds());

        abort_unless(
            in_array((int) $transaction->outlet_id, $allowedOutletIds, true),
            403,
            'Anda tidak memiliki akses ke transaksi ini.'
        );
    }

	   public function index(Request $request)
{
    $query = Transaction::with(['owner', 'outlet', 'qrisTransaction', 'memberTransaction.member.user', 'manualTransaction'])
        ->orderBy('created_at', 'desc');

    TransactionFilters::applyAdminIndexFilters($query, $request);

    $summary = (clone $query)
        ->reorder()
        ->selectRaw("
            COUNT(CASE WHEN status = 'success' THEN 1 END) as total_success_count,
            SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as total_success_amount,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as total_pending_count
        ")
        ->first();

    $totalTransactionsCount = $summary->total_success_count;
    $totalTransactionsAmount = $summary->total_success_amount;
    $completedTransactionsCount = $summary->total_success_count;
    $pendingTransactionsCount = $summary->total_pending_count;

    $transactions = $query->paginate(100);
    $pageTransactionsCount = $transactions->where('status', 'success')->count();
    $pageTransactionsAmount = $transactions->where('status', 'success')->sum('amount');
    $showTotals = !in_array($request->status, ['pending', 'failed'], true);

    return view('admin.transactions.index', compact(
        'transactions',
        'totalTransactionsCount',
        'totalTransactionsAmount',
        'completedTransactionsCount',
        'pageTransactionsCount',
        'pageTransactionsAmount',
        'showTotals'
    ));
}

	   public function qris_transaction(Request $request)
	{
	    $query = QrisTransactionFilters::baseQuery();
    [$startDate, $endDate] = QrisTransactionFilters::applyFilters($query, $request);

    // --- PERUBAHAN UNTUK HEADER (FILTERED) ---
    // Sekarang card di atas akan ikut berubah saat difilter tanggal/search
    $totalTransactionsCountOverall = $query->count();
    $totalTransactionsAmountOverall = $query->sum('amount');
    $completedTransactionsCount = $totalTransactionsCountOverall;

    // Execute Pagination
    $transactions = $query->paginate(100);

    // --- PERUBAHAN UNTUK FOOTER (PER HALAMAN) ---
    // Menggunakan collection dari pagination untuk menghitung yang tampil saja
    $totalFilteredTransactionsCount = $transactions->count(); // Jumlah record di halaman ini (maks 200)
    $totalFilteredTransactionsAmount = $transactions->sum('amount'); // Jumlah uang di halaman ini

        $activatedDeviceTransactionsCount = DeviceTransaction::whereNotNull('activated_at')
        ->whereHas('transaction', function ($q) use ($request) {
            $q->where('type', 'qris')
                ->where('status', 'success');
            TransactionAccessScope::apply($q);
            QrisTransactionFilters::applyFilters($q, $request);
        })
        ->count();

    if (!$startDate || !$endDate) {
        $minDate = (clone $query)->min('created_at');
        $maxDate = (clone $query)->max('created_at');
        $startDate = $startDate ?: ($minDate ? \Carbon\Carbon::parse($minDate)->startOfDay() : null);
        $endDate = $endDate ?: ($maxDate ? \Carbon\Carbon::parse($maxDate)->endOfDay() : null);
    }

    $daysSpan = 1;
    if ($startDate && $endDate) {
        $daysSpan = max(1, $startDate->diffInDays($endDate) + 1);
    }
    $averageDailyTransactions = $daysSpan > 0 ? ($totalTransactionsCountOverall / $daysSpan) : 0;

	    $isAdminContext = $this->isAdminContext();
	    $transactionsIndexRoute = $isAdminContext
	        ? route('admin.transactions.index', ['status' => 'success', 'type' => 'qris'])
	        : route('partner.transactions.index', ['status' => 'success', 'type' => 'qris']);
	    $qrisRoute = $isAdminContext ? route('admin.transactions.qris') : route('partner.transactions.qris');
	    $devicesRoute = $isAdminContext ? route('admin.devices.index') : route('partner.device.list');
        $transactionShowRoute = $isAdminContext ? 'admin.transactions.show' : 'partner.transactions.show';
	    $exportRoute = route('export.qris-transactions', $request->query());

	    return view('admin.transactions.qris', compact(
	        'transactions',
        'totalTransactionsCountOverall',
        'totalTransactionsAmountOverall',
        'completedTransactionsCount',
        'activatedDeviceTransactionsCount',
        'totalFilteredTransactionsCount',
        'totalFilteredTransactionsAmount',
        'averageDailyTransactions',
	        'transactionsIndexRoute',
	        'qrisRoute',
	        'devicesRoute',
            'transactionShowRoute',
	        'exportRoute'
	    ));
	}
	    public function show(Request $request, Transaction $transaction)
	    {
            $this->authorizeTransactionAccess($transaction);

	        $transaction->load([
	            'owner.user',
	            'outlet',
            'qrisTransaction',
            'memberTransaction.member.user',
            'memberTransaction.subscription',
	            'manualTransaction.service',
	            'deviceTransactions.device',
	        ]);

            $isAdminContext = $this->isAdminContext();

	        return view('admin.transactions.show', compact('transaction', 'isAdminContext'));
	    }
	    public function manual_transaction(Request $request)
	    {
        $baseQuery = ManualTransactionFilters::baseQuery();
        $filteredSummaryQuery = clone $baseQuery;

        ManualTransactionFilters::applyFilters($baseQuery, $request);
        ManualTransactionFilters::applyFilters($filteredSummaryQuery, $request);

        // --- Calculate totals for the summary cards based on the FILTERED query ---
        // These variables will now reflect the applied filters
        $totalTransactionsCountOverall = $filteredSummaryQuery->count();
        $totalTransactionsAmountOverall = $filteredSummaryQuery->sum('amount');

        // 'Transaksi Selesai (Manual)' card: This is the same as totalFilteredTransactionsCount
        // because the base query already filters for 'status = success'.
        $completedTransactionsCount = $totalTransactionsCountOverall;

        // Rata-rata pemasukan harian berdasarkan filter aktif
        $startDate = $endDate = null;
        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            if (count($dates) === 2) {
                try {
                    $startDate = Carbon::createFromFormat('Y-m-d', trim($dates[0]))->startOfDay();
                    $endDate = Carbon::createFromFormat('Y-m-d', trim($dates[1]))->endOfDay();
                } catch (\Exception $e) {
                    $startDate = null;
                    $endDate = null;
                }
            }
        }

        if (!$startDate || !$endDate) {
            $minDate = (clone $filteredSummaryQuery)->min('created_at');
            $maxDate = (clone $filteredSummaryQuery)->max('created_at');
            $startDate = $startDate ?: ($minDate ? Carbon::parse($minDate)->startOfDay() : null);
            $endDate = $endDate ?: ($maxDate ? Carbon::parse($maxDate)->endOfDay() : null);
        }

        $daysSpan = 1;
        if ($startDate && $endDate) {
            $daysSpan = max(1, $startDate->diffInDays($endDate) + 1);
        }
        $averageDailyIncome = $daysSpan > 0 ? ($totalTransactionsAmountOverall / $daysSpan) : 0;

        // 'Perangkat Dijalankan' card: Count DeviceTransactions where activated_at is NOT NULL
        // AND the associated Transaction matches all current filters.
        $activatedDeviceTransactionsCount = DeviceTransaction::whereNotNull('activated_at')
            ->whereHas('transaction', function ($q) use ($request) {
                $q->where('type', 'manual')
                    ->where('status', 'success');
                TransactionAccessScope::apply($q);
                ManualTransactionFilters::applyFilters($q, $request);
            })
            ->count();


        // Paginate the main query (which also has all filters applied)
        $transactions = $baseQuery->paginate(200);

	        $isAdminContext = $this->isAdminContext();
	        $manualRoute = $isAdminContext ? route('admin.transactions.manual') : route('partner.transactions.manual');
            $transactionShowRoute = $isAdminContext ? 'admin.transactions.show' : 'partner.transactions.show';
	        $exportRoute = route('export.manual-transactions', $request->query());

        // The footer totals should still use totalFilteredTransactionsCount/Amount
        // as they are meant to reflect the data shown in the table.
        $totalFilteredTransactionsCount = $transactions->total(); // Total from pagination
        $totalFilteredTransactionsAmount = $transactions->sum('amount'); // Sum only for current page, or re-run sum on $filteredSummaryQuery if you need total across all pages for footer.
        // If you want the footer to show the sum of all *filtered* transactions (not just current page),
        // you should use $filteredSummaryQuery->sum('amount') here as well.
        // I'll keep it as $transactions->sum('amount') for now, assuming footer means current page sum.
        // If you want the sum of ALL filtered transactions (across all pages), change the line above to:
        // $totalFilteredTransactionsAmount = $filteredSummaryQuery->sum('amount');


        return view('admin.transactions.manual', compact(
            'transactions',
            'totalTransactionsCountOverall',        // Now reflects filters
            'totalTransactionsAmountOverall',       // Now reflects filters
            'completedTransactionsCount',           // Now reflects filters
            'activatedDeviceTransactionsCount',     // Now reflects filters
            'totalFilteredTransactionsCount',       // Total for current page (or all filtered if changed above)
	            'totalFilteredTransactionsAmount',      // Total amount for current page (or all filtered if changed above)
	            'averageDailyIncome',
	            'manualRoute',
                'transactionShowRoute',
	            'exportRoute'
	        ));
	    }

    public function member_transaction(Request $request)
    {
        $query = Transaction::with([
            'owner.user',
            'outlet',
            'memberTransaction.service', // Memuat relasi memberTransaction dan service di dalamnya
            'deviceTransactions.device'
	        ])
            ->where('type', 'member') // **Perubahan utama: filter type 'member'**
            ->where('status', 'success') // Filter default untuk status success
            ->orderBy('created_at', 'desc');

        TransactionAccessScope::apply($query);

        // Apply filters (daterange and search) to the query
        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            if (count($dates) === 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]);

                $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', '%' . $search . '%')
                    ->orWhere('amount', 'like', '%' . $search . '%')
                    ->orWhereHas('owner', function ($q2) use ($search) {
                        $q2->where('brand_name', 'like', '%' . $search . '%')
                            ->orWhereHas('user', function ($q3) use ($search) {
                                $q3->where('name', 'like', '%' . $search . '%');
                            });
                    })
                    ->orWhereHas('outlet', function ($q4) use ($search) {
                        $q4->where('outlet_name', 'like', '%' . $search . '%')
                            ->orWhere('code', 'like', '%' . $search . '%')
                            ->orWhere('address', 'like', '%' . $search . '%');
                    })
                    // ->orWhereHas('memberTransaction.service', function ($q5) use ($search) {
                    //     $q5->where('service_name', 'like', '%' . $search . '%');
                    // })
                    ->orWhereHas('memberTransaction', function ($q6) use ($search) {
                        $q6->where('notes', 'like', '%' . $search . '%');
                    });
            });
        }

        // --- Perubahan di sini: Hitung total sebelum pagination untuk member transaction ---
        $totalFilteredTransactionsAmount = $query->sum('amount');
        $totalFilteredTransactionsCount = $query->count();
        // ---------------------------------------------------------------------------------

        $transactions = $query->paginate(200);

        // Statistik untuk card summary (tetap global atau sesuai filter keseluruhan untuk member)
        $memberOverallQuery = Transaction::where('type', 'member');
        TransactionAccessScope::apply($memberOverallQuery);

        $memberSuccessQuery = Transaction::where('type', 'member')->where('status', 'success');
        TransactionAccessScope::apply($memberSuccessQuery);

        $totalTransactionsCountOverall = (clone $memberOverallQuery)->count();
        $totalTransactionsAmountOverall = (clone $memberOverallQuery)->sum('amount');
        $completedTransactionsCount = (clone $memberSuccessQuery)->count();
        $activatedDeviceTransactionsCount = DeviceTransaction::whereNotNull('activated_at')
            ->whereHas('transaction', function ($q) {
                TransactionAccessScope::apply($q);
            })
            ->count();


        return view('admin.transactions.member', compact(
            'transactions',
            'totalTransactionsCountOverall',
            'totalTransactionsAmountOverall',
            'completedTransactionsCount',
            'activatedDeviceTransactionsCount',
            'totalFilteredTransactionsCount',
            'totalFilteredTransactionsAmount'
        ));
    }

    public function bypass(Request $request, Transaction $transaction)
    {
        $this->authorizeTransactionAccess($transaction);

        $request->validate([
            'proof_of_payment' => 'required|image|max:5120',
        ]);

        if ($transaction->type !== 'qris' || $transaction->status !== 'pending') {
            return redirect()->back()->with('error', 'Transaksi ini tidak dapat dibypass.');
        }

        $transaction->loadMissing('outlet.qrisBillingPayments', 'outlet.devices');
        if ($transaction->outlet?->has_overdue_billing) {
            return redirect()->back()->with('error', 'Bypass tidak diizinkan karena masa aktif QRIS outlet sudah jatuh tempo.');
        }

        if ($request->hasFile('proof_of_payment')) {
            $path = $request->file('proof_of_payment')->store('proof_of_payments', 'public');

            if ($transaction->qrisTransaction) {
                $transaction->qrisTransaction->proof_of_payment = $path;
                $transaction->qrisTransaction->bypass_status = 'inactive';
                $transaction->qrisTransaction->save();
            }
        }

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diunggah. Transaksi ini telah di-bypass dan mesin akan dapat membaca perintah untuk menyala tanpa menambah saldo owner.');
    }

    public function updateBypassStatus(Request $request, Transaction $transaction)
    {
        $this->authorizeTransactionAccess($transaction);

        if ($transaction->type !== 'qris' || !$transaction->qrisTransaction) {
            return redirect()->back()->with('error', 'Transaksi ini tidak mendukung pembaruan status bypass.');
        }

        $transaction->loadMissing('outlet.qrisBillingPayments', 'outlet.devices');
        if ($request->bypass_status === 'active' && $transaction->outlet?->has_overdue_billing) {
            return redirect()->back()->with('error', 'Bypass tidak diizinkan karena masa aktif QRIS outlet sudah jatuh tempo.');
        }

        $transaction->qrisTransaction->bypass_status = $request->bypass_status;
        $transaction->qrisTransaction->save();

        if ($request->bypass_status == 'active') {
            $transaction->qrisTransaction->bypass_activation = now();
            $transaction->qrisTransaction->save();
        }

        $statusLabels = [
            'inactive' => 'Nonaktif',
            'active' => 'Aktif',
            'activated' => 'Telah Dinyalakan'
        ];
        $label = $statusLabels[$request->bypass_status] ?? $request->bypass_status;

        return redirect()->back()->with('success', 'Status Bypass berhasil diperbarui menjadi ' . $label . '.');
    }

    public function destroy(Transaction $transaction)
    {
        try {
            if (!(Auth::guard('admin_config')->check() && !session('impersonating')) && !(Auth::check() && Auth::user()->role === 'admin')) {
                return redirect()->back()
                    ->with('error', 'Akses ditolak.');
            }

            $transaction->delete();

            return redirect()->back()
                ->with('success', 'Transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
    public function manual_verification_list(Request $request)
    {
        $isAdminContext = $this->isAdminContext();

        $query = Transaction::with(['owner', 'outlet', 'qrisTransaction'])
            ->where('type', 'qris')
            ->where('status', 'pending');

        TransactionAccessScope::apply($query);

        $tab = $request->query('tab', 'with_proof');

        if ($tab === 'with_proof') {
            $query->whereHas('qrisTransaction', function($q) {
                $q->whereNotNull('proof_of_payment');
            });
        } else {
            $query->whereHas('qrisTransaction', function($q) {
                $q->whereNull('proof_of_payment');
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('admin.transactions.verifications', compact('transactions', 'tab', 'isAdminContext'));
    }

    public function checkPaymentGateway(Transaction $transaction, QrisTransactionFinalizer $transactionFinalizer)
    {
        if (!$this->isAdminContext()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        try {
            $qrisDetail = $transaction->qrisTransaction;
            if (!$qrisDetail) {
                return response()->json(['status' => 'error', 'message' => 'Detail QRIS tidak ditemukan di transaksi ini.']);
            }

            $provider = $qrisDetail->payment_provider ?? 'xendit';

            if ($provider === 'midtrans' || $provider === 'midtrans_partner') {
                $owner = $transaction->owner;
                $isPartner = ($provider === 'midtrans_partner') || ($owner && $owner->payment_account_type === 'owner' && $owner->merchant_id);

                if ($isPartner) {
                    $partnerServerKey = config('services.midtrans_partner.server_key');
                    $partnerId = config('services.midtrans_partner.partner_id');
                    $merchantId = $owner->merchant_id ?? config('services.midtrans_partner.merchant_id');

                    if (!$partnerServerKey || !$partnerId || !$merchantId) {
                        return response()->json(['status' => 'error', 'message' => 'Kredensial Midtrans Partner (Partner Server Key/ID/Merchant ID) belum lengkap.']);
                    }
                    Log::info("Partner Server Key: {$partnerServerKey}, Partner ID: {$partnerId}, Merchant ID: {$merchantId}");

                    $baseUrl = config('services.midtrans_partner.environment') === 'production'
                        ? 'https://partner-api.midtrans.com'
                        : 'https://partner-api.stg.midtrans.com';

                        Log::info("{$baseUrl}/api/v1/core/" . rawurlencode($transaction->order_id) . "/status");

                    $response = \Illuminate\Support\Facades\Http::withHeaders([
                        'Authorization' => 'Basic ' . base64_encode($partnerServerKey . ':'),
                        'X-PARTNER-ID' => $partnerId,
                        'X-MERCHANT-ID' => $merchantId,
                        'Accept' => 'application/json',
                    ])->get("{$baseUrl}/api/v1/core/" . rawurlencode($transaction->order_id) . "/status");
                } else {
                    $serverKey = (string) config('services.midtrans.server_key');

                    if ($serverKey === '') {
                        return response()->json(['status' => 'error', 'message' => 'Midtrans server key belum dikonfigurasi.']);
                    }

                    $baseUrl = config('services.midtrans.environment') === 'production'
                        ? 'https://api.midtrans.com'
                        : 'https://api.sandbox.midtrans.com';

                    $response = \Illuminate\Support\Facades\Http::withBasicAuth($serverKey, '')
                        ->acceptJson()
                        ->get("{$baseUrl}/v2/" . rawurlencode($transaction->order_id) . "/status");
                }

                Log::info("Midtrans Status Check attempt", [
                    'order_id' => $transaction->order_id,
                    'is_partner' => $isPartner,
                    'status_code' => $response->status()
                ]);


                if ($response->successful()) {
                    $data = $response->json();
                    $status = strtolower((string) ($data['transaction_status'] ?? ''));
                    $amount = number_format((float) ($data['gross_amount'] ?? $transaction->amount), 0, ',', '.');

	                    if (in_array($status, ['settlement', 'capture'], true)) {
                            $transactionFinalizer->finalizeByOrderId(
                                orderId: $transaction->order_id,
                                status: 'success',
                                grossAmount: (int) round((float) ($data['gross_amount'] ?? $transaction->total_amount ?? $transaction->amount)),
                                gatewayReference: (string) ($data['transaction_id'] ?? $qrisDetail->gateway_reference ?? ''),
                                metadata: [
                                    'device_code' => (string) ($data['custom_field1'] ?? $qrisDetail->device_code ?? ''),
                                    'service_type' => (string) ($data['custom_field2'] ?? $qrisDetail->service_type ?? ''),
                                    'outlet_code' => (string) ($data['custom_field3'] ?? ''),
                                ],
                            );

	                        return response()->json([
	                            'status' => 'success',
	                            'message' => "Pembayaran SUKSES di Midtrans dan transaksi lokal sudah diperbarui.\nNominal: Rp {$amount}",
	                            'data' => $data,
	                        ]);
	                    }

                        if (in_array($status, ['expire', 'cancel', 'deny'], true)) {
                            $transaction->update(['status' => 'failed']);
                            
                            return response()->json([
                                'status' => 'failed',
                                'message' => "Transaksi di Midtrans berstatus: {$status}. Status lokal telah diubah menjadi FAILED.",
                                'data' => $data,
                            ]);
                        }

                    return response()->json([
                        'status' => $status === '' ? 'not_found' : 'warning',
                        'message' => $status === ''
                            ? 'Belum ada data pembayaran Midtrans untuk transaksi ini.'
                            : "Pembayaran ditemukan tapi statusnya: {$status}.",
                        'data' => $data,
                    ]);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menghubungi Midtrans (Status: ' . $response->status() . ').',
                ]);
            }

            $apiKey = (string) config('services.xendit.api_key');

            if ($apiKey === '') {
                return response()->json(['status' => 'error', 'message' => 'Xendit API key belum dikonfigurasi.']);
            }

            $qrCodeId = $qrisDetail->gateway_reference ?: $qrisDetail->payment_url;

            if (!$qrCodeId) {
                return response()->json(['status' => 'error', 'message' => 'QR ID tidak ditemukan di transaksi ini.']);
            }

            $response = \Illuminate\Support\Facades\Http::withBasicAuth($apiKey, '')
                ->withHeaders(['api-version' => '2022-07-31'])
                ->get("https://api.xendit.co/qr_codes/{$qrCodeId}/payments");

            if ($response->successful()) {
                $data = $response->json();
                $payments = isset($data['data']) ? $data['data'] : (array_is_list($data) ? $data : []);

                if (count($payments) > 0) {
                    $payment = $payments[0];
                    $amount = number_format((float) ($payment['amount'] ?? $transaction->amount), 0, ',', '.');
                    $status = (string) ($payment['status'] ?? '');

	                    if (in_array($status, ['SUCCEEDED', 'COMPLETED'], true)) {
                            $transactionFinalizer->finalizeByOrderId(
                                orderId: $transaction->order_id,
                                status: 'success',
                                grossAmount: (int) round((float) ($payment['amount'] ?? $transaction->total_amount ?? $transaction->amount)),
                                gatewayReference: (string) ($payment['id'] ?? $qrisDetail->gateway_reference ?? ''),
                                metadata: [
                                    'device_code' => (string) ($qrisDetail->device_code ?? ''),
                                    'service_type' => (string) ($qrisDetail->service_type ?? ''),
                                ],
                            );

	                        return response()->json([
	                            'status' => 'success',
	                            'message' => "Pembayaran SUKSES di Xendit dan transaksi lokal sudah diperbarui.\nNominal: Rp {$amount}",
	                            'data' => $payments,
	                        ]);
	                    }

                    return response()->json([
                        'status' => 'warning',
                        'message' => "Pembayaran ditemukan tapi statusnya: {$status}.",
                        'data' => $payments,
                    ]);
                }

                return response()->json([
                    'status' => 'not_found',
                    'message' => 'Belum ada pembayaran yang masuk untuk QR ini di Xendit.',
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghubungi Xendit (Status: ' . $response->status() . ').'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function verify_status(Request $request, Transaction $transaction)
    {
        if (!$this->isAdminContext()) {
            abort(403);
        }

        $request->validate([
            'verification_status' => 'required|in:success,failed'
        ]);

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi sudah diproses sebelumnya.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $transaction->status = $request->verification_status;

            if ($request->verification_status === 'success') {
                $feeAmount = round($transaction->amount * 0.007);
                $netAmount = $transaction->amount - $feeAmount;

                $transaction->fee_amount = $feeAmount;
                $transaction->total_amount = $transaction->amount;

                \Illuminate\Support\Facades\DB::table('owners')
                    ->where('id', $transaction->owner_id)
                    ->increment('balance', $netAmount);
            }

            $transaction->save();
            \Illuminate\Support\Facades\DB::commit();

            return back()->with('success', 'Status transaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
