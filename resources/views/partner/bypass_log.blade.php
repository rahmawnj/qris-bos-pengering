@props([
    'items' => ['Partner', 'Monitoring', 'Bypass Logs'],
    'title' => 'Bypass Logs',
    'subtitle' => 'Catatan bypass device dan outlet',
])

@extends('layouts.dashboard.app')

@push('styles')
    {{-- Diperlukan untuk Date Range Picker --}}
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" />

    {{-- Custom CSS untuk tampilan yang lebih rapi --}}
    <style>
        .table-responsive {
            overflow-x: auto;
        }

        .table th,
        .table td {
            vertical-align: middle;
            padding: 0.75rem;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            /* Light gray on hover */
        }

        .status-badge {
            font-size: 0.85em;
            padding: 0.4em 0.8em;
            border-radius: 0.25rem;
            display: inline-block;
            /* Agar bisa diatur paddingnya */
        }

        /* Warna untuk status bypass */
        .status-success {
            background-color: #28a745;
            color: #fff;
        }

        /* Misalnya, bypass successful */
        .status-warning {
            background-color: #ffc107;
            color: #212529;
        }

        /* Misalnya, bypass temporary */
        .status-danger {
            background-color: #dc3545;
            color: #fff;
        }

        /* Misalnya, bypass failed atau disabled */
        .status-info {
            background-color: #17a2b8;
            color: #fff;
        }

        /* Status lain */

        .filter-form .form-control-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
        }

        .filter-form label {
            font-size: 0.9em;
            margin-bottom: 0.25rem;
        }

        .filter-form .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }

        .pagination-info {
            font-size: 0.9em;
            color: #6c757d;
        }
    </style>
@endpush

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title ?? '' }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand" title="Perbesar">
                    <i class="fa fa-expand"></i>
                </a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"
                    title="Refresh Data">
                    <i class="fa fa-redo"></i>
                </a>
            </div>
        </div>
        <div class="panel-body">
            <div class="card p-3 mb-4">
                <form method="GET" action="{{ route('partner.bypass.logs') }}">
                    <div class="d-flex flex-wrap align-items-end gap-3">
                        <div style="min-width: 240px; flex: 1;">
                            <label for="filter-daterange" class="form-label mb-1 fw-bold">Rentang Waktu Log</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                <input type="text" name="daterange" class="form-control" id="filter-daterange"
                                    autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                                    value="{{ request('daterange') }}" placeholder="Pilih Rentang Waktu">
                            </div>
                        </div>
                        <div style="min-width: 200px;">
                            <label for="type" class="form-label mb-1 fw-bold">Tipe Bypass</label>
                            <select name="type" id="type" class="form-control form-control-sm">
                                <option value="">-- Semua Tipe --</option>
                                <option value="bypass" {{ request('type') == 'bypass' ? 'selected' : '' }}>Bypass</option>
                                <option value="session" {{ request('type') == 'session' ? 'selected' : '' }}>Drop Off</option>
                            </select>
                        </div>
                        <div style="min-width: 280px; flex: 2;">
                            <label for="search" class="form-label mb-1 fw-bold">Pencarian</label>
                            <input type="text" name="search" id="search" class="form-control form-control-sm"
                                value="{{ request('search') }}" placeholder="Outlet, Device, Status, Tipe...">
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa fa-filter me-1"></i> Terapkan
                            </button>
                            <a href="{{ route('partner.bypass.logs') }}" class="btn btn-default btn-sm">
                                <i class="fa fa-redo me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="text-center" width="1%">#</th>
                            <th>Detail Outlet</th>
                            <th>Detail Perangkat</th>
                            <th class="text-center">Tipe Bypass</th>
                            <th class="text-center">Status Bypass</th>
                                                        <th class="text-center">Keterangan</th>

                            <th class="text-center">Waktu Aktivasi Bypass</th>
                            <th class="text-center">Waktu Dibuat (Log)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="text-center">
                                    {{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}</td>
                                <td>
                                    <strong>{{ $log->outlet_name ?? ($log->outlet_code ?? 'N/A') }}</strong><br>
                                    <small class="text-muted"><i class="fa fa-map-marker-alt me-1"></i>
                                        {{ $log->outlet_address ?? 'Alamat tidak tersedia' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $log->device_name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted"><i class="fa fa-barcode me-1"></i>
                                        {{ $log->device_code ?? 'N/A' }}</small>
                                </td>
                                <td class="text-center">
                                    @php
                                        $type = $log->type ?? 'N/A';
                                        $displayText = $type === 'session' ? 'Drop Off' : ucfirst($type);
                                        $badgeClass = match ($type) {
                                            'session' => 'bg-primary',
                                            'bypass' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                    @endphp

                                    <span class="badge {{ $badgeClass }} status-badge">
                                        {{ $displayText }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $status = strtolower($log->bypass_status ?? 'unknown');

                                        // Warna yang tersedia (bisa tambah sesuai selera)
                                        $availableColors = [
                                            'bg-primary',
                                            'bg-success',
                                            'bg-danger',
                                            'bg-warning',
                                            'bg-info',
                                            'bg-secondary',
                                            'bg-dark',
                                        ];

                                        // Buat warna tetap berdasarkan hash status
                                        $hash = crc32($status);
                                        $index = $hash % count($availableColors);
                                        $badgeClass = $availableColors[$index];
                                    @endphp
                                    <span class="badge {{ $badgeClass }} status-badge">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                                                <td>{{ $log->note }}</td>

                                <td class="text-center">
                                    @if ($log->bypass_activation)
                                        {{ \Carbon\Carbon::parse($log->bypass_activation)->format('d M Y, H:i:s') }}
                                        <br>
                                        <small
                                            class="text-muted">{{ \Carbon\Carbon::parse($log->bypass_activation)->diffForHumans() }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($log->created_at)
                                        {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i:s') }}
                                        <br>
                                        <small
                                            class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fa fa-exclamation-circle me-1"></i> Tidak ada data log bypass yang ditemukan
                                    untuk kriteria ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="pagination-info">
                    Menampilkan {{ $logs->firstItem() }} hingga {{ $logs->lastItem() }} dari
                    {{ $logs->total() }} total log.
                </div>
                <div>{{ $logs->appends(request()->query())->links() }}</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Moment.js is usually already in vendor.min.js or app.min.js --}}
    <script type="text/javascript" src="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>

    <script>
        $(function() {
            // Inisialisasi Date Range Picker
            $('#filter-daterange').daterangepicker({
                opens: 'left', // Posisi calendar
                autoUpdateInput: false, // Jangan update input otomatis
                locale: {
                    format: 'DD/MM/YYYY', // Format tampilan di input
                    cancelLabel: 'Clear',
                    applyLabel: 'Apply',
                    daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                        'September', 'Oktober', 'November', 'Desember'
                    ],
                    firstDay: 1
                }
            }, function(start, end, label) {
                // Ketika tanggal dipilih, update nilai input
                $('#filter-daterange').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
            });

            // Handle tombol "Clear" pada Date Range Picker
            $('#filter-daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            // Set nilai input kembali jika ada di request sebelumnya
            @if (request('daterange'))
                $('#filter-daterange').val('{{ request('daterange') }}');
            @else
                // Only set default if no daterange is in request
                $('#filter-daterange').data('daterangepicker').setStartDate(moment());
                $('#filter-daterange').data('daterangepicker').setEndDate(moment());
            @endif
        });
    </script>
@endpush
