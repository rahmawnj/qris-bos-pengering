@props([
    'items' => ['Admin', 'Transaksi', 'Transaksi QRIS'],
    'title' => 'Transaksi QRIS',
    'subtitle' => 'Kelola dan pantau transaksi pembayaran QRIS.',
])

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

.icon-circle.bg-primary { background-color: #007bff !important; }
.icon-circle.bg-success { background-color: #28a745 !important; }
.icon-circle.bg-info { background-color: #17a2b8 !important; }
.icon-circle.bg-warning { background-color: #ffc107 !important; }

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

.ribbon-paid {
    position: absolute;
    top: -5px;
    right: -5px;
    z-index: 1;
    overflow: hidden;
    width: 75px;
    height: 75px;
    text-align: right;
}
.ribbon-paid span {
    font-size: 10px;
    font-weight: bold;
    color: #FFF;
    text-transform: uppercase;
    text-align: center;
    line-height: 20px;
    transform: rotate(45deg);
    -webkit-transform: rotate(45deg);
    width: 100px;
    display: block;
    background: #28a745; /* Warna Hijau Sukses */
    background: linear-gradient(#2ecc71 0%, #27ae60 100%);
    box-shadow: 0 3px 10px -5px rgba(0, 0, 0, 1);
    position: absolute;
    top: 19px;
    right: -21px;
}
/* Opsional: Tambahkan efek lipatan kecil di pojok pita */
.ribbon-paid::before {
    content: "";
    top: 0; right: 0;
    position: absolute;
    border-left: 3px solid #1e7e34;
    border-top: 3px solid #1e7e34;
}
</style>
@endpush

@extends('layouts.dashboard.app')

@php
    $transactionsIndexRoute = $transactionsIndexRoute ?? route('admin.transactions.index', ['status' => 'success', 'type' => 'qris']);
    $qrisRoute = $qrisRoute ?? route('admin.transactions.qris');
    $devicesRoute = $devicesRoute ?? route('admin.devices.index');
    $exportRoute = $exportRoute ?? route('export.qris-transactions', request()->query());
@endphp

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    {{-- Card Summary --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="summary-card">
                <div class="icon-circle bg-primary">
                    <i class="fa fa-money-bill-alt"></i>
                </div>
                <div>
                    <h5 class="card-title">Total Transaksi (QRIS)</h5>
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
                    <h5 class="card-title">Total Jumlah Uang (QRIS)</h5>
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
                    <h5 class="card-title">Rata-rata Transaksi Harian (QRIS)</h5>
                    <p class="card-text h3">{{ number_format($averageDailyTransactions, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card">
                <div class="icon-circle bg-info">
                    <i class="fa fa-mobile-alt"></i>
                </div>
                <div>
                    <h5 class="card-title">Transaksi Perangkat Teraktivasi</h5>
                    <p class="card-text h3">{{ $activatedDeviceTransactionsCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3 mb-4">
        <form method="GET" action="{{ $qrisRoute }}">
            <div class="d-flex flex-wrap align-items-end gap-3">
                <div style="min-width: 240px; flex: 1;">
                    <label for="filter-daterange" class="form-label mb-1 fw-bold">Rentang Waktu</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                        <input type="text" name="daterange" class="form-control daterange-picker" id="filter-daterange"
                            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                            value="{{ request('daterange') }}" placeholder="Pilih Rentang Waktu">
                    </div>
                </div>
                <div style="min-width: 150px; flex: 1;">
                    <label for="filter-provider" class="form-label mb-1 fw-bold">Provider</label>
                    <select name="provider" id="filter-provider" class="form-select form-select-sm">
                        <option value="">Semua Provider</option>
                        <option value="midtrans" {{ request('provider') === 'midtrans' ? 'selected' : '' }}>Midtrans</option>
                        <option value="xendit" {{ request('provider') === 'xendit' ? 'selected' : '' }}>Xendit</option>
                    </select>
                </div>
                <div style="min-width: 280px; flex: 2;">
                    <label for="filter-search" class="form-label mb-1 fw-bold">Pencarian</label>
                    <input type="text" name="search" id="filter-search" class="form-control form-control-sm"
                        placeholder="Cari (ID Pesanan, Owner, Jumlah)" value="{{ request('search') }}">
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-filter me-1"></i> Terapkan Filter
                    </button>
                    <a href="{{ $qrisRoute }}" class="btn btn-default btn-sm">
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
                            <th>Kode Device</th>
                            <th>Tipe</th>
                            <th>Provider</th>
                            <th>Jumlah</th>
                            <th>Status</th>
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
                                $displayDeviceCode = $transaction->deviceTransactions->first()?->device_code
                                    ?? $transaction->qrisTransaction?->device_code
                                    ?? 'N/A';
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $transaction->order_id }}
                                </td>
                                <td>
                                    <strong>{{ $transaction->owner->brand_name ?? '-' }}</strong><br>
                                    <small class="text-muted">
                                        Outlet: {{ $transaction->outlet->address ?? 'N/A' }}<br>
                                        Device: {{ $displayDeviceCode }}
                                    </small>
                                </td>
                                <td>{{ $displayDeviceCode }}</td>
                                <td>
                                    @php
                                        $serviceType = strtolower((string)($transaction->deviceTransactions->first()?->service_type ?? ''));
                                        $deviceTypeLabel = str_contains($serviceType, 'dryer') ? 'Dryer' : (str_contains($serviceType, 'washer') ? 'Washer' : 'N/A');
                                    @endphp
                                    {{ $deviceTypeLabel }}
                                </td>
                                <td><span class="badge bg-dark">{{ $transaction->qris_provider_label }}</span></td>
                                <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                <td><span class="badge bg-success">{{ ucfirst($transaction->status) }}</span></td>
                                <td>
                                    {{ $transaction->created_at->setTimezone($tz)->format('d-m-Y H:i') }}
                                    {{ strtoupper($transaction->timezone) }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        @if ($transaction->qrisTransaction)
                                            <a href="{{ route($transactionShowRoute ?? 'admin.transactions.show', $transaction) }}"
                                                class="btn btn-info btn-sm text-white"
                                                title="Lihat Detail Transaksi">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">Detail tidak tersedia</span>
                                        @endif

                                        @if (isset($isAdminContext) ? $isAdminContext : true)
                                            @if ($transaction->status === 'pending')
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-primary btn-check-payment-gateway text-white"
                                                    title="Cek Status {{ $transaction->qris_provider_label }}"
                                                    data-provider="{{ strtolower($transaction->qris_provider_label) }}"
                                                    data-check-url="{{ route('admin.transactions.check_payment_gateway', $transaction->id) }}">
                                                    <i class="fa fa-search-dollar"></i>
                                                </button>

                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-warning text-dark"
                                                    title="Upload Bukti Pembayaran"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#proofUploadModal-{{ $transaction->id }}">
                                                    <i class="fa fa-upload"></i>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                    @if (isset($isAdminContext) ? $isAdminContext : true)
                                        @if ($transaction->status === 'pending')
                                            <div class="modal fade" id="proofUploadModal-{{ $transaction->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form action="{{ route('admin.transactions.bypass', $transaction->id) }}" method="POST" enctype="multipart/form-data" class="modal-content">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Upload Bukti Pembayaran</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Order ID</label>
                                                                <input type="text" class="form-control" value="{{ $transaction->order_id }}" readonly>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="proof_of_payment_{{ $transaction->id }}" class="form-label fw-bold">Bukti Pembayaran</label>
                                                                <input class="form-control" type="file" id="proof_of_payment_{{ $transaction->id }}" name="proof_of_payment" accept="image/*" required>
                                                            </div>
                                                            <p class="text-muted mb-0 text-start">Bukti ini akan masuk ke daftar verifikasi manual dan bisa diaktifkan sebagai bypass bila pembayaran gateway tidak terbaca.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-warning text-dark fw-bold">
                                                                <i class="fa fa-upload me-1"></i> Upload
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Tidak ada transaksi QRIS berhasil ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    {{-- FOOTER TABEL - Menggunakan totalFilteredTransactionsAmount dan totalFilteredTransactionsCount --}}
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end"><strong>Total Jumlah</strong></td>
                            <td><strong>Rp {{ number_format($totalFilteredTransactionsAmount, 0, ',', '.') }}</strong></td>
                            <td colspan="4"></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end"><strong>Jumlah Transaksi</strong></td>
                            <td><strong>{{ $totalFilteredTransactionsCount }}</strong></td>
                            <td colspan="4"></td>
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
