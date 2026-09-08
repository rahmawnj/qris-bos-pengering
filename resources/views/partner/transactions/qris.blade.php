@props([
    'items' => ['Partner', 'Transaksi', 'Transaksi QRIS'],
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
        <form method="GET" action="{{ route('partner.qris.transactions') }}">
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
                    <a href="{{ route('partner.qris.transactions') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-times me-1"></i> Reset
                    </a>
                </div>
                <div class="ms-auto">
                    <a href="{{ route('export.qris-transactions', request()->query()) }}" class="btn btn-success btn-sm">
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
                                        Device: {{ $transaction->deviceTransactions->first()?->device_code ?? 'N/A' }}
                                    </small>
                                </td>
                                <td>{{ $transaction->deviceTransactions->first()?->device_code ?? 'N/A' }}</td>
                                <td>
                                    @php
                                        $serviceType = strtolower((string)($transaction->deviceTransactions->first()?->service_type ?? ''));
                                        $deviceTypeLabel = str_contains($serviceType, 'dryer') ? 'Dryer' : (str_contains($serviceType, 'washer') ? 'Washer' : 'N/A');
                                    @endphp
                                    {{ $deviceTypeLabel }}
                                </td>
                                <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                <td><span class="badge bg-success">{{ ucfirst($transaction->status) }}</span></td>
                                <td>
                                    {{ $transaction->created_at->setTimezone($tz)->format('d-m-Y H:i') }}
                                    {{ strtoupper($transaction->timezone) }}
                                </td>
                                <td>
                                    <x-print-invoice :transaction="$transaction" />

                                    @if ($transaction->qrisTransaction)
                                        <a href="#modal-dialog-{{ $transaction->id }}" class="btn btn-info btn-sm"
                                            data-bs-toggle="modal">
                                            <i class="fa fa-info-circle"></i>
                                        </a>

                                        <div class="modal fade" id="modal-dialog-{{ $transaction->id }}" tabindex="-1"
                                            role="dialog" aria-labelledby="qrisDetailModalLabel{{ $transaction->id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="qrisDetailModalLabel{{ $transaction->id }}">Detail
                                                            Transaksi QRIS #{{ $transaction->order_id }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6><i class="fa fa-qrcode"></i> Detail QRIS</h6>
                                                                <hr class="mt-1 mb-2">
                                                                <dl class="row">
                                                                    <dt class="col-sm-4">Payment URL:</dt>
                                                                    <dd class="col-sm-8"><a
                                                                            href="{{ $transaction->qrisTransaction->payment_url }}"
                                                                            target="_blank">{{ Str::limit($transaction->qrisTransaction->payment_url, 40) }}</a>
                                                                    </dd>
                                                                </dl>
                                                                <div class="text-center mt-3">
                                                              @if ($transaction->qrisTransaction->qr_code_image)
    <div class="position-relative d-inline-block">
        <div class="ribbon-paid">
            <span>DIBAYAR</span>
        </div>

        <img src="{{ url('storage/qrcodes/' . $transaction->qrisTransaction->qr_code_image) }}"
             alt="QR Code" class="img-thumbnail"
             style="max-width: 200px; height: auto;">
    </div>
@else
    <p class="text-muted">Gambar QR Code tidak tersedia.</p>
@endif
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6><i class="fa fa-info-circle"></i> Data Transaksi Utama
                                                                </h6>
                                                                <hr class="mt-1 mb-2">
                                                                <dl class="row">
                                                                    <dt class="col-sm-4">Order ID:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ $transaction->order_id ?? 'Belum Lengkap' }}
                                                                    </dd>

                                                                    <dt class="col-sm-4">Jumlah:</dt>
                                                                    <dd class="col-sm-8">Rp
                                                                        {{ number_format($transaction->amount, 0) ?? 'Belum Lengkap' }}
                                                                    </dd>

                                                                    <dt class="col-sm-4">Owner:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ $transaction->owner->brand_name ?? 'Belum Lengkap' }}
                                                                    </dd>

                                                                    <dt class="col-sm-4">Outlet:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ $transaction->outlet->outlet_name ?? 'Belum Lengkap' }}
                                                                        ({{ $transaction->outlet->address ?? 'Belum Lengkap' }})
                                                                    </dd>

                                                                    <dt class="col-sm-4">Device Code:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ $transaction->device_code ?? 'Belum Lengkap' }}
                                                                    </dd>

                                                                    <dt class="col-sm-4">Status Transaksi:</dt>
                                                                    <dd class="col-sm-8"><span
                                                                            class="badge bg-success">{{ ucfirst($transaction->status) ?? 'Belum Lengkap' }}</span>
                                                                    </dd>

                                                                    <dt class="col-sm-4">Tanggal Transaksi:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ $transaction->created_at->setTimezone($tz)->format('d-m-Y H:i') }}
                                                                        {{ strtoupper($transaction->timezone) }}</dd>
                                                                </dl>
                                                            </div>
                                                        </div>

                                                        <h6 class="mt-4"><i class="fa fa-desktop"></i> Detail Transaksi
                                                            Perangkat</h6>
                                                        <hr class="mt-1 mb-2">
                                                        @if ($transaction->deviceTransactions->isNotEmpty())
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered table-sm mt-3">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Kode Perangkat</th>
                                                                            <th>Tipe Layanan</th>
                                                                            <th>Status Jalankan</th>
                                                                            <th>Waktu Aktivasi</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($transaction->deviceTransactions as $deviceTrans)
                                                                            <tr>
                                                                                <td>{{ $deviceTrans->device->code ?? $deviceTrans->device_code }}
                                                                                </td>
                                                                                <td>{{ ucfirst($deviceTrans->service_type) }}
                                                                                </td>
                                                                                <td>
                                                                                    @if ($deviceTrans->activated_at)
                                                                                        <span
                                                                                            class="badge bg-success">Dijalankan</span>
                                                                                    @else
                                                                                        <span
                                                                                            class="badge bg-warning text-dark">Belum
                                                                                            Dijalankan</span>
                                                                                    @endif
                                                                                </td>
                                                                                <td>
                                                                                    @if ($deviceTrans->activated_at)
                                                                                        {{ \Carbon\Carbon::parse($deviceTrans->activated_at)->setTimezone($tz)->format('d-m-Y H:i') }}
                                                                                    @else
                                                                                        N/A
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @else
                                                            <p class="text-muted">Tidak ada detail transaksi perangkat
                                                                untuk transaksi ini.</p>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Detail tidak tersedia</span>
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
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" />
    <script type="text/javascript" src="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>

    <script>
        $(function() {
            // Inisialisasi Date Range Picker
            $('.daterange-picker').daterangepicker({
                opens: 'left',
                locale: {
                    format: 'YYYY-MM-DD',
                    applyLabel: 'Terapkan',
                    cancelLabel: 'Batal',
                    fromLabel: 'Dari',
                    toLabel: 'Sampai',
                    customRangeLabel: 'Rentang Kustom',
                    daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                        'September', 'Oktober', 'November', 'Desember'
                    ],
                    firstDay: 1
                }
            });

            // Set nilai input saat tanggal dipilih
            $('.daterange-picker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
            });

            // Hapus nilai input jika filter di-reset
            $('.daterange-picker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            // Set nilai awal datepicker jika ada di URL
            var daterange = "{{ request('daterange') }}";
            if (daterange) {
                $('.daterange-picker').val(daterange);
            }
        });
    </script>
@endpush
