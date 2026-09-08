<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner;
use App\Models\Outlet;
use App\Models\Device;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\DeviceTransaction;
use App\Models\ServiceType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {

        if ($request->filled('daterange')) {
            $dateRange = explode(' - ', $request->input('daterange'));

            if (count($dateRange) === 2 && !empty($dateRange[0]) && !empty($dateRange[1])) {
                try {
                    $startDate = Carbon::createFromFormat('Y/m/d', $dateRange[0])->startOfDay();
                    $endDate = Carbon::createFromFormat('Y/m/d', $dateRange[1])->endOfDay();

                    $daterangeFilter = $dateRange[0] . ' - ' . $dateRange[1];
                } catch (\Exception $e) {
                    Log::error('Dashboard Date Range Filter Error: Failed to parse date string.', [
                        'input_daterange' => $request->input('daterange'),
                        'part_1' => $dateRange[0] ?? null,
                        'part_2' => $dateRange[1] ?? null,
                        'exception_message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $startDate = null;
                    $endDate = null;

                    // Fallback: default range 30 hari terakhir
                    $startDate = Carbon::now()->subDays(30)->startOfDay();
                    $endDate = Carbon::now()->endOfDay();
                    $daterangeFilter = $startDate->format('Y/m/d') . ' - ' . $endDate->format('Y/m/d');
                }
            } else {
                Log::warning('Dashboard Date Range Filter Warning: Daterange input malformed after explode.', [
                    'input_daterange' => $request->input('daterange'),
                    'exploded_array' => $dateRange
                ]);

                $startDate = Carbon::now()->subDays(30)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $daterangeFilter = $startDate->format('Y/m/d') . ' - ' . $endDate->format('Y/m/d');
            }
        } else {
            // DEFAULT: user belum kirim filter (7 hari terakhir)
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            $daterangeFilter = $startDate->format('Y/m/d') . ' - ' . $endDate->format('Y/m/d');
            $defaultRangeLabel = '7 Hari Terakhir';
        }

        $allowedOutletIds = getData()->getOutletIds();
        $selectedBrandIds = array_map('intval', (array) $request->input('brands', []));
        $selectedOutletIds = array_map('intval', (array) $request->input('outlets', []));
        $selectedDeviceRank = $request->input('device_rank') === 'bottom' ? 'bottom' : 'top';

        if (!empty($selectedBrandIds)) {
            $brandOutletIds = Outlet::whereIn('id', $allowedOutletIds)
                ->whereIn('owner_id', $selectedBrandIds)
                ->pluck('id')
                ->toArray();
            $allowedOutletIds = array_values(array_intersect($allowedOutletIds, $brandOutletIds));
        }
        if (!empty($selectedOutletIds)) {
            $outletIds = array_values(array_intersect($allowedOutletIds, $selectedOutletIds));
        } else {
            $outletIds = $allowedOutletIds;
        }

        $allowedDeviceCodes = Device::whereIn('outlet_id', $allowedOutletIds)
            ->orderBy('code')
            ->pluck('code')
            ->toArray();
        $manuallySelectedDeviceCodes = array_values(array_intersect(
            (array) $request->input('devices', []),
            $allowedDeviceCodes
        ));
        $rankCandidateDeviceCodes = !empty($manuallySelectedDeviceCodes)
            ? $manuallySelectedDeviceCodes
            : Device::whereIn('outlet_id', $outletIds)->orderBy('code')->pluck('code')->toArray();
        $selectedDeviceCodes = $this->rankDeviceCodes(
            $startDate,
            $endDate,
            $outletIds,
            $rankCandidateDeviceCodes,
            $selectedDeviceRank
        );

        if (empty($outletIds)) {
            Log::warning('Dashboard: No outlets found for current user. Returning empty data.');
            return view('admin.dashboard', compact(
                'startDate',
                'endDate',
                'daterangeFilter',
                'selectedBrandIds',
                'selectedOutletIds',
                'selectedDeviceCodes',
                'manuallySelectedDeviceCodes',
                'selectedDeviceRank'
            ))->with([
                'categories' => [],
                'seriesData' => [],
                'donutLabels' => [],
                'donutData' => [],
                'serviceLabels' => [],
                'serviceData' => [],
                'totalTransactions' => 0,
                'totalManualTransactions' => 0,
                'totalQrisTransactions' => 0,
                'serviceTypesDbNames' => [],
                'totalServiceCounts' => [],
                'dailyTransactionCategories' => [],
                'dailyTransactionSeriesData' => [],
                'hourlyLabels' => [],
                'hourlyData' => [],
                'multiDeviceChartCategories' => [],
                'multiDeviceChartSeriesData' => [],
                'multiDeviceChartTopSeriesData' => [],
                'multiDeviceChartBottomSeriesData' => [],
                'message' => 'Tidak ada outlet yang terkait dengan akun Anda atau data outlet tidak ditemukan.'
            ]);
        }

        $applyDeviceFilterToTransactions = function ($query) use ($selectedDeviceCodes) {
            if (!empty($selectedDeviceCodes)) {
                $query->whereExists(function ($sub) use ($selectedDeviceCodes) {
                    $sub->select(DB::raw(1))
                        ->from('device_transactions')
                        ->whereColumn('device_transactions.transaction_id', 'transactions.id')
                        ->whereIn('device_transactions.device_code', $selectedDeviceCodes);
                });
            }
        };


        // === 2. Data for Chart 1: Daily Transaction Amounts by Type (Area Chart) ===
        // Menggunakan LOWER(type) untuk memastikan grouping case-insensitive
        $transactionsForChartsQuery = Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('LOWER(type) as type_lower'), // Ubah di sini
            DB::raw('SUM(amount) as total_amount')
        )
            ->where('status', 'success')
            ->whereIn('outlet_id', $outletIds);

        if ($startDate && $endDate) {
            $transactionsForChartsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $applyDeviceFilterToTransactions($transactionsForChartsQuery);
        $transactionsForCharts = $transactionsForChartsQuery->groupBy(DB::raw('DATE(created_at)'), DB::raw('LOWER(type)')) // Ubah di sini
            ->orderBy(DB::raw('DATE(created_at)'))
            ->get();

        $dates = $transactionsForCharts->pluck('date')->unique()->sort()->values();
        $categories = $dates->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))->toArray();
        $transactionTypes = ['manual', 'qris', 'member']; // Tetap lowercase karena kita menggunakannya untuk membandingkan dengan type_lower

        $seriesData = [];
        foreach ($transactionTypes as $type) {
            $seriesData[$type] = $dates->map(
                fn($date) =>
                (int) optional(
                    $transactionsForCharts->first(fn($t) => $t->date == $date && $t->type_lower == $type) // Ubah di sini
                )->total_amount
            )->toArray();
        }

        // === 3. Data for Chart 2: Donut Chart (Transaction Type Summary) ===
        // Menggunakan CASE statement untuk pengelompokan yang konsisten dan case-insensitive
        $transactionSummaryQuery = Transaction::select(
            DB::raw("CASE
                        WHEN LOWER(type) IN ('manual', 'kasir') THEN 'Kasir / Manual'
                        WHEN LOWER(type) = 'qris' THEN 'QRIS'
                        ELSE 'Lainnya' -- Untuk menangani tipe yang tidak terduga
                    END as payment_category"),
            DB::raw('COUNT(*) as total')
        )
            ->where('status', 'success')
            ->whereIn('outlet_id', $outletIds);

        if ($startDate && $endDate) {
            $transactionSummaryQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $applyDeviceFilterToTransactions($transactionSummaryQuery);
        $transactionSummary = $transactionSummaryQuery->groupBy('payment_category')->get(); // Group by the new category

        // Siapkan data untuk donut chart dengan urutan yang diinginkan dan kategori yang sudah dikonsolidasi
        $donutLabels = [];
        $donutData = [];
        $expectedCategories = ['Kasir / Manual', 'QRIS'];

        foreach ($expectedCategories as $category) {
            $count = $transactionSummary->firstWhere('payment_category', $category)->total ?? 0;
            $donutLabels[] = $category;
            $donutData[] = $count;
        }

        // Tambahkan 'Lainnya' jika ada data yang masuk kategori ini
        $lainnyaCount = $transactionSummary->firstWhere('payment_category', 'Lainnya')->total ?? 0;
        if ($lainnyaCount > 0) {
            $donutLabels[] = 'Lainnya';
            $donutData[] = $lainnyaCount;
        }

        // Total transaksi keseluruhan (untuk donut chart), harus konsisten
        $totalTransactionsQuery = Transaction::query()
            ->where('status', 'success')
            ->whereIn('outlet_id', $outletIds);
        if ($startDate && $endDate) {
            $totalTransactionsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $applyDeviceFilterToTransactions($totalTransactionsQuery);
        $totalTransactions = $totalTransactionsQuery->count();

        // Ambil total untuk setiap kategori, menggunakan kategori yang sudah dikonsolidasi
        $totalManualTransactions = $transactionSummary->firstWhere('payment_category', 'Kasir / Manual')->total ?? 0; // Sesuaikan dengan kategori di CASE
        $totalQrisTransactions = $transactionSummary->firstWhere('payment_category', 'QRIS')->total ?? 0;

        // === 4. Data for Chart 3: Donut Chart (Service Comparison: Washer vs Dryer) ===
        $serviceTypesDbNames = ServiceType::pluck('name')->map(fn($name) => Str::snake($name))->toArray();

        $serviceComparisonQuery = DeviceTransaction::select('device_transactions.service_type', DB::raw('COUNT(*) as total'))
            ->join('transactions', 'device_transactions.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'success')
            ->whereIn('transactions.outlet_id', $outletIds)
            ->whereIn('device_transactions.service_type', $serviceTypesDbNames);

        if ($startDate && $endDate) {
            $serviceComparisonQuery->whereBetween('transactions.created_at', [$startDate, $endDate]);
        }
        if (!empty($selectedDeviceCodes)) {
            $serviceComparisonQuery->whereIn('device_transactions.device_code', $selectedDeviceCodes);
        }

        $serviceComparison = $serviceComparisonQuery->groupBy('device_transactions.service_type')->get();

        $serviceLabels = $serviceComparison->pluck('service_type')->toArray();
        $serviceData = $serviceComparison->pluck('total')->map(fn($value) => (int) $value)->toArray();

        $totalServiceCounts = [];
        foreach ($serviceTypesDbNames as $type) {
            $totalServiceCounts[$type] = $serviceComparison->firstWhere('service_type', $type)->total ?? 0;
        }

        // === Daily Device Usage (Washer vs Dryer) ===
        $dailyDeviceUsageQuery = DeviceTransaction::select(
            DB::raw('DATE(transactions.created_at) as date'),
            DB::raw('LOWER(device_transactions.service_type) as service_type'),
            DB::raw('COUNT(*) as total_transactions')
        )
            ->join('transactions', 'device_transactions.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'success')
            ->whereIn('transactions.outlet_id', $outletIds);

        if ($startDate && $endDate) {
            $dailyDeviceUsageQuery->whereBetween('transactions.created_at', [$startDate, $endDate]);
        }
        if (!empty($selectedDeviceCodes)) {
            $dailyDeviceUsageQuery->whereIn('device_transactions.device_code', $selectedDeviceCodes);
        }

        $dailyDeviceUsage = $dailyDeviceUsageQuery
            ->groupBy(DB::raw('DATE(transactions.created_at)'), DB::raw('LOWER(device_transactions.service_type)'))
            ->orderBy(DB::raw('DATE(transactions.created_at)'))
            ->get();

        $dailyTransactionCategories = $dailyDeviceUsage->pluck('date')->unique()->sort()->values()
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        $dailyWasherSeriesData = [];
        $dailyDryerSeriesData = [];

        foreach ($dailyTransactionCategories as $date) {
            $washerCount = $dailyDeviceUsage->first(function ($row) use ($date) {
                $rowDate = Carbon::parse($row->date)->format('Y-m-d');
                return $rowDate === $date && str_contains($row->service_type, 'washer');
            })->total_transactions ?? 0;

            $dryerCount = $dailyDeviceUsage->first(function ($row) use ($date) {
                $rowDate = Carbon::parse($row->date)->format('Y-m-d');
                return $rowDate === $date && str_contains($row->service_type, 'dryer');
            })->total_transactions ?? 0;

            $dailyWasherSeriesData[] = (int) $washerCount;
            $dailyDryerSeriesData[] = (int) $dryerCount;
        }

        // === NEW DATA FOR REVISION: Hourly Transaction Trend (Donut Chart) ===
        $hourlyTransactionTrendQuery = Transaction::select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('COUNT(*) as total_transactions')
        )
            ->where('status', 'success')
            ->whereIn('outlet_id', $outletIds);

        if ($startDate && $endDate) {
            $hourlyTransactionTrendQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $applyDeviceFilterToTransactions($hourlyTransactionTrendQuery);

        $hourlyTransactionTrend = $hourlyTransactionTrendQuery->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get();

        $hourlyLabels = [];
        foreach ($hourlyTransactionTrend as $item) {
            $hourlyLabels[] = sprintf('%02d:00', $item->hour);
        }
        $hourlyData = $hourlyTransactionTrend->pluck('total_transactions')->map(fn($value) => (int) $value)->toArray();

        // Call the new function to get multi-device chart data
        $dataMultiDeviceChart = $this->dataMultiDeviceChart($startDate, $endDate, $outletIds, $selectedDeviceCodes);
        $trendDeviceDonut = $this->trendDeviceDonut($startDate, $endDate, $outletIds, $selectedDeviceCodes);

        $totalMembers = Member::count();
        $totalDevices = Device::count();
        $totalOutlets = Outlet::count();
        $totalOwners = Owner::count();

        // === Summary cards (role-aware) ===
        $qrisAmountQuery = Transaction::query()
            ->where('status', 'success')
            ->whereIn('outlet_id', $outletIds)
            ->whereRaw("LOWER(type) = 'qris'");
        if ($startDate && $endDate) {
            $qrisAmountQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $applyDeviceFilterToTransactions($qrisAmountQuery);
        $qrisAmount = (int) $qrisAmountQuery->count();

        $dropoffCountQuery = Transaction::query()
            ->where('status', 'success')
            ->whereIn('outlet_id', $outletIds)
            ->whereRaw("LOWER(type) IN ('manual','kasir')");
        if ($startDate && $endDate) {
            $dropoffCountQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $applyDeviceFilterToTransactions($dropoffCountQuery);
        $dropoffCount = (int) $dropoffCountQuery->count();

        $balanceOwnerIds = Outlet::whereIn('id', $outletIds)
            ->pluck('owner_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
        $qrisBalance = (int) Owner::whereIn('id', $balanceOwnerIds)->sum('balance');

        $defaultRangeLabel = $defaultRangeLabel ?? null;

        return view('admin.dashboard', compact(
            'totalMembers',
            'totalDevices',
            'daterangeFilter',
            'defaultRangeLabel',
            'totalOutlets',
            'totalOwners',
            'qrisAmount',
            'dropoffCount',
            'qrisBalance',
            'selectedBrandIds',
            'selectedOutletIds',
            'selectedDeviceCodes',
            'manuallySelectedDeviceCodes',
            'selectedDeviceRank',
            'categories',
            'seriesData',
            'donutLabels',
            'donutData',
            'serviceLabels',
            'serviceData',
            'totalTransactions',
            'totalManualTransactions',
            'totalQrisTransactions',
            'serviceTypesDbNames',
            'totalServiceCounts',
            'dailyTransactionCategories',
            'dailyWasherSeriesData',
            'dailyDryerSeriesData',
            'hourlyLabels',
            'hourlyData',
            'dataMultiDeviceChart',
            'trendDeviceDonut',
        ));
    }

    private function trendDeviceDonut(?Carbon $startDate, ?Carbon $endDate, array $outletIds, array $selectedDeviceCodes = []): array
    {
        if (empty($outletIds)) {
            Log::warning('trendDeviceDonut: No outlets found for current owner. Returning empty data.');
            return [
                'trendDeviceDonutLabels' => [],
                'trendDeviceDonutData' => []
            ];
        }

        // Build the query to count total transactions per device
        $deviceSummaryQuery = DeviceTransaction::select(
            'device_transactions.device_code',
            DB::raw('COUNT(*) as total_transactions')
        )
            ->join('transactions', 'device_transactions.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'success')
            ->whereIn('transactions.outlet_id', $outletIds);

        if ($startDate && $endDate) {
            $deviceSummaryQuery->whereBetween('transactions.created_at', [$startDate, $endDate]);
        }
        if (!empty($selectedDeviceCodes)) {
            $deviceSummaryQuery->whereIn('device_transactions.device_code', $selectedDeviceCodes);
        }

        $deviceSummary = $deviceSummaryQuery
            ->groupBy('device_transactions.device_code')
            ->orderBy('device_transactions.device_code')
            ->get();

        $deviceSummaryMap = $deviceSummary->pluck('total_transactions', 'device_code');
        $trendDeviceDonutLabels = !empty($selectedDeviceCodes)
            ? $selectedDeviceCodes
            : $deviceSummary->pluck('device_code')->take(10)->toArray();
        $trendDeviceDonutData = collect($trendDeviceDonutLabels)
            ->map(fn($deviceCode) => (int) ($deviceSummaryMap[$deviceCode] ?? 0))
            ->toArray();

        return [
            'trendDeviceDonutLabels' => $trendDeviceDonutLabels,
            'trendDeviceDonutData' => $trendDeviceDonutData
        ];
    }

    private function dataMultiDeviceChart(?Carbon $startDate, ?Carbon $endDate, array $outletIds, array $selectedDeviceCodes = []): array
    {
        if (empty($outletIds)) {
            Log::warning('dataMultiDeviceChart: No outlets found for current owner. Returning empty data.');
            return [
                'multiDeviceChartCategories' => [],
                'multiDeviceChartSeriesData' => [],
                'multiDeviceChartTopSeriesData' => [],
                'multiDeviceChartBottomSeriesData' => []
            ];
        }

        // Build the query for device transactions over time
        $deviceTransactionsQuery = DeviceTransaction::select(
            DB::raw('DATE(transactions.created_at) as date'),
            'device_transactions.device_code',
            DB::raw('COUNT(*) as total_transactions')
        )
            ->join('transactions', 'device_transactions.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'success')
            ->whereIn('transactions.outlet_id', $outletIds);

        if ($startDate && $endDate) {
            $deviceTransactionsQuery->whereBetween('transactions.created_at', [$startDate, $endDate]);
        }
        if (!empty($selectedDeviceCodes)) {
            $deviceTransactionsQuery->whereIn('device_transactions.device_code', $selectedDeviceCodes);
        }

        $deviceTransactions = $deviceTransactionsQuery
            ->groupBy(DB::raw('DATE(transactions.created_at)'), 'device_transactions.device_code')
            ->orderBy(DB::raw('DATE(transactions.created_at)'))
            ->get();

        // Extract unique dates and use the globally ranked device set selected in the dashboard filter.
        $allDates = $deviceTransactions->pluck('date')->unique()->sort()->values();
        $allDeviceCodes = !empty($selectedDeviceCodes)
            ? collect($selectedDeviceCodes)
            : $deviceTransactions->pluck('device_code')->unique()->sort()->values()->take(10);

        // Prepare chart categories (dates)
        $multiDeviceChartCategories = $allDates->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))->toArray();

        $buildSeries = function ($deviceCodes) use ($allDates, $deviceTransactions) {
            $series = [];

            foreach ($deviceCodes as $deviceCode) {
                $data = $allDates->map(function ($date) use ($deviceTransactions, $deviceCode) {
                    $item = $deviceTransactions->first(fn($dt) => $dt->date == $date && $dt->device_code == $deviceCode);

                    return (int) ($item->total_transactions ?? 0);
                })->toArray();

                $series[] = [
                    'name' => $deviceCode,
                    'data' => $data
                ];
            }

            return $series;
        };

        $multiDeviceChartSeriesData = $buildSeries($allDeviceCodes);

        return [
            'multiDeviceChartCategories' => $multiDeviceChartCategories,
            'multiDeviceChartSeriesData' => $multiDeviceChartSeriesData,
            'multiDeviceChartTopSeriesData' => $multiDeviceChartSeriesData,
            'multiDeviceChartBottomSeriesData' => []
        ];
    }

    private function rankDeviceCodes(?Carbon $startDate, ?Carbon $endDate, array $outletIds, array $candidateDeviceCodes, string $rank): array
    {
        $candidateDeviceCodes = array_values(array_unique(array_filter($candidateDeviceCodes)));

        if (empty($outletIds) || empty($candidateDeviceCodes)) {
            return [];
        }

        $deviceTotals = collect($candidateDeviceCodes)->mapWithKeys(fn($code) => [$code => 0]);

        $summaryQuery = DeviceTransaction::select(
            'device_transactions.device_code',
            DB::raw('COUNT(*) as total_transactions')
        )
            ->join('transactions', 'device_transactions.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'success')
            ->whereIn('transactions.outlet_id', $outletIds)
            ->whereIn('device_transactions.device_code', $candidateDeviceCodes);

        if ($startDate && $endDate) {
            $summaryQuery->whereBetween('transactions.created_at', [$startDate, $endDate]);
        }

        $summaryQuery
            ->groupBy('device_transactions.device_code')
            ->get()
            ->each(function ($item) use ($deviceTotals) {
                $deviceTotals[$item->device_code] = (int) $item->total_transactions;
            });

        $sorted = $rank === 'bottom'
            ? $deviceTotals->sort()
            : $deviceTotals->sortDesc();

        return $sorted->keys()->take(10)->values()->toArray();
    }
}
