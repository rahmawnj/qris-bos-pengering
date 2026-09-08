<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Owner;
use App\Models\Outlet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;


class TransactionController extends Controller
{
    private function getTransactionsQuery(Request $request)
    {
        $user = Auth::user();

        if (!$user->owner) {
            $outlets = Outlet::all();
            $outletIds = $outlets->pluck('id');
        } elseif ($user->owner) {
            $outlets = $user->outlets ?? collect();
            $outletIds = $outlets->pluck('id');
        } else {
            $outlets = collect();
            $outletIds = [];
        }

        $selectedOutletId = $request->get('outlet_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        return Transaction::when($selectedOutletId, function ($query) use ($selectedOutletId) {
            $query->where('outlet_id', $selectedOutletId);
        }, function ($query) use ($outletIds) {
            $query->whereIn('outlet_id', $outletIds);
        })
            ->when($startDate, function ($query) use ($startDate) {
                $query->where('time', '>=', Carbon::parse($startDate)->startOfDay());
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->where('time', '<=', Carbon::parse($endDate)->endOfDay());
            })
            ->orderBy('time', 'desc');
    }

    public function index(Request $request)
    {
        $transactions = $this->getTransactionsQuery($request)->paginate(100);
        $outlets = Outlet::all(); // Assuming you need all outlets for the filtering in the view

        return view('dashboard.transactions.index', [
            'transactions' => $transactions,
            'outlets' => $outlets,
        ]);
    }

  
}