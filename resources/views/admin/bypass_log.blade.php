@props([
    'items' => $breadcrumbItems ?? ['Admin', 'Monitoring', 'Bypass Logs'],
    'title' => 'Bypass Logs',
    'subtitle' => 'Catatan bypass device dan outlet',
])

@extends('layouts.dashboard.app')

@push('styles')
    {{-- Diperlukan untuk Date Range Picker --}}
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

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
 <div class="card p-3 mb-4">
                <form method="GET" action="{{ route($routeName ?? 'admin.bypass.logs') }}">
                    <div class="d-flex flex-wrap align-items-end gap-3">
                        <div style="min-width: 240px; flex: 1;">
                            <label for="filter-daterange" class="form-label mb-1 fw-bold">Rentang Waktu Log</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                <input type="text" name="daterange" autocomplete="off" class="form-control" id="filter-daterange"
                                    autocorrect="off" autocapitalize="off" spellcheck="false"
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
                            <a href="{{ route($routeName ?? 'admin.bypass.logs') }}" class="btn btn-default btn-sm">
                                <i class="fa fa-redo me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
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


            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="text-center" width="1%">#</th>
                            <th>Detail Outlet</th>
                            <th>Detail Perangkat</th>
                            <th class="text-center">Tipe Bypass</th> {{-- Menambahkan kolom Type --}}
                            <th class="text-center">Status Bypass</th>
                                                        <th class="text-center">Keterangan</th>

                            <th class="text-center">Waktu Aktivasi Bypass</th> {{-- Ganti Tanggal Log menjadi Waktu Aktivasi Bypass --}}
                            <th class="text-center">Waktu Dibuat (Log)</th> {{-- Menambahkan Tanggal Log --}}
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
                                <td colspan="7" class="text-center py-4 text-muted"> {{-- Update colspan to 7 --}}
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
    {{-- Moment.js is usually already in vendor.min.js or app.min.js in Color Admin --}}
    {{-- Date Range Picker JS --}}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        $(function() {
            // Initialize daterangepicker
            $('#filter-daterange').daterangepicker({
                timePicker: false,
                showDropdowns: true,
                autoUpdateInput: false, // Prevents auto-updating the input field until "Apply"
                autoApply: false, // Prevents auto-closing the picker
                locale: {
                    format: 'YYYY-MM-DD',
                    separator: ' - ',
                    cancelLabel: 'Clear',
                    applyLabel: 'Terapkan',
                    customRangeLabel: 'Custom'
                },
                ranges: {
                    'Hari Ini': [moment(), moment()],
                    'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                    '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                    'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')]
                }
            });

            // Set initial value for daterange input if a value exists
            var initialDateRange = "{{ request('daterange') }}";
            if (initialDateRange) {
                $('#filter-daterange').val(initialDateRange);
            } else {
                // Optionally set a default range, e.g., "Hari Ini" if no range is selected
                // var today = moment().format('YYYY-MM-DD');
                // $('#filter-daterange').val(today + ' - ' + today);
            }


            // Event listener for when the 'Apply' button is clicked in the daterangepicker
            $('#filter-daterange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                // You might want to submit the form here automatically if a range is applied
                // $(this).closest('form').submit();
            });

            // Event listener for when the 'Clear' button is clicked in the daterangepicker
            $('#filter-daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                // If you want to clear the filter and re-submit the form
                // $(this).closest('form').submit();
            });

            // Handle export button click
            $('#export-button').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                var exportUrl = "{{ route('export.admin-transactions') }}"; // Make sure this route exists
                var queryString = form.serialize(); // Get all form data as query string
                window.location.href = exportUrl + '?' + queryString;
            });
        });
    </script>
@endpush
