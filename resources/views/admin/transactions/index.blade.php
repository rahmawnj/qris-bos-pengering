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

        .badge-selfservice {
            background-color: #20c997;
            color: #fff;
        }

        .badge-dropoff {
            background-color: #fd7e14;
            color: #fff;
        }

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }

        .pagination-container nav {
            margin-bottom: 0;
        }

        .summary-card {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: .5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
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
        }

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
    <script src="{{ asset('assets/plugins/moment/min/moment.min.js') }}"></script>
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
                var exportUrl = "{{ route('export.admin-transactions') }}"; // Make sure this route exists
                var queryString = form.serialize(); // Get all form data as query string
                window.location.href = exportUrl + '?' + queryString;
            });
        });
    </script>
@endpush

@extends('layouts.dashboard.app')

@section('content')
    @php
        $isAdminContext = !session('impersonating') &&
            (Auth::guard('admin_config')->check() || (Auth::check() && Auth::user()->role === 'admin'));
        $transactionsIndexRoute = $isAdminContext ? route('admin.transactions.index') : route('partner.transactions.index');
    @endphp

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

    <div class="card p-3 mb-4">
        <form method="GET" action="{{ $transactionsIndexRoute }}">
            <div class="d-flex flex-wrap align-items-end gap-3">
                 <div style="min-width: 240px; flex: 1;">
                    <label for="filter-daterange" class="form-label mb-1 fw-bold">Rentang Waktu</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                        <input type="text" name="daterange" class="form-control" id="filter-daterange" autocomplete="off"
                            autocorrect="off" autocapitalize="off" spellcheck="false"
                            value="{{ request('daterange') }}" placeholder="Pilih Rentang Waktu">
                    </div>
                </div>

                <div style="min-width: 180px;">
                    <label for="filter-status" class="form-label mb-1 fw-bold">Status</label>
                    <select name="status" id="filter-status" class="form-control form-control-sm">
                        <option value="">-- Semua Status --</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Sukses</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Gagal</option>
                    </select>
                </div>
                <div style="min-width: 200px;">
                    <label for="filter-type" class="form-label mb-1 fw-bold">Tipe Pembayaran</label>
                    <select name="type" id="filter-type" class="form-control form-control-sm">
                        <option value="">-- Semua Tipe Pembayaran --</option>
                        <option value="cash" {{ request('type') == 'cash' ? 'selected' : '' }}>Tunai</option>
                        <option value="non_cash" {{ request('type') == 'non_cash' ? 'selected' : '' }}>Transfer</option>
                        <option value="qris" {{ request('type') == 'qris' ? 'selected' : '' }}>QRIS</option>
                    </select>
                </div>

                <div style="min-width: 240px; flex: 1;">
                    <label for="filter-search" class="form-label mb-1 fw-bold">Pencarian</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                        <input type="text" name="search" id="filter-search" class="form-control form-control-sm" autocomplete="off"
                            placeholder="Nama Outlet, ORDER ID, Jumlah" value="{{ request('search') }}">
                    </div>
                </div>
            </div>
            <hr>
            <div class="filter-actions">
                <div class="filter-left">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-filter me-1"></i> Terapkan Filter
                    </button>
                    <a href="{{ $transactionsIndexRoute }}" class="btn btn-default btn-sm">
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
    </div>

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

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>Order ID</th>
                            <th>Owner</th>
                            <th>Outlet</th>
                            <th class="text-end">Jumlah</th>
                            <th>Tipe Pembayaran / Provider</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
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
                                        $statusBadgeClass = 'bg-secondary';
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
                                        $typeBadgeClass = 'bg-info';
                                        break;
                                }

                                $qrisDeviceCode = $transaction->qrisTransaction?->device_code;
                                $displayDeviceCode = $transaction->deviceTransactions->first()?->device_code
                                    ?? $qrisDeviceCode
                                    ?? '-';
                            @endphp
                            <tr>
                                <td>{{ $transactions->firstItem() + $loop->index }}</td>
                                <td>{{ $transaction->order_id }}</td>
                                <td>
                                    <strong>{{ $transaction->owner->brand_name ?? '-' }}</strong><br>
                                    <small class="text-muted">{{ $transaction->owner->user->name ?? '-' }}</small>
                                </td>
                               <td>
                                    <strong>{{ $transaction->outlet->outlet_name ?? '-' }}</strong><br>
                                    <small class="text-muted">
                                        <i class="fa fa-cash-register me-1"></i> Device:
                                        {{ $displayDeviceCode }}
                                    </small>
                                </td>
                                <td class="text-end">Rp{{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                <td>
                                    @if ($transaction->type === 'manual')
                                        <span class="badge badge-status {{ $typeBadgeClass }}">
                                            {{ $transaction->manualTransaction->payment_method === 'cash' ? 'Tunai' : ($transaction->manualTransaction->payment_method === 'non_cash' ? 'Transfer' : ucfirst($transaction->manualTransaction->payment_method)) }}
                                        </span>
                                    @elseif ($transaction->type === 'qris')
                                        <span class="badge badge-status {{ $typeBadgeClass }}">QRIS</span>
                                        <br><small class="text-muted">{{ $transaction->qris_provider_label }}</small>
                                    @else
                                        <span class="badge badge-status {{ $typeBadgeClass }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $serviceLabel = 'Lainnya';
                                        $serviceClass = 'bg-secondary';
                                        if ($transaction->type === 'manual') {
                                            $serviceLabel = 'Drop Off';
                                            $serviceClass = 'badge-dropoff';
                                        } elseif ($transaction->type === 'qris') {
                                            $serviceLabel = 'Self Service';
                                            $serviceClass = 'badge-selfservice';
                                        } elseif ($transaction->type === 'member') {
                                            $serviceLabel = 'Member';
                                            $serviceClass = 'badge-member';
                                        }
                                    @endphp
                                    <span class="badge badge-status {{ $serviceClass }}">{{ $serviceLabel }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-status {{ $statusBadgeClass }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($transaction->created_at)->setTimezone($tz)->format('d-m-Y H:i:s') }}
                                    <br><small class="text-muted">{{ strtoupper($transaction->timezone) }}</small>
                                </td>
                             <td class="align-middle">
                            <div class="d-flex align-items-center gap-1">
                                <a href="{{ route($isAdminContext ? 'admin.transactions.show' : 'partner.transactions.show', $transaction->id) }}" class="btn btn-sm btn-info text-white" title="Lihat Detail Transaksi">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if ((Auth::guard('admin_config')->check() && !session('impersonating')) || (Auth::check() && Auth::user()->role === 'admin'))
                                    <form action="{{ route('admin.transactions.destroy', $transaction->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')"
                                        class="m-0"> @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">Tidak ada transaksi ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($showTotals)
                        <tfoot>
                            <tr>
                                <th colspan="1">Total:</th>
                                <th colspan="3">{{ $pageTransactionsCount }} Transaksi</th>
                                <th class="text-end">Total Jumlah:</th>
                                <th class="text-end">Rp {{ number_format($pageTransactionsAmount, 0, ',', '.') }}</th>
                                <th colspan="4"></th>
                            </tr>
                        </tfoot>
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

@push('styles')
    <link href="{{ asset('assets/plugins/gritter/css/jquery.gritter.css') }}" rel="stylesheet" />
    {{-- Tambahkan style jika ada untuk tampilan form owner, atau gunakan Bootstrap default --}}
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/gritter/js/jquery.gritter.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>
    <script>
        @if (session('success'))
            $.gritter.add({
                title: 'Success!',
                text: '{{ session('success') }}',
                sticky: false,
                time: 3000,
                class_name: 'gritter-light'
            });
        @endif

        @if (session('error'))
            $.gritter.add({
                title: 'Error!',
                text: '{{ session('error') }}',
                sticky: false,
                time: 3000,
                class_name: 'gritter-light'
            });
        @endif

        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('imagePreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        $(document).on('click', '.btn-check-payment-gateway', function() {
            const btn = $(this);
            const provider = String(btn.data('provider') || 'gateway').toUpperCase();
            const checkUrl = btn.data('check-url');
            const originalHtml = btn.html();

            btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

            $.ajax({
                url: checkUrl,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    btn.html(originalHtml).prop('disabled', false);

                    if (res.status === 'success') {
                        swal("Pembayaran Ditemukan di " + provider, res.message, "success")
                            .then(() => window.location.reload());
                        return;
                    }

                    if (res.status === 'not_found') {
                        swal("Belum Ada Pembayaran", res.message, "warning");
                        return;
                    }

                    swal("Info " + provider, res.message || 'Status belum berubah.', "info");
                },
                error: function() {
                    btn.html(originalHtml).prop('disabled', false);
                    swal("Gagal", "Tidak bisa mengecek status pembayaran saat ini.", "error");
                }
            });
        });
    </script>
@endpush
