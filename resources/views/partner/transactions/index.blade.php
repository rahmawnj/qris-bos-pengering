@props([
    'items' => ['Partner', 'Transaksi', 'Daftar Transaksi'],
    'title' => 'Transaksi',
    'subtitle' => 'Lihat dan kelola seluruh transaksi yang tercatat',
])

@push('styles')
    <link href="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />
    <style>
        /* Custom styles for better table and filter appearance */
        .panel-body .form-control-sm {
            height: calc(1.5em + .5rem + 2px);
            /* Adjust height for sm inputs */
            padding: .25rem .5rem;
            font-size: .875rem;
            line-height: 1.5;
            border-radius: .2rem;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            /* Space between filter elements */
            flex-wrap: wrap;
            /* Allow wrapping on smaller screens */
        }

        .filter-group .input-group {
            flex: 1;
            /* Allow input groups to grow */
            min-width: 180px;
            /* Minimum width for input groups before wrapping */
        }

        .filter-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }

        .filter-left,
        .filter-right {
            display: flex;
            gap: 10px;
        }


        .table thead th {
            vertical-align: middle;
            white-space: nowrap;
            /* Prevent headers from wrapping too much */
        }

        .table tbody td {
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: #f5f5f5;
            /* Subtle hover effect */
        }

        .badge-status {
            padding: .4em .6em;
            border-radius: .25rem;
            font-size: 85%;
            font-weight: 600;
            display: inline-block;
            /* Ensure badge respects padding */
        }

        .badge-pending {
            background-color: #ffc107;
            color: #343a40;
        }

        /* Yellow for pending */
        .badge-success {
            background-color: #28a745;
            color: #fff;
        }

        /* Green for success */
        .badge-failed {
            background-color: #dc3545;
            color: #fff;
        }

        /* Red for failed */
        .badge-qris {
            background-color: #007bff;
            color: #fff;
        }

        /* Blue for QRIS */
        .badge-manual {
            background-color: #6c757d;
            color: #fff;
        }

        /* Gray for manual */
        .badge-member {
            background-color: #17a2b8;
            color: #fff;
        }

        /* Cyan for member */

        /* Pagination alignment */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }

        .pagination-container nav {
            margin-bottom: 0;
            /* Remove default bottom margin from Laravel pagination */
        }

        /* NEW STYLES FOR MODERN CARDS */
        .summary-card {
            background-color: #ffffff;
            /* White background */
            border: 1px solid #e0e0e0;
            /* Light border */
            border-radius: .5rem;
            /* Slightly larger border-radius */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            /* Modern shadow */
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            /* Space between icon and text */
            height: 100%;
            min-height: 120px;
        }

        .summary-card .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            color: #ffffff;
            flex-shrink: 0;
            /* Prevent icon from shrinking */
        }

        /* Specific icon circle colors */
        .icon-circle.bg-primary {
            background-color: #007bff !important;
        }

        .icon-circle.bg-success {
            background-color: #28a745 !important;
        }

        .icon-circle.bg-info {
            background-color: #17a2b8 !important;
        }

        .icon-circle.bg-warning {
            background-color: #ffc107 !important;
        }

        .icon-circle.bg-danger {
            background-color: #dc3545 !important;
        }

        .summary-card .card-title {
            font-size: 1rem;
            /* Smaller title */
            color: #6c757d;
            /* Muted color for title */
            margin-bottom: 0.25rem;
        }

        .summary-card .card-text.h3 {
            font-size: 1.75rem;
            /* Slightly smaller h3 for balance */
            margin-bottom: 0.5rem;
            color: #343a40;
            /* Darker color for value */
        }

        .summary-card .card-text.small-desc {
            font-size: 0.875rem;
            /* Smaller description */
            color: #999;
            /* Lighter color for description */
            margin-bottom: 0;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>

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
                var exportUrl = "{{ route('export.partner-transactions') }}"; // Make sure this route exists
                var queryString = form.serialize(); // Get all form data as query string
                window.location.href = exportUrl + '?' + queryString;
            });
        });
    </script>
@endpush

