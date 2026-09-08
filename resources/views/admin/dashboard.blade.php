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

    <div class="stat-grid mb-4">
        <div class="stat-card stat-orange">
            <div class="stat-title">Pendapatan</div>
            <div class="stat-value">Rp {{ number_format($qrisBalance ?? 0, 0, ',', '.') }}</div>
            <div class="stat-sub">Total balance owner sesuai filter brand/outlet</div>
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
                    <div id="category-line" class="widget-chart-full-width" style="height: 260px;"></div>
                </div>
                <div class="widget-chart-sidebar bg-white">
                    <div class="chart-number">
                        {{ $totalTransactions ?? 0 }} {{-- Added ?? 0 for safety --}}
                        <small>Jumlah Transaksi</small>
                    </div>
                    <div class="flex-grow-1 d-flex align-items-center">
                        <div id="perbandingan-donut" style="height: 180px"></div>
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
                <div id="transaction-summary" style="height: 170px;"></div>
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
                    <div id="daily-total-transactions-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="panel panel-default" data-sortable-id="index-3">
                <div class="panel-heading">
                    <h4 class="panel-title">Tren Jam Transaksi</h4>
                </div>
                <div class="panel-body">
                    <div id="hourly-transaction-trend-chart" style="height: 300px;"></div>
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
                    <div id="multi-device-line-chart" style="height: 320px;"></div>
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
                    <div id="device-trend-donut-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript" src="/assets/plugins/moment/min/moment.min.js"></script>
    <script type="text/javascript" src="/assets/plugins/bootstrap-daterangepicker/daterangepicker.js">
    </script>

    <script src="/assets/plugins/apexcharts/dist/apexcharts.min.js"></script>
    <script src="/assets/plugins/@highlightjs/cdn-assets/highlight.min.js"></script>

  <script>
     $(function() {
    $('#dashboard-daterange').daterangepicker({
        opens: 'left',
        autoUpdateInput: false,
        locale: {
            format: 'YYYY/MM/DD',
            cancelLabel: 'Clear',
            applyLabel: 'Apply',
            daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            firstDay: 1
        },
        ranges: {
            'Hari Ini': [moment(), moment()],
            'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
            '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
            'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
            'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    // Mengisi input saat tanggal dipilih
    $('#dashboard-daterange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY/MM/DD') + ' - ' + picker.endDate.format('YYYY/MM/DD'));
    });

    // Mengosongkan input saat tekan Clear
    $('#dashboard-daterange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
});
  </script>

    <script>
        var categories = {!! json_encode($categories) !!};
        var seriesData = [
            {
                name: 'QRIS',
                data: {!! json_encode($seriesData['qris'] ?? []) !!}
            },
            {
                name: 'Manual',
                data: {!! json_encode($seriesData['manual'] ?? []) !!}
            }
        ];

        var areaOptions = {
            chart: {
                height: 350,
                type: 'area',
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            series: seriesData,
            xaxis: {
                type: 'datetime',
                categories: categories,
                labels: {
                    datetimeUTC: false,
                    format: 'dd/MM'
                }
            },
            tooltip: {
                x: {
                    format: 'dd/MM/yy'
                }
            },
            colors: ['#FF4560', '#00E396', '#008FFB']
        };

        var areaChart = new ApexCharts(document.querySelector('#category-line'), areaOptions);
        areaChart.render();
    </script>

    <script>
        var donutData = {!! json_encode($donutData) !!}.map(function(v) { return Number(v) || 0; });
        var donutLabels = {!! json_encode($donutLabels) !!};

        var donutOptions = {
            chart: {
                type: 'donut',
                height: 300,
                toolbar: {
                    show: false
                }
            },
            series: donutData,
            labels: donutLabels,
            colors: ['#008FFB', '#00E396', '#FF4560'],
            legend: {
                position: 'bottom',
                fontSize: '12px',
                markers: {
                    width: 10,
                    height: 10,
                    radius: 12,
                },
                itemMargin: {
                    horizontal: 8,
                    vertical: 2
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => {
                                        const valA = parseFloat(a) || 0;
                                        const valB = parseFloat(b) || 0;
                                        return valA + valB;
                                    }, 0);
                                }
                            }
                        }
                    }
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        var donutChart = new ApexCharts(document.querySelector("#transaction-summary"), donutOptions);
        donutChart.render();
    </script>

    <script>
        var perbandinganOptions = {
            chart: {
                type: 'donut',
                height: 180,
                toolbar: {
                    show: false
                }
            },
            series: {!! json_encode($serviceData) !!},
            labels: {!! json_encode($serviceLabels) !!},
            colors: ['#008FFB', '#00E396', '#FFC107', '#6C757D'],
            legend: {
                show: true,
                position: 'bottom',
                fontSize: '11px',
                markers: {
                    width: 8,
                    height: 8,
                    radius: 12,
                },
                itemMargin: {
                    horizontal: 5,
                    vertical: 0
                },
                formatter: function(seriesName, opts) {
                    const total = opts.w.globals.series[opts.seriesIndex];
                    return seriesName + " - " + total;
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            name: {
                                show: true
                            },
                            value: {
                                show: true,
                                formatter: function(val) {
                                    return val;
                                }
                            },
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => {
                                        // Konversi setiap nilai ke float, jika tidak bisa, gunakan 0
                                        const valA = parseFloat(a) || 0;
                                        const valB = parseFloat(b) || 0;
                                        return valA + valB;
                                    }, 0); // Pastikan nilai awal reduce adalah angka 0
                                }
                            }
                        }
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val;
                    }
                }
            },
            responsive: [{
                breakpoint: 768,
                options: {
                    chart: {
                        height: 200,
                        width: '100%'
                    },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center'
                    }
                }
            }]
        };

        var perbandinganChart = new ApexCharts(document.querySelector("#perbandingan-donut"), perbandinganOptions);
        perbandinganChart.render();
    </script>

    {{-- Daily Device Usage (Washer vs Dryer) Bar Chart --}}
    <script>
        var dailyTotalTransactionsOptions = {
            chart: {
                type: 'bar',
                height: 300,
                stacked: true,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            series: [
                {
                    name: 'Washer',
                    data: {!! json_encode($dailyWasherSeriesData ?? []) !!}
                },
                {
                    name: 'Dryer',
                    data: {!! json_encode($dailyDryerSeriesData ?? []) !!}
                }
            ],
            xaxis: {
                categories: {!! json_encode($dailyTransactionCategories) !!},
                type: 'datetime',
                labels: {
                    datetimeUTC: false,
                    format: 'dd/MM'
                }
            },
            yaxis: {
                title: {
                    text: 'Jumlah Pemakaian'
                }
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " transaksi"
                    }
                },
                x: {
                    format: 'dd/MM/yy'
                }
            },
            colors: ['#2F80ED', '#27AE60']
        };

        var dailyTotalTransactionsChart = new ApexCharts(document.querySelector("#daily-total-transactions-chart"),
            dailyTotalTransactionsOptions);
        dailyTotalTransactionsChart.render();
    </script>

    {{-- NEW SCRIPT FOR REVISION: Hourly Transaction Trend Donut Chart --}}
    <script>
        var hourlyTransactionTrendOptions = {
            chart: {
                type: 'donut',
                height: 300,
                toolbar: {
                    show: false
                }
            },
            series: {!! json_encode($hourlyData) !!},
            labels: {!! json_encode($hourlyLabels) !!},
            colors: ['#008FFB', '#00E396', '#FF4560', '#775DD0', '#FEB019', '#F9A826', '#E91E63', '#9C27B0',
                '#FFC107', '#2196F3', '#4CAF50', '#FF9800', '#F44336', '#9E9E9E', '#607D8B', '#FFD700',
                '#ADFF2F', '#8A2BE2', '#DC143C', '#20B2AA', '#BA55D3', '#7B68EE', '#FF6347', '#4682B4'
            ], // More colors for 24 hours
            legend: {
                position: 'right', // Place legend on the right for better space management
                fontSize: '12px',
                markers: {
                    width: 10,
                    height: 10,
                    radius: 12,
                },
                itemMargin: {
                    horizontal: 8,
                    vertical: 2
                },
                formatter: function(seriesName, opts) {
                    const total = opts.w.globals.series[opts.seriesIndex];
                    return seriesName + " - " + total;
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => {
                                        const valA = parseFloat(a) || 0;
                                        const valB = parseFloat(b) || 0;
                                        return valA + valB;
                                    }, 0);
                                }
                            }
                        }
                    }
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 280
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        var hourlyTransactionTrendChart = new ApexCharts(document.querySelector("#hourly-transaction-trend-chart"),
            hourlyTransactionTrendOptions);
        hourlyTransactionTrendChart.render();
    </script>

    {{-- NEW SCRIPT FOR MULTI-DEVICE LINE CHART --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data dari controller ($devtra)
            const multiDeviceChartCategories = {!! json_encode($dataMultiDeviceChart['multiDeviceChartCategories'] ?? []) !!};
            const multiDeviceChartSeriesData = {!! json_encode($dataMultiDeviceChart['multiDeviceChartSeriesData'] ?? []) !!};

            const buildMultiDeviceLineChartOptions = function(seriesData) {
                return {
                chart: {
                    height: 320,
                    type: 'line',
                    zoom: {
                        enabled: false
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                series: seriesData,
                xaxis: {
                    type: 'datetime',
                    categories: multiDeviceChartCategories,
                    labels: {
                        datetimeUTC: false,
                        format: 'dd/MM'
                    }
                },
                yaxis: {
                    title: {
                        text: 'Jumlah Transaksi'
                    },
                    min: 0,
                    tickAmount: 5 // Menentukan jumlah tick pada y-axis
                },
                tooltip: {
                    x: {
                        format: 'dd/MM/yy'
                    }
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    floating: false,
                    fontSize: '12px',
                    itemMargin: {
                        horizontal: 8,
                        vertical: 4
                    }
                },
                colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A', '#26A69A',
                    '#D10CE8', '#F86624', '#2E294E'
                ] // Contoh warna
            };
            };

            new ApexCharts(document.querySelector("#multi-device-line-chart"),
                buildMultiDeviceLineChartOptions(multiDeviceChartSeriesData)).render();
        });
    </script>

    {{-- NEW SCRIPT FOR TREND DEVICE DONUT CHART (Using data from $trendDeviceDonut) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data aktual dari Laravel
            const trendDeviceDonutData = {!! json_encode($trendDeviceDonut['trendDeviceDonutData'] ?? []) !!};
            const trendDeviceDonutLabels = {!! json_encode($trendDeviceDonut['trendDeviceDonutLabels'] ?? []) !!};

            // Konfigurasi ApexCharts
            const options = {
                chart: {
                    type: 'donut',
                    height: 350,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: {
                        show: false
                    }
                },
                series: trendDeviceDonutData,
                labels: trendDeviceDonutLabels,
                colors: ['#4CAF50', '#2196F3', '#FFC107', '#FF5722', '#9C27B0', '#00BCD4', '#8BC34A',
                    '#FF9800'
                ], // Contoh warna
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opts) {
                        // Menampilkan label perangkat dan persentase
                        return opts.w.config.labels[opts.seriesIndex] + " (" + val.toFixed(1) + "%)";
                    }
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    fontSize: '14px',
                    markers: {
                        radius: 12,
                    },
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 280
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total Transaksi',
                                    formatter: function(w) {
                                        return w.globals.series.reduce((a, b) => a + b, 0);
                                    }
                                }
                            }
                        }
                    }
                }
            };

            // Render chart
            const chart = new ApexCharts(document.querySelector("#device-trend-donut-chart"), options);
            chart.render();
        });
    </script>
@endpush
