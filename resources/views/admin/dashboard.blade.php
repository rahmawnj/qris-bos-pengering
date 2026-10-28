@props([
    'title' => 'Dashboard Admin',
])

@extends('layouts.dashboard.app')
@push('styles')
<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <script src="/assets/plugins/iconify/iconify-icon.min.js"></script>
    {{-- Diperlukan untuk Date Range Picker --}}
    <link rel="stylesheet" type="text/css"
        href="/assets/plugins/bootstrap-daterangepicker/daterangepicker.css" />

    <style>
        /* Tambahkan style jika diperlukan, misal untuk input filter */
        .filter-row {
            margin-bottom: 20px;
        }

        .input-group-text {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        @media (max-width: 1200px) {
            .stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }
        }

        .dashboard-chart-loading {
            position: relative;
            min-height: 120px;
        }

        .dashboard-chart-loading::after {
            content: "Memuat grafik...";
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: inherit;
            color: #6c757d;
            font-size: .875rem;
            background: linear-gradient(90deg, #f8f9fa 25%, #eef1f4 50%, #f8f9fa 75%);
            background-size: 200% 100%;
            animation: dashboard-chart-shimmer 1.4s ease-in-out infinite;
            border-radius: 8px;
        }

        @keyframes dashboard-chart-shimmer {
            from { background-position: 100% 0; }
            to { background-position: -100% 0; }
        }

        .stat-card {
            position: relative;
            border-radius: 14px;
            overflow: hidden;
            color: #fff;
            padding: 18px 20px;
            min-height: 120px;
            box-shadow: 0 10px 22px rgba(0, 0, 0, 0.12);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .stat-card .stat-title {
            font-weight: 600;
            font-size: 12px;
            letter-spacing: .6px;
            text-transform: uppercase;
            opacity: .9;
        }

        .stat-card .stat-value {
            font-size: 28px;
            font-weight: 800;
            line-height: 1.1;
        }

        .stat-card .stat-sub {
            font-size: 12px;
            opacity: .85;
            border-top: 1px solid rgba(255, 255, 255, 0.25);
            padding-top: 8px;
        }

        .stat-icon {
            position: absolute;
            right: 14px;
            top: 12px;
            font-size: 46px;
            opacity: .18;
        }

        .stat-orange {
            background: linear-gradient(135deg, #ff9f1c 0%, #ff7a00 100%);
        }

        .stat-green {
            background: linear-gradient(135deg, #1db954 0%, #0ea64f 100%);
        }

        .stat-blue {
            background: linear-gradient(135deg, #2f80ff 0%, #1b6bff 100%);
        }

        .stat-red {
            background: linear-gradient(135deg, #ff4d5a 0%, #e11b2f 100%);
        }
    </style>
@endpush

@section('content')


    @php
        $isAdminContext = !session('impersonating') &&
            (Auth::guard('admin_config')->check() || (Auth::check() && Auth::user()->role === 'admin'));
        $transactionsLink = $isAdminContext ? route('admin.transactions.index') : route('partner.transactions.index');
    @endphp

    @if (request()->routeIs('partner.dashboard') && isset($qrisBillingOutlets) && $qrisBillingOutlets->isNotEmpty())
        @foreach ($qrisBillingOutlets as $billingOutlet)
            @php
                $alertType = $billingOutlet->billing_alert_type;
                $remainingDays = $billingOutlet->billing_remaining_days;
                $alertClass = $alertType === 'warning' ? 'alert-warning' : 'alert-danger';
                $billingUrl = route('partner.qris-billing.show', $billingOutlet->id);
            @endphp
            <div class="alert {{ $alertClass }} mb-3 qris-billing-alert" role="alert">
                <div>
                    <strong>{{ $billingOutlet->outlet_name }} — Masa Aktif QRIS</strong><br>
                    @if ($alertType === 'expired')
                        Masa aktif outlet sudah habis atau belum ditentukan. Silakan lakukan perpanjangan masa aktif sekarang.
                    @else
                        Masa aktif outlet akan berakhir dalam {{ $remainingDays }} hari
                        ({{ $billingOutlet->qris_billing_due_date->translatedFormat('d F Y') }}).
                    @endif
                </div>

                <div class="mt-3">
                    @if ($alertType === 'warning')
                        <button type="button" class="btn btn-warning btn-sm qris-billing-alert-dismiss">
                            Mengerti
                        </button>
                    @else
                        <a href="{{ $billingUrl }}" class="btn btn-danger btn-sm">
                            Perpanjang Masa Aktif
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    <div class="stat-grid mb-4" style="margin-top: 24px;">
        <div class="stat-card stat-orange">
            <div class="stat-title">Omzet</div>
            <div class="stat-value">Rp {{ number_format($qrisBalance ?? 0, 0, ',', '.') }}</div>
            <div class="stat-sub">Total omzet berdasarkan filter tanggal &amp; outlet</div>
            <div class="stat-icon"><i class="fa fa-wallet"></i></div>
        </div>
        <div class="stat-card stat-green">
            <div class="stat-title">Transaksi QRIS</div>
            <div class="stat-value">{{ number_format($qrisAmount ?? 0, 0, ',', '.') }}</div>
            <div class="stat-sub">Jumlah transaksi QRIS</div>
            <div class="stat-icon"><i class="fa fa-qrcode"></i></div>
        </div>
        <div class="stat-card stat-blue">
            <div class="stat-title">Drop Off</div>
            <div class="stat-value">{{ $dropoffCount ?? 0 }}</div>
            <div class="stat-sub">Total transaksi manual</div>
            <div class="stat-icon"><i class="fa fa-hand-holding-usd"></i></div>
        </div>
        <div class="stat-card stat-red">
            <div class="stat-title">Total Transaksi</div>
            <div class="stat-value">{{ $totalTransactions ?? 0 }}</div>
            <div class="stat-sub">Semua transaksi sukses</div>
            <div class="stat-icon"><i class="fa fa-receipt"></i></div>
        </div>
    </div>

    <div class="card p-3 mb-4">
    <form action="{{ request()->url() }}" method="GET">

        @php
            $selectedBrandIdsList = request('brands') ?? [];
            $selectedOutletIdsList = request('outlets') ?? [];
            $selectedDeviceCodesList = request('devices') ?? [];
            $activeDeviceRank = $selectedDeviceRank ?? request('device_rank', 'top');

            $allowedOutletIds = getData()->getOutletIds();

            $availableBrandsList = \App\Models\Owner::whereHas('outlets', function ($q) use ($allowedOutletIds) {
                $q->whereIn('id', $allowedOutletIds);
            })->orderBy('brand_name')->get();

            $brandOutletIds = [];
            if (!empty($selectedBrandIdsList)) {
                $brandOutletIds = \App\Models\Outlet::whereIn('id', $allowedOutletIds)
                    ->whereIn('owner_id', $selectedBrandIdsList)
                    ->pluck('id')
                    ->toArray();
            }

            $availableOutletsList = $availableOutlets ?? \App\Models\Outlet::whereIn('id', $allowedOutletIds)
                ->when(!empty($selectedBrandIdsList), function ($q) use ($selectedBrandIdsList) {
                    $q->whereIn('owner_id', $selectedBrandIdsList);
                })
                ->orderBy('outlet_name')
                ->get();

            $deviceOutletIds = !empty($selectedOutletIdsList)
                ? $selectedOutletIdsList
                : (!empty($selectedBrandIdsList) ? $brandOutletIds : $allowedOutletIds);

            $availableDevicesList = $availableDevices ?? \App\Models\Device::whereIn('outlet_id', $deviceOutletIds)
                ->orderBy('code')
                ->get();

            $selectedBrandCount = is_array($selectedBrandIdsList) ? count($selectedBrandIdsList) : 0;
            $selectedOutletCount = is_array($selectedOutletIdsList) ? count($selectedOutletIdsList) : 0;
            $selectedDeviceCount = is_array($selectedDeviceCodesList) ? count($selectedDeviceCodesList) : 0;
        @endphp

        <div class="d-flex flex-wrap align-items-end gap-3">

            <div style="flex: 1; min-width: 50px;">
                <label for="dashboard-daterange" class="form-label mb-1 fw-bold">Filter Tanggal:</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                    <input type="text" name="daterange" autocomplete="off" class="form-control"
                           id="dashboard-daterange" autocorrect="off" autocapitalize="off" spellcheck="false"
                           value="{{ request('daterange') ?: ($daterangeFilter ?? '') }}"
                           placeholder="Pilih Rentang Waktu">
                </div>
            </div>

            <div style="min-width: 150px;">
                <label class="form-label mb-1 fw-bold">Filter Brand:</label>
                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm dropdown-toggle w-100" type="button" id="filterBrandBtn"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Brand{{ $selectedBrandCount ? ' (' . $selectedBrandCount . ')' : '' }}
                    </button>
                    <div class="dropdown-menu p-3" aria-labelledby="filterBrandBtn" style="max-height: 240px; overflow:auto; min-width: 200px;">
                        @forelse ($availableBrandsList as $brand)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="brands[]"
                                    value="{{ $brand->id }}" id="brand_{{ $brand->id }}"
                                    {{ in_array($brand->id, $selectedBrandIdsList) ? 'checked' : '' }}>
                                <label class="form-check-label" for="brand_{{ $brand->id }}">
                                    {{ $brand->brand_name }}
                                </label>
                            </div>
                        @empty
                            <small class="text-muted">Brand tidak tersedia</small>
                        @endforelse
                    </div>
                </div>
            </div>

            <div style="min-width: 150px;">
                <label class="form-label mb-1 fw-bold">Filter Outlet:</label>
                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm dropdown-toggle w-100" type="button" id="filterOutletBtn"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Outlet{{ $selectedOutletCount ? ' (' . $selectedOutletCount . ')' : '' }}
                    </button>
                    <div class="dropdown-menu p-3" aria-labelledby="filterOutletBtn" style="max-height: 240px; overflow:auto; min-width: 200px;">
                        @forelse ($availableOutletsList as $outlet)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="outlets[]"
                                    value="{{ $outlet->id }}" id="outlet_{{ $outlet->id }}"
                                    {{ in_array($outlet->id, $selectedOutletIdsList) ? 'checked' : '' }}>
                                <label class="form-check-label" for="outlet_{{ $outlet->id }}">
                                    {{ $outlet->outlet_name }}
                                </label>
                            </div>
                        @empty
                            <small class="text-muted">Outlet tidak tersedia</small>
                        @endforelse
                    </div>
                </div>
            </div>

            <div style="min-width: 150px;">
                <label class="form-label mb-1 fw-bold">Filter Device:</label>
                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm dropdown-toggle w-100" type="button" id="filterDeviceBtn"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Device{{ $selectedDeviceCount ? ' (' . $selectedDeviceCount . ')' : '' }}
                    </button>
                    <div class="dropdown-menu p-3" aria-labelledby="filterDeviceBtn" style="max-height: 240px; overflow:auto; min-width: 200px;">
                        @forelse ($availableDevicesList as $device)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="devices[]"
                                    value="{{ $device->code }}" id="device_{{ $device->code }}"
                                    {{ in_array($device->code, $selectedDeviceCodesList) ? 'checked' : '' }}>
                                <label class="form-check-label" for="device_{{ $device->code }}">
                                    {{ $device->code }}
                                </label>
                            </div>
                        @empty
                            <small class="text-muted">Device tidak tersedia</small>
                        @endforelse
                    </div>
                </div>
            </div>

            <div style="min-width: 150px;">
                <label for="device_rank" class="form-label mb-1 fw-bold">Urutan Device:</label>
                <select name="device_rank" id="device_rank" class="form-select form-select-sm">
                    <option value="top" {{ $activeDeviceRank === 'top' ? 'selected' : '' }}>Terbanyak</option>
                    <option value="bottom" {{ $activeDeviceRank === 'bottom' ? 'selected' : '' }}>Tersedikit</option>
                </select>
            </div>

        </div>

        <hr class="my-3">

        <div class="d-flex justify-content-between align-items-center">
            <div class="text-truncate" style="max-width: 260px;">
                <small class="text-muted">Periode Aktif:</small>
                <span class="fw-bold text-primary ms-1">
                    @php
                        $daterangeText = request('daterange');
                        if ($daterangeText && str_contains($daterangeText, ' - ')) {
                            [$dStart, $dEnd] = explode(' - ', $daterangeText);
                            try {
                                $dStartFmt = \Carbon\Carbon::createFromFormat('Y/m/d', $dStart)->format('d/m/y');
                                $dEndFmt = \Carbon\Carbon::createFromFormat('Y/m/d', $dEnd)->format('d/m/y');
                                $daterangeText = $dStartFmt . ' - ' . $dEndFmt;
                            } catch (\Exception $e) {
                                // keep original
                            }
                        }
                    @endphp
                    {{ $daterangeText ?: ($daterangeFilter ?? 'Semua Waktu') }}
                </span>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ request()->url() }}" class="btn btn-dark btn-sm px-3">
                    <i class="fa fa-redo me-1"></i> Reset
                </a>
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="fa fa-filter me-1"></i> Terapkan Filter
                </button>
            </div>
        </div>

    </form>
</div>

    <div class="row">
        <div class="col-xl-8">
            <div class="widget-chart with-sidebar">
                <div class="widget-chart-content bg-white">
                    <h4 class="chart-title">
                        Analisis Transaksi
                        <small>Transaksi per Tanggal (Jumlah)</small>
                    </h4>
                    <div id="category-line" class="widget-chart-full-width" style="height: 260px;" data-dashboard-chart="true"></div>
                </div>
                <div class="widget-chart-sidebar bg-white">
                    <div class="chart-number">
                        {{ $totalTransactions ?? 0 }} {{-- Added ?? 0 for safety --}}
                        <small>Jumlah Transaksi</small>
                    </div>
                    <div class="flex-grow-1 d-flex align-items-center">
                        <div id="perbandingan-donut" style="height: 180px" data-dashboard-chart="true"></div>
                    </div>
                    {{-- Removed manual chart-legend as ApexCharts will generate its own for perbandingan-donut --}}
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="panel panel-default h-100 d-flex flex-column" data-sortable-id="index-1">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        Kategori Pembayaran
                    </h4>
                </div>
                <div id="transaction-summary" style="height: 170px;" data-dashboard-chart="true"></div>
                <div class="list-group mt-auto">

                    <a href="javascript:;"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center text-ellipsis">
                        1. Drop Off
                        <span class="badge bg-green fs-10px">{{ $totalManualTransactions ?? 0 }}</span>
                        {{-- Added ?? 0 for safety --}}
                    </a>
                    <a href="javascript:;"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center text-ellipsis">
                        2. Self Service (QRIS)
                        <span class="badge bg-gray-600 fs-10px">{{ $totalQrisTransactions ?? 0 }}</span>
                        {{-- Added ?? 0 for safety --}}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- NEW ROW FOR REVISION --}}
    <div class="row mt-4">
        <div class="col-xl-6">
            <div class="panel panel-default" data-sortable-id="index-2">
                <div class="panel-heading">
                    <h4 class="panel-title">Total Transaksi Harian</h4>
                </div>
                <div class="panel-body">
                    <div id="daily-total-transactions-chart" style="height: 300px;" data-dashboard-chart="true"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="panel panel-default" data-sortable-id="index-3">
                <div class="panel-heading">
                    <h4 class="panel-title">Tren Jam Transaksi</h4>
                </div>
                <div class="panel-body">
                    <div id="hourly-transaction-trend-chart" style="height: 300px;" data-dashboard-chart="true"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-8">
            <div class="panel panel-default" data-sortable-id="index-4">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        Tren Transaksi Multi-Perangkat Harian
                        ({{ ($selectedDeviceRank ?? 'top') === 'bottom' ? 'Tersedikit' : 'Terbanyak' }})
                    </h4>
                </div>
                <div class="panel-body">
                    <div id="multi-device-line-chart" style="height: 320px;" data-dashboard-chart="true"></div>
                </div>
            </div>
        </div>

        <!-- Kanan: Donut Chart (Lebar 4 kolom) -->
        <div class="col-xl-4">
            <div class="panel panel-default" data-sortable-id="index-5">
                <div class="panel-heading">
                    <h4 class="panel-title">Total Transaksi per Perangkat</h4>
                </div>
                <div class="panel-body">
                    <div id="device-trend-donut-chart" style="height: 300px;" data-dashboard-chart="true"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')

    <script src="{{ asset('assets/plugins/apexcharts/dist/apexcharts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.qris-billing-alert-dismiss').forEach(function (button) {
                button.addEventListener('click', function () {
                    button.closest('.qris-billing-alert')?.remove();
                });
            });

            const chartElements = Array.from(document.querySelectorAll('[data-dashboard-chart="true"]'));
            chartElements.forEach(function (element) {
                element.classList.add('dashboard-chart-loading');
            });

            const selectedDeviceCodes = @json(array_values($selectedDeviceCodes ?? []));
            const baseUrl = new URL(window.location.href);
            const baseOptions = {
                chart: {
                    toolbar: { show: false },
                    animations: { enabled: false },
                    fontFamily: 'inherit'
                },
                noData: { text: 'Belum ada data' }
            };
            const colors = ['#348fe2', '#00acac', '#f59c1a'];

            function renderChart(id, options) {
                const element = document.getElementById(id);
                if (!element || typeof ApexCharts === 'undefined') {
                    return Promise.resolve();
                }

                element.innerHTML = '';
                return Promise.resolve(new ApexCharts(element, options).render());
            }

            function markChartGroupFailed(ids) {
                ids.forEach(function (id) {
                    const element = document.getElementById(id);
                    if (!element) return;
                    element.classList.remove('dashboard-chart-loading');
                    element.innerHTML = '<div class="text-muted small text-center p-3">Grafik gagal dimuat. Coba muat ulang halaman.</div>';
                });
            }

            const jobs = [
                {
                    chart: 'hourly',
                    ids: ['hourly-transaction-trend-chart'],
                    render: function (data) {
                        return renderChart('hourly-transaction-trend-chart', {
                            ...baseOptions,
                            chart: { ...baseOptions.chart, type: 'bar', height: 300 },
                            series: [{ name: 'Transaksi', data: data.hourlyData || [] }],
                            xaxis: { categories: data.hourlyLabels || [] },
                            plotOptions: { bar: { columnWidth: '55%', borderRadius: 3 } },
                            colors: [colors[0]]
                        });
                    }
                },
                {
                    chart: 'transactions',
                    ids: ['category-line', 'perbandingan-donut', 'transaction-summary'],
                    render: function (data) {
                        const transactionTotal = Number(data.totalTransactions || 0).toLocaleString('id-ID');
                        const transactionCount = document.querySelector('.widget-chart-sidebar .chart-number');
                        if (transactionCount) {
                            const small = transactionCount.querySelector('small');
                            transactionCount.textContent = transactionTotal + ' ';
                            if (small) transactionCount.appendChild(small);
                        }

                        const totalStat = document.querySelector('.stat-card.stat-red .stat-value');
                        if (totalStat) totalStat.textContent = transactionTotal;

                        const summaryBadges = document.querySelectorAll('.list-group .badge');
                        if (summaryBadges.length >= 2) {
                            summaryBadges[0].textContent = Number(data.totalManualTransactions || 0).toLocaleString('id-ID');
                            summaryBadges[1].textContent = Number(data.totalQrisTransactions || 0).toLocaleString('id-ID');
                        }

                        return Promise.all([
                            renderChart('category-line', {
                                ...baseOptions,
                                chart: { ...baseOptions.chart, type: 'area', height: 260, stacked: false },
                                series: [
                                    { name: 'Manual', data: (data.seriesData && data.seriesData.manual) || [] },
                                    { name: 'QRIS', data: (data.seriesData && data.seriesData.qris) || [] },
                                    { name: 'Member', data: (data.seriesData && data.seriesData.member) || [] }
                                ],
                                xaxis: { categories: data.categories || [] },
                                colors: colors,
                                dataLabels: { enabled: false },
                                stroke: { curve: 'smooth', width: 2 },
                                fill: { type: 'gradient', gradient: { opacityFrom: .35, opacityTo: .05 } }
                            }),
                            renderChart('perbandingan-donut', {
                                ...baseOptions,
                                chart: { ...baseOptions.chart, type: 'donut', height: 180 },
                                labels: data.donutLabels || [],
                                series: data.donutData || [],
                                legend: { position: 'bottom' },
                                colors: colors
                            }),
                            renderChart('transaction-summary', {
                                ...baseOptions,
                                chart: { ...baseOptions.chart, type: 'donut', height: 170 },
                                labels: data.donutLabels || [],
                                series: data.donutData || [],
                                legend: { show: false },
                                dataLabels: { enabled: true },
                                colors: colors
                            })
                        ]);
                    }
                },
                {
                    chart: 'daily',
                    ids: ['daily-total-transactions-chart'],
                    render: function (data) {
                        return renderChart('daily-total-transactions-chart', {
                            ...baseOptions,
                            chart: { ...baseOptions.chart, type: 'bar', height: 300 },
                            series: [
                                { name: 'Washer', data: data.dailyWasherSeriesData || [] },
                                { name: 'Dryer', data: data.dailyDryerSeriesData || [] }
                            ],
                            xaxis: { categories: data.dailyTransactionCategories || [] },
                            plotOptions: { bar: { columnWidth: '55%', borderRadius: 3 } },
                            colors: colors
                        });
                    }
                },
                {
                    chart: 'device-trend',
                    ids: ['device-trend-donut-chart'],
                    render: function (data) {
                        const trend = data.trendDeviceDonut || {};
                        return renderChart('device-trend-donut-chart', {
                            ...baseOptions,
                            chart: { ...baseOptions.chart, type: 'donut', height: 300 },
                            labels: trend.trendDeviceDonutLabels || [],
                            series: trend.trendDeviceDonutData || [],
                            legend: { position: 'bottom' },
                            colors: colors
                        });
                    }
                },
                {
                    chart: 'multi-device',
                    ids: ['multi-device-line-chart'],
                    render: function (data) {
                        const multi = data.dataMultiDeviceChart || {};
                        return renderChart('multi-device-line-chart', {
                            ...baseOptions,
                            chart: { ...baseOptions.chart, type: 'line', height: 320 },
                            series: multi.multiDeviceChartSeriesData || [],
                            xaxis: { categories: multi.multiDeviceChartCategories || [] },
                            stroke: { curve: 'smooth', width: 2 },
                            markers: { size: 0 },
                            colors: colors
                        });
                    }
                }
            ];

            async function loadChartsSequentially() {
                for (const job of jobs) {
                    const chartUrl = new URL(baseUrl.toString());
                    chartUrl.searchParams.set('load_charts', '1');
                    chartUrl.searchParams.set('chart', job.chart);
                    // Kirim ranking device dari render halaman awal supaya setiap request grafik
                    // tidak perlu mengulang query untuk menghitung ranking yang sama.
                    chartUrl.searchParams.set('ranked_devices', JSON.stringify(selectedDeviceCodes));

                    try {
                        const response = await fetch(chartUrl.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin',
                            cache: 'no-store'
                        });

                        if (!response.ok) {
                            throw new Error('Gagal memuat grafik ' + job.chart + ' (' + response.status + ')');
                        }

                        const data = await response.json();
                        await job.render(data);
                    } catch (error) {
                        markChartGroupFailed(job.ids);
                        console.error('Dashboard chart loading error (' + job.chart + '):', error);
                    } finally {
                        job.ids.forEach(function (id) {
                            document.getElementById(id)?.classList.remove('dashboard-chart-loading');
                        });
                    }
                }
            }

            loadChartsSequentially();
        });
    </script>
@endpush
