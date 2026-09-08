<?php

namespace App\Http\Controllers\Partner;

use Carbon\Carbon;
use App\Models\Owner;
use App\Models\Device;
use App\Models\Member;
use App\Models\Outlet;
use App\Models\ServiceType;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\DeviceTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardOwnerController extends Controller
{
    public function dashboard(Request $request)
    {
       
        $ownerData = getData();
        $outletIds = $ownerData ? $ownerData->getOutletIds() : [];
        $canQueryByOwner = Auth::user()?->role === 'owner' && getBrand();

        // Jika tidak ada outlet yang ditemukan untuk owner, log peringatan
        if (empty($outletIds) && !$canQueryByOwner) {
            Log::warning('Dashboard: No outlets found for current owner. Returning empty data.');
            // Jika tidak ada outlet, tidak ada data yang bisa diambil,
            // jadi kita bisa menginisialisasi semua variabel yang akan dilewatkan ke view
            // dengan nilai default atau kosong dan langsung return.
            // Ini untuk mencegah error jika outletIds kosong dan digunakan dalam query.
            return view('partner.dashboard', compact(
                'startDate',
                'endDate' // Pass relevant filter dates if applicable
            ))->with([
                'categories' => [],
                'seriesData' => [],
                'donutLabels' => [],
                'donutData' => [],
                'serviceLabels' => [],
                'serviceData' => [],
                'totalTransactions' => 0,
                'totalMemberTransactions' => 0,
                'totalManualTransactions' => 0,
                'totalQrisTransactions' => 0,
                'serviceTypesDbNames' => [],
                'totalServiceCounts' => [],
                'dailyTransactionCategories' => [],
                'dailyTransactionSeriesData' => [],
                'hourlyLabels' => [],
                'hourlyData' => [],
                'multiDeviceChartCategories' => [], // Added for new chart
                'multiDeviceChartSeriesData' => [], // Added for new chart
                'multiDeviceChartTopSeriesData' => [],
                'multiDeviceChartBottomSeriesData' => [],
                'message' => 'Tidak ada outlet yang terkait dengan akun Anda atau data outlet tidak ditemukan.'
            ]);
        }

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
            // DEFAULT: user belum kirim filter
            $startDate = Carbon::now()->subDays(30)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            $daterangeFilter = $startDate->format('Y/m/d') . ' - ' . $endDate->format('Y/m/d');
        }

        $selectedDeviceRank = $request->input('device_rank') === 'bottom' ? 'bottom' : 'top';
        $selectedDeviceCodes = $this->rankDeviceCodes(
            $startDate,
            $endDate,
            $this->candidateDeviceCodes(),
            $selectedDeviceRank
        );

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

        $applyDeviceFilterToDeviceTransactions = function ($query) use ($selectedDeviceCodes) {
            if (!empty($selectedDeviceCodes)) {
                $query->whereIn('device_transactions.device_code', $selectedDeviceCodes);
            }
        };

        // === 2. Data for Chart 1: Daily Transaction Amounts by Type (Area Chart) ===
        // Menggunakan LOWER(type) untuk memastikan grouping case-insensitive
        $transactionsForChartsQuery = Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('LOWER(type) as type_lower'), // Ubah di sini
            DB::raw('SUM(amount) as total_amount')
        )
            ->where('status', 'success');

        $this->applyTransactionAccess($transactionsForChartsQuery);

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
                        WHEN LOWER(type) = 'member' THEN 'Member'
                        WHEN LOWER(type) IN ('manual', 'kasir') THEN 'Kasir / Manual'
                        WHEN LOWER(type) = 'qris' THEN 'QRIS'
                        ELSE 'Lainnya' -- Untuk menangani tipe yang tidak terduga
                    END as payment_category"),
            DB::raw('COUNT(*) as total')
        )
            ->where('status', 'success');

        $this->applyTransactionAccess($transactionSummaryQuery);

        if ($startDate && $endDate) {
            $transactionSummaryQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $applyDeviceFilterToTransactions($transactionSummaryQuery);
        $transactionSummary = $transactionSummaryQuery->groupBy('payment_category')->get(); // Group by the new category

        // Siapkan data untuk donut chart dengan urutan yang diinginkan dan kategori yang sudah dikonsolidasi
        $donutLabels = [];
        $donutData = [];
        $expectedCategories = ['Member', 'Kasir / Manual', 'QRIS'];

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
            ->where('status', 'success');
        $this->applyTransactionAccess($totalTransactionsQuery);
        if ($startDate && $endDate) {
            $totalTransactionsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $applyDeviceFilterToTransactions($totalTransactionsQuery);
        $totalTransactions = $totalTransactionsQuery->count();

        // Ambil total untuk setiap kategori, menggunakan kategori yang sudah dikonsolidasi
        $totalMemberTransactions = $transactionSummary->firstWhere('payment_category', 'Member')->total ?? 0;
        $totalManualTransactions = $transactionSummary->firstWhere('payment_category', 'Kasir / Manual')->total ?? 0; // Sesuaikan dengan kategori di CASE
        $totalQrisTransactions = $transactionSummary->firstWhere('payment_category', 'QRIS')->total ?? 0;

        // === 4. Data for Chart 3: Donut Chart (Service Comparison: Washer vs Dryer) ===
        $serviceTypesDbNames = ServiceType::pluck('name')->map(fn($name) => Str::snake($name))->toArray();

        $serviceComparisonQuery = DeviceTransaction::select('device_transactions.service_type', DB::raw('COUNT(*) as total'))
            ->join('transactions', 'device_transactions.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'success')
            ->whereIn('device_transactions.service_type', $serviceTypesDbNames);

        $this->applyTransactionAccess($serviceComparisonQuery, 'transactions');

        if ($startDate && $endDate) {
            $serviceComparisonQuery->whereBetween('transactions.created_at', [$startDate, $endDate]);
        }
        $applyDeviceFilterToDeviceTransactions($serviceComparisonQuery);

        $serviceComparison = $serviceComparisonQuery->groupBy('device_transactions.service_type')->get();

        $serviceLabels = $serviceComparison->pluck('service_type')->toArray();
        $serviceData = $serviceComparison->pluck('total')->map(fn($value) => (int) $value)->toArray();

        $totalServiceCounts = [];
        foreach ($serviceTypesDbNames as $type) {
            $totalServiceCounts[$type] = $serviceComparison->firstWhere('service_type', $type)->total ?? 0;
        }

        // === NEW DATA FOR REVISION: Daily Total Transactions (Bar Chart) ===
        $dailyTotalTransactionsQuery = Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total_transactions')
        )
            ->where('status', 'success');

        $this->applyTransactionAccess($dailyTotalTransactionsQuery);

        if ($startDate && $endDate) {
            $dailyTotalTransactionsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $applyDeviceFilterToTransactions($dailyTotalTransactionsQuery);

        $dailyTotalTransactions = $dailyTotalTransactionsQuery->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->get();

        $dailyTransactionCategories = $dailyTotalTransactions->pluck('date')->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))->toArray();
        $dailyTransactionSeriesData = $dailyTotalTransactions->pluck('total_transactions')->toArray();

        // === NEW DATA FOR REVISION: Hourly Transaction Trend (Donut Chart) ===
        $hourlyTransactionTrendQuery = Transaction::select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('COUNT(*) as total_transactions')
        )
            ->where('status', 'success');

        $this->applyTransactionAccess($hourlyTransactionTrendQuery);

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
        $dataMultiDeviceChart = $this->dataMultiDeviceChart($startDate, $endDate, $selectedDeviceCodes);
        $trendDeviceDonut = $this->trendDeviceDonut($startDate, $endDate, $selectedDeviceCodes);

        return view('partner.dashboard', compact(
            'daterangeFilter',
            'categories',
            'seriesData',
            'donutLabels',
            'donutData',
            'serviceLabels',
            'serviceData',
            'totalTransactions',
            'totalMemberTransactions',
            'totalManualTransactions',
            'totalQrisTransactions',
            'serviceTypesDbNames',
            'totalServiceCounts',
            'dailyTransactionCategories',
            'dailyTransactionSeriesData',
            'hourlyLabels',
            'hourlyData',
            'dataMultiDeviceChart',
            'trendDeviceDonut',
            'selectedDeviceRank',
            'selectedDeviceCodes',
        ));
    }

    private function trendDeviceDonut(?Carbon $startDate, ?Carbon $endDate, array $selectedDeviceCodes = []): array
    {
        // Re-use outletIds logic from dashboard function
        $ownerData = getData();
        if (!$ownerData || !$ownerData->outlets) {
            Log::error('trendDeviceDonut: Owner data or outlets relationship not found.');
            return [
                'trendDeviceDonutLabels' => [],
                'trendDeviceDonutData' => []
            ];
        }
        $outletIds = $ownerData->getOutletIds();

        if (empty($outletIds) && !(Auth::user()?->role === 'owner' && getBrand())) {
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
            ->where('transactions.status', 'success');

        $this->applyTransactionAccess($deviceSummaryQuery, 'transactions');

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

    private function dataMultiDeviceChart(?Carbon $startDate, ?Carbon $endDate, array $selectedDeviceCodes = []): array
    {
        // Re-use outletIds logic from dashboard function
        $ownerData = getData();
        if (!$ownerData || !$ownerData->outlets) {
            Log::error('dataMultiDeviceChart: Owner data or outlets relationship not found.');
            return [
                'multiDeviceChartCategories' => [],
                'multiDeviceChartSeriesData' => [],
                'multiDeviceChartTopSeriesData' => [],
                'multiDeviceChartBottomSeriesData' => []
            ];
        }
        $outletIds = $ownerData->getOutletIds();

        if (empty($outletIds) && !(Auth::user()?->role === 'owner' && getBrand())) {
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
            ->where('transactions.status', 'success');

        $this->applyTransactionAccess($deviceTransactionsQuery, 'transactions');

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

    private function candidateDeviceCodes(): array
    {
        $ownerData = getData();
        $outletIds = $ownerData ? $ownerData->getOutletIds() : [];

        if (!empty($outletIds)) {
            return Device::whereIn('outlet_id', $outletIds)
                ->orderBy('code')
                ->pluck('code')
                ->toArray();
        }

        $query = DeviceTransaction::select('device_transactions.device_code')
            ->join('transactions', 'device_transactions.transaction_id', '=', 'transactions.id');

        $this->applyTransactionAccess($query, 'transactions');

        return $query
            ->whereNotNull('device_transactions.device_code')
            ->distinct()
            ->orderBy('device_transactions.device_code')
            ->pluck('device_transactions.device_code')
            ->toArray();
    }

    private function rankDeviceCodes(?Carbon $startDate, ?Carbon $endDate, array $candidateDeviceCodes, string $rank): array
    {
        $candidateDeviceCodes = array_values(array_unique(array_filter($candidateDeviceCodes)));

        if (empty($candidateDeviceCodes)) {
            return [];
        }

        $deviceTotals = collect($candidateDeviceCodes)->mapWithKeys(fn($code) => [$code => 0]);

        $summaryQuery = DeviceTransaction::select(
            'device_transactions.device_code',
            DB::raw('COUNT(*) as total_transactions')
        )
            ->join('transactions', 'device_transactions.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'success')
            ->whereIn('device_transactions.device_code', $candidateDeviceCodes);

        $this->applyTransactionAccess($summaryQuery, 'transactions');

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

    private function applyTransactionAccess($query, ?string $table = null)
    {
        $user = Auth::user();
        $prefix = $table ? $table . '.' : '';

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->role === 'owner') {
            $owner = getBrand();

            return $owner
                ? $query->where($prefix . 'owner_id', $owner->id)
                : $query->whereRaw('1 = 0');
        }

        if ($user->role === 'cashier') {
            $user->loadMissing('cashier');
            $outletId = optional($user->cashier)->outlet_id;

            return $outletId
                ? $query->where($prefix . 'outlet_id', $outletId)
                : $query->whereRaw('1 = 0');
        }

        return $query->whereRaw('1 = 0');
    }









    public function device_list()
    {

        $feature = getData();
        if (!$feature->can('partner.device_list')) {
            abort(403, 'Anda tidak memiliki izin.');
        }
        $devices = getData()->devices->with(['outlet.qrisBillingPayments', 'outlet.devices'])->get();
        $outlets = getData()->outlets->get();

        return view('partner.device_list', compact('devices', 'outlets'));
    }




    public function updateDeviceServicePrices(Request $request, Device $device)
    {
        $feature = getData();
        if (!$feature->can('partner.device.service_types.update')) {
            abort(403, 'Anda tidak memiliki izin.');
        }
        // $validated = $request->validate([
        //     'prices' => 'required|array',
        //     'prices.*' => 'required|min:0',
        // ]);

        DB::beginTransaction();
        try {
            foreach ($request->prices as $serviceTypeId => $price) {
                DB::table('device_service_type')->updateOrInsert(
                    [
                        'device_id' => $device->id,
                        'service_type_id' => $serviceTypeId,
                    ],
                    [
                        'price' => $price,
                    ]
                );
            }

            DB::commit();
            return redirect()->back()->with('success', 'Harga layanan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack(); // batalkan semua kalau ada error

            // Lempar balik atau handle sesuai kebutuhan
            return back()->withErrors(['error' => 'Gagal menyimpan harga: ' . $e->getMessage()]);
        }
    }

    public function updateDeviceDetails(Request $request, Device $device)
    {
        $feature = getData();
        if (!$feature->can('partner.device.update')) {
            abort(403, 'Anda tidak memiliki izin.');
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'outlet_id' => 'required|exists:outlets,id',
        ]);

        // try {
        $device->update([
            'name' => $request->input('name'),
            'outlet_id' => $request->input('outlet_id'),
        ]);

        return redirect()->back()->with('success', 'Device details updated successfully!');
        // } catch (\Exception $e) {
        //     Log::error("Error updating device details for device ID {$device->id}: " . $e->getMessage());
        //     return redirect()->back()->with('error', 'Failed to update device details. Please try again.');
        // }
    }

    public function storeDevice(Request $request)
    {
        $feature = getData();
        if (!$feature->can('partner.device.store')) {
            abort(403, 'Anda tidak memiliki izin.');
        }


        try {
            DB::transaction(function () use ($request) {
                $data = [
                    'name'      => $request->name,
                    'outlet_id' => $request->outlet_id,
                    'device_status' => 'off', // Default status for new devices
                ];

                $device = Device::create($data);
                $device->code = Device::generateUniqueCode($device->id);
                $device->save();
            });

            return redirect()->route('partner.device.list')
                ->with('success', 'Device berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error storing new device for partner: ' . $e->getMessage());
            return redirect()->route('partner.device.list')
                ->with('error', 'Terjadi kesalahan saat menambahkan device: ' . $e->getMessage());
        }
    }

    public function destroyDevice(Device $device)
    {
        $feature = getData();
        if (!$feature->can('partner.device.destroy')) {
            abort(403, 'Anda tidak memiliki izin.');
        }

        try {
            DB::transaction(function () use ($device) {
                $device->delete();
            });

            return response()->json(['status' => 'success', 'message' => 'Device berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Error deleting device for partner: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus device: ' . $e->getMessage()], 500);
        }
    }
}
