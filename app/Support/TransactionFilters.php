<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class TransactionFilters
{
    /**
     * Apply the same filter logic used by the Admin transactions index.
     * Returns [$startDate, $endDate] for downstream use (e.g. export header).
     */
    public static function applyAdminIndexFilters(Builder $query, Request $request): array
    {
        TransactionAccessScope::apply($query);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter tipe pembayaran (cash / non_cash via manualTransaction, lainnya via type)
        $query->when($request->filled('type'), function ($q) use ($request) {
            $type = $request->type;
            if ($type === 'cash' || $type === 'non_cash') {
                $q->where('type', 'manual')
                    ->whereHas('manualTransaction', function ($subQuery) use ($type) {
                        $subQuery->where('payment_method', $type);
                    });
            } else {
                $q->where('type', $type);
            }
        });

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
                    // Ignore invalid daterange to avoid breaking the page/export
                    $startDate = null;
                    $endDate = null;
                }
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // For order_id, we check if it starts with the search string first for better index usage
                $q->where('order_id', 'like', $search . '%')
                    ->orWhere('order_id', 'like', '%' . $search . '%')
                    ->orWhere('amount', 'like', $search . '%')
                    ->orWhereHas('owner', function ($q2) use ($search) {
                        $q2->where('brand_name', 'like', '%' . $search . '%')
                            ->orWhereHas('user', function ($q3) use ($search) {
                                $q3->where('name', 'like', '%' . $search . '%');
                            });
                    })
                    ->orWhereHas('outlet', function ($q4) use ($search) {
                        $q4->where('outlet_name', 'like', '%' . $search . '%')
                            ->orWhere('code', 'like', $search . '%')
                            ->orWhere('address', 'like', '%' . $search . '%');
                    });
            });
        }

        return [$startDate, $endDate];
    }
}
