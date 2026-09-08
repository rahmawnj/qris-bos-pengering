<?php

namespace App\Support;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ManualTransactionFilters
{
    public static function baseQuery(): Builder
    {
        $query = Transaction::with([
            'owner.user',
            'outlet',
            'manualTransaction.service',
            'deviceTransactions.device'
        ])
            ->where('type', 'manual')
            ->where('status', 'success')
            ->orderBy('created_at', 'desc');

        TransactionAccessScope::apply($query);

        return $query;
    }

    /**
     * Apply manual-transaction filters (daterange, search, payment_method).
     * Returns [$startDate, $endDate] if daterange is provided.
     */
    public static function applyFilters(Builder $query, Request $request): array
    {
        $startDate = null;
        $endDate = null;

        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            if (count($dates) === 2) {
                try {
                    $startDate = Carbon::createFromFormat('Y-m-d', trim($dates[0]))->startOfDay();
                    $endDate = Carbon::createFromFormat('Y-m-d', trim($dates[1]))->endOfDay();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    $startDate = null;
                    $endDate = null;
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
                    })
                    ->orWhereHas('manualTransaction', function ($q5) use ($search) {
                        $q5->where('notes', 'like', '%' . $search . '%')
                            ->orWhere('customer_name', 'like', '%' . $search . '%')
                            ->orWhere('customer_phone_number', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->filled('payment_method') && in_array($request->payment_method, ['cash', 'non_cash'])) {
            $query->whereHas('manualTransaction', function ($q) use ($request) {
                $q->where('payment_method', $request->payment_method);
            });
        }

        return [$startDate, $endDate];
    }
}