@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    @if ($showTotals)
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="summary-card">
                    <div class="icon-circle bg-primary">
                        <i class="fa fa-receipt"></i> {{-- Icon untuk Total Transaksi --}}
                    </div>
                    <div>
                        <h5 class="card-title">Total Transaksi</h5>
                        <p class="card-text h3">{{ $totalTransactionsCount }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="summary-card">
                    <div class="icon-circle bg-success">
                        <i class="fa fa-money-bill-wave"></i> {{-- Icon untuk Total Jumlah Uang --}}
                    </div>
                    <div>
                        <h5 class="card-title">Total Jumlah Uang</h5>
                        <p class="card-text h3">Rp {{ number_format($totalTransactionsAmount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="summary-card">
                    <div class="icon-circle bg-info">
                        <i class="fa fa-check-circle"></i> {{-- Icon untuk Transaksi Selesai --}}
                    </div>
                    <div>
                        <h5 class="card-title">Transaksi Selesai</h5>
                        <p class="card-text h3">{{ $completedTransactionsCount }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title ?? '' }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand">
                    <i class="fa fa-expand"></i>
                </a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload">
                    <i class="fa fa-redo"></i>
                </a>
            </div>
        </div>
        <div class="panel-body">
            <form method="GET" action="{{ route('partner.transactions.index') }}" class="mb-4">
                <div class="row g-3"> {{-- Use row and g-3 for consistent spacing --}}
                    <div class="col-md-3 col-sm-6">
                        <label for="filter-status" class="form-label visually-hidden">Status</label>
                        <select name="status" id="filter-status" class="form-control form-control-sm">
                            <option value="">-- Semua Status --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Sukses</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Gagal</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label for="filter-type" class="form-label visually-hidden">Tipe Pembayaran</label>
                        <select name="type" id="filter-type" class="form-control form-control-sm">
        <option value="">-- Semua Tipe Pembayaran --</option>
        {{-- Opsi baru untuk filter manual --}}
        <option value="cash" {{ request('type') == 'cash' ? 'selected' : '' }}>Tunai</option>
        <option value="non_cash" {{ request('type') == 'non_cash' ? 'selected' : '' }}>Transfer</option>
        <option value="qris" {{ request('type') == 'qris' ? 'selected' : '' }}>QRIS</option>
        <option value="member" {{ request('type') == 'member' ? 'selected' : '' }}>Member</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-8">
                        <label for="filter-daterange" class="form-label visually-hidden">Rentang Waktu</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                            <input type="text" name="daterange" class="form-control" id="filter-daterange"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                                value="{{ request('daterange') }}" placeholder="Pilih Rentang Waktu">
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <label for="filter-search" class="form-label visually-hidden">Pencarian</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input type="text" name="search" id="filter-search" class="form-control form-control-sm"
                                placeholder="Nama Outlet, ORDER ID, Jumlah" value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="filter-actions">
                    <div class="filter-left">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa fa-filter me-1"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('partner.transactions.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-redo me-1"></i> Reset
                        </a>
                    </div>

                    <div class="filter-right">
                        <button type="button" id="export-button" class="btn btn-success btn-sm">
                            <i class="fa fa-file-excel me-1"></i> Export ke Excel
                        </button>
                    </div>
                </div>

            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>Order ID</th>
                            <th>Outlet</th>
                            <th class="text-end">Jumlah</th> {{-- Align amount to the right --}}
                            <th>Tipe Pembayaran</th>
                            <th>Status</th>
                            <th>Payment Method / Provider</th>
                            <th>Waktu</th>
                            <th width="1%" class="text-center">Aksi</th> {{-- Align action buttons to center --}}
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            @php
                                $timezoneMap = [
                                    'wib' => 'Asia/Jakarta',
                                    'wita' => 'Asia/Makassar',
                                    'wit' => 'Asia/Jayapura',
                                ];
                                $tzKey = strtolower($transaction->timezone);
                                $tz = $timezoneMap[$tzKey] ?? 'Asia/Jakarta';

                                // Define badge classes based on status and type
                                $statusBadgeClass = '';
                                switch ($transaction->status) {
                                    case 'pending':
                                        $statusBadgeClass = 'badge-pending';
                                        break;
                                    case 'success':
                                        $statusBadgeClass = 'badge-success';
                                        break;
                                    case 'failed':
                                        $statusBadgeClass = 'badge-failed';
                                        break;
                                    default:
                                        $statusBadgeClass = 'bg-secondary'; // Fallback
                                        break;
                                }

                                $typeBadgeClass = '';
                                switch ($transaction->type) {
                                    case 'qris':
                                        $typeBadgeClass = 'badge-qris';
                                        break;
                                    case 'manual':
                                        $typeBadgeClass = 'badge-manual';
                                        break;
                                    case 'member':
                                        $typeBadgeClass = 'badge-member';
                                        break;
                                    default:
                                        $typeBadgeClass = 'bg-info'; // Fallback
                                        break;
                                }
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $transaction->order_id }}
                                </td>
                                <td>
                                    <strong>{{ $transaction->outlet->outlet_name ?? '-' }}</strong><br>
                               <small class="text-muted">
        <i class="fa fa-cash-register me-1"></i> Device:
        {{-- Menggunakan operator '?->' untuk optional chaining pada rantai relasi --}}
        {{ $transaction->deviceTransactions->first()?->device_code ?? '-' }}
    </small>
                                </td>
                                <td class="text-end">Rp{{ number_format($transaction->amount, 0, ',', '.') }} </td>
                                <td>
<span class="badge badge-status {{ $typeBadgeClass }}">
    {{ $transaction->type === 'manual' ? 'Drop Off' : ucfirst($transaction->type) }}
</span>
                                </td>

                                <td>
                                    <span class="badge badge-status {{ $statusBadgeClass }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($transaction->type === 'manual')
                                        <span class="badge badge-status {{ $typeBadgeClass }}">
                                            {{ $transaction->manualTransaction->payment_method === 'cash' ? 'Tunai' : ($transaction->manualTransaction->payment_method === 'non_cash' ? 'Transfer' : ucfirst($transaction->manualTransaction->payment_method)) }}
                                        </span>
                                    @elseif ($transaction->type === 'qris')
                                        <span class="badge bg-dark">{{ $transaction->qris_provider_label }}</span>
                                    @else
                                        <span class="badge badge-status {{ $typeBadgeClass }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    {{ $transaction->created_at }}
                                    <br><small class="text-muted">{{ strtoupper($transaction->timezone) }}</small>
                                </td>
                                <td class="text-center">
                                    <x-print-invoice :transaction="$transaction" />
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">Tidak ada transaksi ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($showTotals)
                        {{-- --- Footer Tabel untuk Total --- --}}
                        <tfoot>
                            <tr>
                                {{-- Colspan disesuaikan dengan jumlah kolom di tbody --}}
                                {{-- Jumlah kolom total di thead adalah 9: #, Order ID, Outlet, Jumlah, Tipe Pembayaran, Tipe Layanan, Status, Waktu, Aksi --}}
                                {{-- Kita ingin 'Total:' di kolom pertama, jumlah di kolom keempat (index 3), dan jumlah transaksi di kolom ketujuh (index 6) --}}
                                <th colspan="1">Total:</th>
                                <th colspan="2">{{ $totalTransactionsCount }}</th> {{-- Jumlah transaksi dan kolom Aksi --}}
                                <th>Total Transaksi:</th>
                                <th class="text-end">Rp {{ number_format($totalTransactionsAmount, 0, ',', '.') }}</th>
                                <th colspan="2"></th> {{-- Kolom kosong untuk Tipe Pembayaran dan Tipe Layanan --}}
                                <th colspan="2"></th> {{-- Kolom kosong untuk Order ID dan Outlet --}}
                            </tr>
                        </tfoot>
                        {{-- --- Akhir Footer Tabel --- --}}
                    @endif
                </table>
            </div>

            <div class="pagination-container">
                <div class="small text-muted">
                    {{ $transactions->firstItem() }}-{{ $transactions->lastItem() }} / {{ $transactions->total() }}
                </div>
                <div>{{ $transactions->appends(request()->query())->links() }}</div>
            </div>
        </div>
    </div>
@endsection
