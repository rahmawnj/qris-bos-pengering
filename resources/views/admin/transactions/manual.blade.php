@props([
    'items' => ['Admin', 'Transaksi', 'Transaksi Drop - Off'],
    'title' => 'Transaksi Drop - Off',
    'subtitle' => 'Kelola dan pantau transaksi pembayaran Drop - Off.',
])

@extends('layouts.dashboard.app')

@php
    $manualRoute = $manualRoute ?? route('admin.transactions.manual');
    $exportRoute = $exportRoute ?? route('export.manual-transactions', request()->query());
@endphp

@push('styles')
    <style>
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

        .summary-card .card-title {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .summary-card .card-text.h3 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            color: #343a40;
        }
    </style>
@endpush

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    {{-- Card Summary --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="summary-card">
                <div class="icon-circle bg-primary">
                    <i class="fa fa-cash-register"></i>
                </div>
                <div>
                    <h5 class="card-title">Total Transaksi (Manual)</h5>
                    <p class="card-text h3">{{ $totalTransactionsCountOverall }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card">
                <div class="icon-circle bg-success">
                    <i class="fa fa-wallet"></i>
                </div>
                <div>
                    <h5 class="card-title">Total Jumlah Uang (Manual)</h5>
                    <p class="card-text h3">Rp {{ number_format($totalTransactionsAmountOverall, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card">
                <div class="icon-circle bg-warning">
                    <i class="fa fa-check-circle"></i>
                </div>
                <div>
                    <h5 class="card-title">Pemasukan Harian (Manual)</h5>
                    <p class="card-text h3">Rp {{ number_format($averageDailyIncome, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card">
                <div class="icon-circle bg-info">
                    <i class="fa fa-mobile-alt"></i>
                </div>
                <div>
                    <h5 class="card-title">Perangkat Dijalankan</h5>
                    <p class="card-text h3">{{ $activatedDeviceTransactionsCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3 mb-4">
        <form method="GET" action="{{ $manualRoute }}">
            <div class="d-flex flex-wrap align-items-end gap-3">
                <div style="min-width: 240px; flex: 1;">
                    <label for="filter-daterange" class="form-label mb-1 fw-bold">Rentang Waktu</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                        <input type="text" name="daterange" class="form-control daterange-picker"
                            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                            id="filter-daterange"
                            value="{{ request('daterange') }}" placeholder="Pilih Rentang Waktu">
                    </div>
                </div>
                <div style="min-width: 200px;">
                    <label for="filter-payment" class="form-label mb-1 fw-bold">Metode Pembayaran</label>
                    <select name="payment_method" id="filter-payment" class="form-control form-control-sm">
                        <option value="">Semua Metode Pembayaran</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                        <option value="non_cash" {{ request('payment_method') == 'non_cash' ? 'selected' : '' }}>Transfer</option>
                    </select>
                </div>
                <div style="min-width: 280px; flex: 2;">
                    <label for="filter-search" class="form-label mb-1 fw-bold">Pencarian</label>
                    <input type="text" name="search" id="filter-search" class="form-control form-control-sm"
                        placeholder="Cari (ID Pesanan, Owner, Jumlah, Layanan, Notes, Pelanggan, No HP)" value="{{ request('search') }}">
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ $manualRoute }}" class="btn btn-default btn-sm">
                        <i class="fa fa-times me-1"></i> Reset
                    </a>
                </div>
                <div class="ms-auto">
                    <a href="{{ $exportRoute }}" class="btn btn-success btn-sm">
                        <i class="fa fa-file-excel me-1"></i> Export ke Excel
                    </a>
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
            <th width="1">#</th>
            <th>ID Pesanan</th>
            <th>Owner & Outlet</th>
            <th>Device Code</th>
            <th>Kasir</th>
            <th>Pelanggan</th>
            <th>Estimasi Selesai</th>
            <th>Jumlah Tagihan</th>
            {{-- Kolom Layanan & Metode Bayar akan diperluas untuk mencakup Unit/Quantity --}}
            <th>Layanan, Unit & Metode Bayar</th>
            <th>Status</th>
            <th>Metode Pembayaran</th>
            <th>Tanggal</th>
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

                // Ambil Manual Transaction Detail
                $manualDetail = $transaction->manualTransaction ?? null;
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    {{ $transaction->order_id }}
                </td>
                <td>
                    <strong>{{ $transaction->owner->brand_name ?? '-' }}</strong><br>
                    <small class="text-muted">
                        Outlet: {{ $transaction->outlet->outlet_name ?? 'N/A' }}<br>
                        Device: {{ $transaction->device_code ?? ($transaction->deviceTransactions->first()?->device_code ?? 'N/A') }}
                    </small>
                </td>
                <td>
                    {{ $transaction->device_code ?? ($transaction->deviceTransactions->first()?->device_code ?? 'N/A') }}
                </td>
                <td>
                    {{ $manualDetail->cashier_name ?? 'N/A' }}
                </td>
                <td>
                    @if ($manualDetail)
                        {{ $manualDetail->customer_name ?? 'N/A' }}<br>
                        <small class="text-muted">{{ $manualDetail->customer_phone_number ?? '-' }}</small>
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    @if ($manualDetail && $manualDetail->estimated_completion_at)
                        {{ \Carbon\Carbon::parse($manualDetail->estimated_completion_at)->setTimezone($tz)->format('d-m-Y H:i') }}
                        {{ strtoupper($transaction->timezone) }}
                    @else
                        N/A
                    @endif
                </td>
                <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                {{-- Kolom Layanan, Unit & Metode Bayar --}}
                <td>
                    @if ($manualDetail)
                        Layanan: <strong>{{ $manualDetail->service->name ?? 'N/A' }}</strong><br>
                        Jumlah: {{ $manualDetail->quantity ?? 1 }} {{ $manualDetail->unit ?? 'Unit' }}<br>
                        Metode: {{ ucfirst(str_replace('_', ' ', $manualDetail->payment_method)) }}
                    @else
                        N/A
                    @endif
                </td>
                <td><span class="badge bg-success">{{ ucfirst($transaction->status) }}</span></td>
                <td>
                    {{-- Tentukan teks dan kelas CSS berdasarkan payment_method --}}
                    @if ($manualDetail && $manualDetail->payment_method === 'cash')
                        <span class="badge bg-warning">Tunai</span>
                    @elseif ($manualDetail && $manualDetail->payment_method === 'non_cash')
                        <span class="badge bg-primary">Transfer</span>
                    @else
                        {{-- Fallback jika ada payment_method lain --}}
                        <span class="badge bg-secondary">{{ ucfirst($manualDetail->payment_method ?? 'N/A') }}</span>
                    @endif
                </td>
                <td>
                    {{ $transaction->created_at->setTimezone($tz)->format('d-m-Y H:i') }}
                    {{ strtoupper($transaction->timezone) }}
                </td>
	                <td>
		                    <a href="{{ route($transactionShowRoute ?? 'admin.transactions.show', $transaction) }}"
		                        class="btn btn-info btn-sm"
		                        title="Lihat Detail Transaksi">
		                        <i class="fa fa-eye"></i>
	                    </a>
	                </td>
	            </tr>
        @empty
            <tr>
                <td colspan="13" class="text-center">Tidak ada transaksi manual berhasil ditemukan.</td>
            </tr>
        @endforelse
    </tbody>
            {{-- FOOTER TABEL --}}
            <tfoot>
                <tr>
                    {{-- Colspan disesuaikan menjadi 4 (karena ada 9 kolom total, dan 9 - 5 = 4 kolom sebelum kolom Jumlah Tagihan) --}}
                    <td colspan="7" class="text-end"><strong>Total Jumlah Tagihan (Semua Halaman):</strong></td>
                    <td><strong>Rp {{ number_format($totalFilteredTransactionsAmount, 0, ',', '.') }}</strong></td>
                    <td colspan="5"></td>
                </tr>
                <tr>
                    <td colspan="7" class="text-end"><strong>Jumlah Transaksi (Semua Halaman):</strong></td>
                    <td colspan="1"><strong>{{ $totalFilteredTransactionsCount }}</strong></td>
                    <td colspan="5"></td>
                </tr>
            </tfoot>
</table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                {{-- Text di bawah pagination tetap menunjukkan total record yang ditemukan --}}
                <div class="small text-muted">
                    {{ $transactions->firstItem() }}-{{ $transactions->lastItem() }} / {{ $transactions->total() }}
                </div>
                <div>{{ $transactions->links() }}</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}

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
