<?php

namespace App\Http\Controllers\Partner;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\DeviceTransaction;
use Illuminate\Support\Facades\Auth;
use App\Support\TransactionAccessScope;
use App\Models\QrisTransactionDetail;

class TransactionController extends Controller
{
   public function index(Request $request)
{
    $isCashier = auth()->user()->role === 'cashier';

    $query = getData()->transactions
        ->with(['owner', 'outlet', 'manualTransaction', 'qrisTransaction']) // Tambahkan manualTransaction
        ->orderBy('transactions.created_at', 'desc');

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // LOGIKA BARU UNTUK FILTER TYPE
    if ($request->filled('type')) {
        $type = $request->type;
        // Jika nilai filter adalah 'cash' atau 'non_cash',
        // filter di relasi manualTransaction
        if ($type === 'cash' || $type === 'non_cash') {
            $query->whereHas('manualTransaction', function ($q) use ($type) {
                $q->where('payment_method', $type);
            });
        } else {
            // Untuk 'qris' atau 'member', filter seperti biasa di kolom 'type'
            $query->where('type', $type);
        }
    }

    if ($request->filled('daterange')) {
        $dates = explode(' - ', $request->daterange);
        if (count($dates) == 2) {
            $startDate = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();
            $query->whereBetween('transactions.created_at', [$startDate, $endDate]);
        }
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search, $isCashier) {
            $q->where('order_id', 'like', '%' . $search . '%')
                ->orWhere('amount', 'like', '%' . $search . '%');

            if (!$isCashier) {
                $q->orWhereHas('outlet', function ($q2) use ($search) {
                    $q2->where('outlet_name', 'like', '%' . $search . '%')
                        ->orWhere('address', 'like', '%' . $search . '%');
                });
            }
        });
    }

    $totalQuery = clone $query;

    $totalTransactionsCount = (clone $totalQuery)->where('status', 'success')->count();
    $totalTransactionsAmount = (clone $totalQuery)->where('status', 'success')->sum('amount');
    $completedTransactionsCount = (clone $totalQuery)->where('status', 'success')->count();
    $showTotals = !in_array($request->status, ['pending', 'failed'], true);

    $transactions = $query->paginate(50);

    return view('partner.transactions.index', compact(
        'transactions',
        'totalTransactionsCount',
        'totalTransactionsAmount',
        'completedTransactionsCount',
        'showTotals'
    ));
}



    public function qris_transaction(Request $request)
{
    // 1. Inisialisasi Query Dasar
    $query = Transaction::with([
        'owner.user',
        'outlet',
        'qrisTransaction',
        'deviceTransactions.device'
    ])
    ->where('type', 'qris')
    ->where('status', 'success')
    ->orderBy('created_at', 'desc');

    // 2. Filter berdasarkan akses partner. Owner memakai owner_id agar transaksi
    // outlet yang sudah soft-delete tetap masuk riwayat.
    TransactionAccessScope::apply($query);

    // 3. Apply Filter Tanggal (Daterange)
    if ($request->filled('daterange')) {
        $dates = explode(' - ', $request->daterange);
        if (count($dates) === 2) {
            $startDate = trim($dates[0]);
            $endDate = trim($dates[1]);
            $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
        }
    }

    // 4. Apply Filter Pencarian (Search)
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('order_id', 'like', '%' . $search . '%')
              ->orWhere('amount', 'like', '%' . $search . '%')
              ->orWhereHas('owner', function ($q2) use ($search) {
                  $q2->where('brand_name', 'like', '%' . $search . '%');
              });
            // ... (tambah filter orWhereHas lainnya jika perlu)
        });
    }

    // --- LOGIKA HEADER (Ikut Filter) ---
    // Kita clone query agar tidak mengganggu pagination nanti
    $headerQuery = clone $query;
    $totalTransactionsCountOverall = $headerQuery->count();
    $totalTransactionsAmountOverall = $headerQuery->sum('amount');
    $completedTransactionsCount = $totalTransactionsCountOverall; // Karena query dasar sudah filter 'success'

    // 5. Eksekusi Pagination (50 data per halaman)
    $transactions = $query->paginate(50);

    // --- LOGIKA FOOTER (Hanya yang muncul di halaman ini) ---
    // Mengambil data dari collection pagination, bukan query ke DB lagi
    $totalFilteredTransactionsCount = $transactions->count();
    $totalFilteredTransactionsAmount = $transactions->sum('amount');

    // 6. Hitung Perangkat Teraktivasi (Hanya untuk data di halaman ini)
    $activatedDeviceTransactionsCount = DeviceTransaction::whereNull('activated_at')
        ->whereHas('transaction', function ($q) {
            $q->where('type', 'qris');
        })
        ->whereIn('transaction_id', $transactions->pluck('id'))
        ->count();

    return view('admin.transactions.qris', compact(
        'transactions',
        'totalTransactionsCountOverall',
        'totalTransactionsAmountOverall',
        'completedTransactionsCount',
        'activatedDeviceTransactionsCount',
        'totalFilteredTransactionsCount',
        'totalFilteredTransactionsAmount'
    ));
}
}
