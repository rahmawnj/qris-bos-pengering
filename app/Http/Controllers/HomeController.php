<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Owner;
use App\Models\Outlet;
use App\Models\Domicile;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{


 public function dashboard_admin()
{
    $totalAdmins = User::whereDoesntHave('owner')->count();
    $totalOwners = Owner::count();
    $totalOutlets = Outlet::count();

    $todayTransaction = Transaction::whereDate('time', Carbon::today())->sum('amount');

    $dateRange = collect();
    for ($i = 9; $i >= 0; $i--) {
        $dateRange->push(Carbon::now()->subDays($i)->format('Y-m-d'));
    }

    $transactions = Transaction::selectRaw('
        DATE(time) as date,
        outlet_id,
        SUM(amount) as total_amount')->where('time', '>=', Carbon::now()->subDays(10))
        ->groupBy('date', 'outlet_id')
        ->orderBy('date')
        ->get();

    $topOutlets = Transaction::selectRaw('
        outlet_id,
        SUM(amount) as total_amount')
        ->where('time', '>=', Carbon::now()->subDays(10))
        ->groupBy('outlet_id')
        ->orderByDesc('total_amount')
        ->limit(20)
        ->pluck('outlet_id');

    $chartData = [];
    foreach ($topOutlets as $outletId) {
        $outletTransactions = $transactions->where('outlet_id', $outletId);

        $data = $dateRange->map(function ($date) use ($outletTransactions) {
            $transaction = $outletTransactions->firstWhere('date', $date);
            return [
                'x' => $date,
                'y' => $transaction ? $transaction->total_amount : 0,
            ];
        });

        $chartData[] = [
            'name' => Outlet::find($outletId)->name,
            'data' => $data->toArray(),
        ];
    }

    $dailyTotals = $dateRange->map(function ($date) {
        $totalAmount = Transaction::whereDate('time', $date)
            ->sum('amount');
        return [
            'date' => $date,
            'total' => $totalAmount,
        ];
    });

    return view('dashboard.dashboard-admin', [
        'totalAdmins' => $totalAdmins,
        'totalOwners' => $totalOwners,
        'totalOutlets' => $totalOutlets,
        'todayTransaction' => $todayTransaction,
        'chartData' => $chartData,
        'dailyTotals' => $dailyTotals,
    ]);
}


    public function dashboard_owner()
    {
        $user = auth()->user();

        $ownedOutlets = $user->outlets;


        $dateRange = collect();
        for ($i = 9; $i >= 0; $i--) {
            $dateRange->push(Carbon::now()->subDays($i)->format('Y-m-d'));
        }

        $transactions = Transaction::selectRaw('
        DATE(time) as date,
        outlet_id,
        SUM(amount) as total_amount')
            ->whereIn('outlet_id', $ownedOutlets->pluck('id'))
            ->where('time', '>=', Carbon::now()->subDays(10))
            ->groupBy('date', 'outlet_id')
            ->orderBy('date')
            ->get();

        $topOutlets = Transaction::selectRaw('
        outlet_id,
        SUM(amount) as total_amount
    ')
            ->whereIn('outlet_id', $ownedOutlets->pluck('id'))
            ->where('time', '>=', Carbon::now()->subDays(10))
            ->groupBy('outlet_id')
            ->orderByDesc('total_amount')
            ->limit(20)
            ->pluck('outlet_id');

        // Format data untuk chart
        $chartData = [];
        foreach ($topOutlets as $outletId) {
            $outletTransactions = $transactions->where('outlet_id', $outletId);

            // Siapkan data untuk outlet ini
            $data = $dateRange->map(function ($date) use ($outletTransactions) {
                $transaction = $outletTransactions->firstWhere('date', $date);
                return [
                    'x' => $date, // Tanggal di sumbu X
                    'y' => $transaction ? $transaction->total_amount : 0, // Jika tidak ada transaksi, set 0
                ];
            });

            $chartData[] = [
                'name' => Outlet::find($outletId)->name,
                'data' => $data->toArray(),
            ];
        }

        $dailyTotals = $dateRange->map(function ($date) {
            $totalAmount = Transaction::whereDate('time', $date)
                ->sum('amount');
            return [
                'date' => $date,
                'total' => $totalAmount,
            ];
        });

        return view('dashboard.dashboard-owner', [
            'chartData' => $chartData,
            'dailyTotals' => $dailyTotals,
        ]);
    }
}
