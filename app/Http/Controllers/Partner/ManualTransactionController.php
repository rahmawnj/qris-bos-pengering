<?php

namespace App\Http\Controllers\Partner;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\DeviceTransaction;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ManualTransactionDetail;

class ManualTransactionController extends Controller
{
    public function transactions(Request $request)
    {
        // Base query for manual transactions that are successful
        $query = Transaction::with([
            'owner.user',
            'outlet',
            'manualTransaction',
            'deviceTransactions.device'
        ])
            ->where('type', 'manual')
            ->where('status', 'success') // Default filter for successful transactions
            ->orderBy('created_at', 'desc');

        // Filter by accessible outlets
        $accessibleOutletIds = getData()->outlets->pluck('id')->toArray();
        $query->whereIn('outlet_id', $accessibleOutletIds);

        // --- IMPORTANT: Clone the query *before* applying specific filters for summaries ---
        $filteredSummaryQuery = clone $query;

        // Apply filters (daterange, search, payment_method) to both the main query and the summary query clone
        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            if (count($dates) === 2) {
                try {
                    // Use Carbon to parse dates and ensure proper start/end of day for accurate range filtering
                    $startDate = Carbon::createFromFormat('Y-m-d', trim($dates[0]))->startOfDay();
                    $endDate = Carbon::createFromFormat('Y-m-d', trim($dates[1]))->endOfDay();

                    $query->whereBetween('created_at', [$startDate, $endDate]);
                    $filteredSummaryQuery->whereBetween('created_at', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    Log::error('Date Range Filter Error (Partner Manual Transactions): ' . $e->getMessage(), [
                        'daterange_input' => $request->input('daterange'),
                        'exception' => $e
                    ]);
                    // Optionally, handle invalid date range gracefully, e.g., by not applying the filter
                }
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
                    });
            });
            // Apply search to the cloned query as well
            $filteredSummaryQuery->where(function ($q) use ($search) {
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
                    });
            });
        }

        if ($request->filled('payment_method') && in_array($request->payment_method, ['cash', 'non_cash'])) {
            $query->whereHas('manualTransaction', function ($q) use ($request) {
                $q->where('payment_method', $request->payment_method);
            });
            // Apply payment method filter to the cloned query
            $filteredSummaryQuery->whereHas('manualTransaction', function ($q) use ($request) {
                $q->where('payment_method', $request->payment_method);
            });
        }

        // --- Calculate totals for the summary cards based on the FILTERED query ---
        $totalFilteredTransactionsAmount = $filteredSummaryQuery->sum('amount');
        $totalFilteredTransactionsCount = $filteredSummaryQuery->count();

        // 'Transaksi Selesai' card: Since the base query already filters for 'status = success',
        // totalFilteredTransactionsCount directly represents the count of completed (successful) transactions
        // that match all current filters.
        $completedTransactionsCount = $totalFilteredTransactionsCount;

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
        $averageDailyIncome = $daysSpan > 0 ? ($totalFilteredTransactionsAmount / $daysSpan) : 0;

        // 'Perangkat Teraktivasi' card: Count DeviceTransactions that are activated
        // AND belong to a Transaction that matches all current filters.
        $activatedDeviceTransactionsCount = DeviceTransaction::whereNotNull('activated_at')
            ->whereHas('transaction', function ($q) use ($request, $accessibleOutletIds) {
                // Ensure the transaction linked to the device transaction also meets the base criteria
                $q->where('type', 'manual')
                    ->where('status', 'success')
                    ->whereIn('outlet_id', $accessibleOutletIds);

                // Re-apply date range filter to the inner transaction query for device transactions
                if ($request->filled('daterange')) {
                    $dates = explode(' - ', $request->daterange);
                    if (count($dates) === 2) {
                        try {
                            $startDate = Carbon::createFromFormat('Y-m-d', trim($dates[0]))->startOfDay();
                            $endDate = Carbon::createFromFormat('Y-m-d', trim($dates[1]))->endOfDay();
                            $q->whereBetween('created_at', [$startDate, $endDate]);
                        } catch (\Exception $e) {
                            // Log error, but allow the main query to proceed
                        }
                    }
                }

                // Re-apply search filter to the inner transaction query for device transactions
                if ($request->filled('search')) {
                    $search = $request->search;
                    $q->where(function ($sq) use ($search) {
                        $sq->where('order_id', 'like', '%' . $search . '%')
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
                            });
                    });
                }

                // Re-apply payment method filter to the inner transaction query for device transactions
                if ($request->filled('payment_method') && in_array($request->payment_method, ['cash', 'non_cash'])) {
                    $q->whereHas('manualTransaction', function ($mq) use ($request) {
                        $mq->where('payment_method', $request->payment_method);
                    });
                }
            })
            ->count();


        // Paginate the main query
        $transactions = $query->paginate(200);

        // Pass the filtered totals to the view for the summary cards
        $totalTransactionsCountOverall = $totalFilteredTransactionsCount;
        $totalTransactionsAmountOverall = $totalFilteredTransactionsAmount;

        return view('admin.transactions.manual', compact(
            'transactions',
            'totalFilteredTransactionsCount',
            'totalFilteredTransactionsAmount',
            'completedTransactionsCount',
            'activatedDeviceTransactionsCount',
            'totalTransactionsCountOverall',
            'totalTransactionsAmountOverall',
            'averageDailyIncome'
        ));
    }
}
