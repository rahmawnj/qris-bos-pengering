<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdminTransactionsExport;
use App\Exports\PartnerTransactionsExport;
use App\Exports\ManualTransactionsExport;
use App\Exports\QrisTransactionsExport;
use App\Support\TransactionFilters;
use App\Support\TransactionAccessScope;
use App\Support\ManualTransactionFilters;
use App\Support\QrisTransactionFilters;

class ExportController extends Controller
{
    public function adminTransactions(Request $request)
    {
        $baseQuery = Transaction::query()->with(['owner.user', 'outlet', 'manualTransaction.service', 'qrisTransaction']);
        [$startDate, $endDate] = TransactionFilters::applyAdminIndexFilters($baseQuery, $request);

        $summaryEnabled = !in_array($request->status, ['pending', 'failed'], true);
        if ($summaryEnabled) {
            $totalAmount = (clone $baseQuery)->where('status', 'success')->sum('amount');
            $totalTransactions = (clone $baseQuery)->where('status', 'success')->count();
        } else {
            $totalAmount = 0;
            $totalTransactions = 0;
        }

        // Ambil transaksi maksimal 1000 untuk ditampilkan
        $transactions = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->limit(1000)
            ->get();

        return Excel::download(
            new AdminTransactionsExport($transactions, $startDate, $endDate, $totalAmount, $totalTransactions, $summaryEnabled),
            'transactions_export_' . Carbon::now()->format('Ymd_His') . '.xlsx'
        );
    }


    public function partnerTransactions(Request $request)
    {
        $transactionsQuery = Transaction::query()->with(['owner.user', 'outlet', 'manualTransaction.service', 'qrisTransaction']);

        TransactionAccessScope::apply($transactionsQuery);

        // Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $transactionsQuery->where(function ($q) use ($search) {
                $q->where('order_id', 'like', '%' . $search . '%')
                    ->orWhere('amount', 'like', '%' . $search . '%')
                    ->orWhereHas('outlet', function ($q4) use ($search) {
                        $q4->where('outlet_name', 'like', '%' . $search . '%')
                            ->orWhere('code', 'like', '%' . $search . '%')
                            ->orWhere('address', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->filled('status')) {
            $transactionsQuery->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $type = $request->type;
            if ($type === 'cash' || $type === 'non_cash') {
                $transactionsQuery->where('type', 'manual')
                    ->whereHas('manualTransaction', function ($q) use ($type) {
                        $q->where('payment_method', $type);
                    });
            } else {
                $transactionsQuery->where('type', $type);
            }
        }

        $startDate = null;
        $endDate = null;

        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            if (count($dates) === 2) {
                $startDate = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();
                $transactionsQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        } else {
            // Default: hari ini sampai 7 hari ke belakang jika daterange kosong
            $endDate = Carbon::now()->endOfDay(); // Hari ini hingga akhir hari
            $startDate = Carbon::now()->subDays(6)->startOfDay(); // 7 hari ke belakang (termasuk hari ini)
            $transactionsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $summaryEnabled = !in_array($request->status, ['pending', 'failed'], true);
        if ($summaryEnabled) {
            $totalAmount = (clone $transactionsQuery)->where('status', 'success')->sum('amount');
            $totalTransactions = (clone $transactionsQuery)->where('status', 'success')->count();
        } else {
            $totalAmount = 0;
            $totalTransactions = 0;
        }

        $transactions = (clone $transactionsQuery)
            ->orderBy('created_at', 'desc')
            ->limit(1000)
            ->get();

            $brandName = getBrand()->brand_name;


        return Excel::download(
            new PartnerTransactionsExport($transactions, $startDate, $endDate, $totalAmount, $totalTransactions, $brandName, $summaryEnabled),
            'transactions_export_' . Carbon::now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function manualTransactions(Request $request)
    {
        $isAdminContext = TransactionAccessScope::isAdminContext();
        $brandName = $isAdminContext ? null : (getBrand()->brand_name ?? null);

        $baseQuery = ManualTransactionFilters::baseQuery();
        [$startDate, $endDate] = ManualTransactionFilters::applyFilters($baseQuery, $request);

        $totalAmount = (clone $baseQuery)->sum('amount');
        $totalTransactions = (clone $baseQuery)->count();

        $transactions = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->limit(1000)
            ->get();

        return Excel::download(
            new ManualTransactionsExport($transactions, $startDate, $endDate, $totalAmount, $totalTransactions, $brandName),
            'manual_transactions_export_' . Carbon::now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function qrisTransactions(Request $request)
    {
        $isAdminContext = TransactionAccessScope::isAdminContext();
        $brandName = $isAdminContext ? null : (getBrand()->brand_name ?? null);

        $baseQuery = QrisTransactionFilters::baseQuery();
        [$startDate, $endDate] = QrisTransactionFilters::applyFilters($baseQuery, $request);

        $totalAmount = (clone $baseQuery)->sum('amount');
        $totalTransactions = (clone $baseQuery)->count();

        $transactions = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->limit(1000)
            ->get();

        return Excel::download(
            new QrisTransactionsExport($transactions, $startDate, $endDate, $totalAmount, $totalTransactions, $brandName),
            'qris_transactions_export_' . Carbon::now()->format('Ymd_His') . '.xlsx'
        );
    }
}
