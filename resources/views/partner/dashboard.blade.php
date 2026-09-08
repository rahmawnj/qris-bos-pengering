@props([
    'title' => 'Dashboard Admin',
])

@extends('layouts.dashboard.app')

@push('styles')
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    {{-- Diperlukan untuk Date Range Picker --}}
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <style>
        /* Tambahkan style jika diperlukan, misal untuk input filter */
        .filter-row {
            margin-bottom: 20px;
        }

        .input-group-text {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
        }
    </style>
@endpush

@section('content')
<div class="card p-3 mb-4">
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3">
	        <div class="d-flex flex-wrap align-items-end gap-2">
	            <div>
	                <label for="dashboard-daterange" class="form-label mb-1">Filter Tanggal:</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                    <input type="text" name="daterange" class="form-control" id="dashboard-daterange"
                        autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                        value="{{ $daterangeFilter ?? '' }}" placeholder="Pilih Rentang Waktu">
	                    </div>
	            </div>
	            <div>
	                <label for="device_rank" class="form-label mb-1">Urutan Device:</label>
	                <select id="device_rank" class="form-select form-select-sm">
	                    <option value="top" {{ ($selectedDeviceRank ?? request('device_rank', 'top')) === 'top' ? 'selected' : '' }}>Terbanyak</option>
	                    <option value="bottom" {{ ($selectedDeviceRank ?? request('device_rank', 'top')) === 'bottom' ? 'selected' : '' }}>Tersedikit</option>
	                </select>
	            </div>
	            <div class="d-flex gap-2">
                <button type="button" id="apply-dashboard-filter" class="btn btn-primary btn-sm">
                    <i class="fa fa-filter me-1"></i> Terapkan
                </button>
                <button type="button" id="reset-dashboard-filter" class="btn btn-default btn-sm">
                    <i class="fa fa-redo me-1"></i> Reset
                </button>
            </div>
        </div>

        <div class="text-end">
            {{-- Bagian Filter Dashboard --}}
            <small class="text-muted">Filter Dashboard:</small><br>
            <span class="fw-bold mb-3 d-block">
                {{ $daterangeFilter ?? 'Semua Waktu' }}
            </span>

            {{-- LOGIKA BARU: MENAMPILKAN MASA AKTIF AKUN DENGAN PENCEK STATUS 30 HARI --}}
            @php
                $user = auth()->user();
                $expiresAt = null;
                $status = null;
                $colorClass = 'text-primary'; // Default color
                $remainingDays = null; // Untuk menyimpan sisa hari

                if ($user) {
                    if ($user->role === 'owner' && $user->owner) {
                        $expiresAt = $user->owner->account_expires_at;
                        $status = $user->owner->status;
                    } elseif ($user->role === 'cashier' && $user->cashier && $user->cashier->owner) {
                        $expiresAt = $user->cashier->owner->account_expires_at;
                        $status = $user->cashier->owner->status;
                    }
                }
            @endphp

            @if ($user->role === 'owner' || $user->role === 'cashier')
                <small class="text-muted">Masa Aktif Akun:</small><br>

                @if ($status === 0)
                    {{-- Kondisi 1: Akun Nonaktif --}}
                    <span class="fw-bold text-danger">AKUN DINONAKTIFKAN</span>
                @elseif ($expiresAt)
                    @php
                        $expiryDate = \Carbon\Carbon::parse($expiresAt);
                    @endphp

                    @if ($expiryDate->isFuture())
                        @php
                            // Hitung sisa hari
                            $remainingDays = now()->diffInDays($expiryDate, false);

                            // Tentukan kelas warna
                            if ($remainingDays <= 30) {
                                $colorClass = 'text-danger'; // Merah jika kurang dari atau sama dengan 30 hari
                            } else {
                                $colorClass = 'text-success'; // Hijau jika lebih dari 30 hari
                            }
                        @endphp

                        {{-- Kondisi 2: Akun Aktif dan Akan Kadaluwarsa --}}
                        <span class="fw-bold {{ $colorClass }}">
                            @if ($remainingDays <= 30)
                                Hampir Habis ({{ $remainingDays }} hari lagi)
                            @endif
                            Berakhir pada: {{ $expiryDate->translatedFormat('d F Y') }}
                        </span>
                    @else
                        {{-- Kondisi 3: Akun Aktif, Tapi Sudah Kadaluwarsa --}}
                        <span class="fw-bold text-danger">
                            Masa Aktif Habis: {{ $expiryDate->translatedFormat('d F Y') }}
                        </span>
                    @endif
                @else
                    {{-- Kondisi 4: Aktif Tanpa Batas --}}
                    <span class="fw-bold text-primary">
                        Aktif Tanpa Batas
                    </span>
                @endif
            @endif
            {{-- END LOGIKA BARU --}}
        </div>
    </div>
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
            <div class="panel panel-default" data-sortable-id="index-1">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        Kategori Pembayaran
                    </h4>
                </div>
                <div id="transaction-summary" style="height: 170px;"></div>
                <div class="list-group">
                    <a href="javascript:;"
                        class="list-group-item rounded-0 list-group-item-action d-flex justify-content-between align-items-center text-ellipsis">
                        1. Member
                        <span class="badge bg-blue fs-10px">{{ $totalMemberTransactions ?? 0 }}</span>
                        {{-- Added ?? 0 for safety --}}
                    </a>
                    <a href="javascript:;"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center text-ellipsis">
                        2. Kasir / Manual
                        <span class="badge bg-green fs-10px">{{ $totalManualTransactions ?? 0 }}</span>
                        {{-- Added ?? 0 for safety --}}
                    </a>
                    <a href="javascript:;"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center text-ellipsis">
                        3. Qris
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
    <script type="text/javascript" src="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}">
    </script>

    <script src="{{ asset('assets/plugins/apexcharts/dist/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/@highlightjs/cdn-assets/highlight.min.js') }}"></script>

    <script>
        $(function() {
            // Inisialisasi Date Range Picker untuk Dashboard
            $('#dashboard-daterange').daterangepicker({
                opens: 'left',
                autoUpdateInput: false, // Jangan update input otomatis, kita update manual
                locale: {
                    format: 'YYYY/MM/DD', // Tetap ini jika Anda ingin YYYY/MM/DD
                    cancelLabel: 'Clear',
                    applyLabel: 'Apply',
                    daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                        'September', 'Oktober', 'November', 'Desember'
                    ],
                    firstDay: 1
                },
                // --- TAMBAHKAN ATAU MODIFIKASI BAGIAN INI ---
                ranges: {
                    'Hari Ini': [moment(), moment()],
                    'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                    '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                    'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')]
                }
                // --- AKHIR BAGIAN YANG DITAMBAHKAN/MODIFIKASI ---
            }, function(start, end, label) {
                // Ketika tanggal dipilih, update nilai input
                $('#dashboard-daterange').val(start.format('YYYY/MM/DD') + ' - ' + end.format(
                    'YYYY/MM/DD'));
            });

            // Event listener untuk tombol "Clear" pada Date Range Picker
            $('#dashboard-daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            // Handle tombol "Terapkan" filter
            $('#apply-dashboard-filter').on('click', function() {
                var daterange = $('#dashboard-daterange').val();
                var deviceRank = $('#device_rank').val() || 'top';
                var currentUrl = new URL(window.location.href);
                if (daterange) {
                    currentUrl.searchParams.set('daterange', daterange);
                } else {
                    currentUrl.searchParams.delete('daterange');
                }
                currentUrl.searchParams.set('device_rank', deviceRank);
                window.location.href = currentUrl.toString(); // Redirect dengan parameter baru
            });

            // Handle tombol "Reset" filter
            $('#reset-dashboard-filter').on('click', function() {
                var currentUrl = new URL(window.location.href);
                currentUrl.searchParams.delete('daterange'); // Hapus parameter daterange
                currentUrl.searchParams.delete('device_rank');
                window.location.href = currentUrl.toString(); // Redirect tanpa parameter daterange
            });

            // Set nilai input kembali jika ada di request sebelumnya
            var currentDaterange = "{{ request('daterange') }}";
            if (currentDaterange) {
                $('#dashboard-daterange').val(currentDaterange);
            } else {
                // Jika tidak ada daterange di request, kosongkan input secara eksplisit
                $('#dashboard-daterange').val('');
            }
        });
    </script>

    <script>
        var categories = {!! json_encode($categories) !!};
        var seriesData = [{
                name: 'Member',
                data: {!! json_encode($seriesData['member'] ?? []) !!}
            },
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
        var donutOptions = {
            chart: {
                type: 'donut',
                height: 300,
                toolbar: {
                    show: false
                }
            },
            series: {!! json_encode($donutData) !!},
            labels: {!! json_encode($donutLabels) !!},
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

    {{-- NEW SCRIPT FOR REVISION: Daily Total Transactions Bar Chart --}}
    <script>
        var dailyTotalTransactionsOptions = {
            chart: {
                type: 'bar',
                height: 300,
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
            series: [{
                name: 'Total Transaksi',
                data: {!! json_encode($dailyTransactionSeriesData) !!}
            }],
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
                    text: 'Jumlah Transaksi'
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
            colors: ['#3366FF'] // Example color
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
