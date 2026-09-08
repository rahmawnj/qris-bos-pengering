@props([
    'items' => ['Partner', 'Transaksi', 'Transaksi manual'],
    'title' => 'Transaksi manual',
    'subtitle' => 'Kelola dan pantau transaksi pembayaran manual.',
])

@extends('layouts.dashboard.app')

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
    </style>
@endpush

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
                    <h5 class="card-title">Total Transaksi (manual)</h5>
                    <p class="card-text h3">{{ $totalFilteredTransactionsCount }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card">
                <div class="icon-circle bg-success">
                    <i class="fa fa-wallet"></i>
                </div>
                <div>
                    <h5 class="card-title">Total Jumlah Uang (manual)</h5>
                    <p class="card-text h3">Rp {{ number_format($totalFilteredTransactionsAmount, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card">
                <div class="icon-circle bg-warning">
                    <i class="fa fa-check-circle"></i>
                </div>
                <div>
                    <h5 class="card-title">Transaksi Selesai (manual)</h5>
                    <p class="card-text h3">{{ $completedTransactionsCount }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card">
                <div class="icon-circle bg-info">
                    <i class="fa fa-mobile-alt"></i>
                </div>
                <div>
                    <h5 class="card-title">Perangkat Teraktivasi</h5>
                    <p class="card-text h3">{{ $activatedDeviceTransactionsCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3 mb-4">
        <form method="GET" action="{{ route('partner.transactions.manual') }}">
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
                        <option value="non_cash" {{ request('payment_method') == 'non_cash' ? 'selected' : '' }}>Non-Tunai</option>
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
                        <i class="fa fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('partner.transactions.manual') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-times me-1"></i> Reset
                    </a>
                </div>
                <div class="ms-auto">
                    <a href="{{ route('export.manual-transactions', request()->query()) }}" class="btn btn-success btn-sm">
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
            <th>Estimasi Selesai</th>
            {{-- Kolom baru untuk Jumlah & Unit --}}
            <th>Jumlah & Unit</th>
            <th>Jumlah Tagihan</th>
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
                        Outlet: {{ $transaction->outlet->address ?? 'N/A' }}<br>
                        Device: {{ $transaction->deviceTransactions->first()?->device_code ?? 'N/A' }}
                    </small>
                </td>
                <td>
                    {{ $transaction->deviceTransactions->first()?->device_code ?? 'N/A' }}
                </td>
                <td>
                    {{ $manualDetail->cashier_name ?? 'N/A' }}
                </td>
                <td>
                    @if ($manualDetail && $manualDetail->estimated_completion_at)
                        {{ \Carbon\Carbon::parse($manualDetail->estimated_completion_at)->setTimezone($tz)->format('d-m-Y H:i') }}
                        {{ strtoupper($transaction->timezone) }}
                    @else
                        N/A
                    @endif
                </td>
                {{-- Tampilkan Jumlah dan Unit --}}
                <td>
                    @if ($manualDetail)
                        <strong>{{ $manualDetail->quantity ?? 1 }}</strong>
                        {{ $manualDetail->unit ?? 'Unit' }}
                    @else
                        N/A
                    @endif
                </td>
                <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                <td><span class="badge bg-success">{{ ucfirst($transaction->status) }}</span></td>
                <td>
                    {{-- Tentukan teks dan kelas CSS berdasarkan payment_method --}}
                    @if ($manualDetail && $manualDetail->payment_method === 'cash')
                        <span class="badge badge-status bg-warning">Tunai</span>
                    @elseif ($manualDetail && $manualDetail->payment_method === 'non_cash')
                        <span class="badge badge-status bg-primary">Transfer</span>
                    @else
                        {{-- Fallback jika ada payment_method lain --}}
                        <span class="badge badge-status bg-secondary">{{ ucfirst($manualDetail->payment_method ?? 'N/A') }}</span>
                    @endif
                </td>
                <td>
                    {{ $transaction->created_at->setTimezone($tz)->format('d-m-Y H:i') }}
                    {{ strtoupper($transaction->timezone) }}
                </td>
                <td>
                    <x-print-invoice :transaction="$transaction" />

                    @if ($manualDetail)
                        <a href="#modal-dialog-{{ $transaction->id }}" class="btn btn-info btn-sm"
                            data-bs-toggle="modal">
                            <i class="fa fa-info-circle"></i>
                        </a>

                        <div class="modal fade" id="modal-dialog-{{ $transaction->id }}" tabindex="-1"
                            role="dialog" aria-labelledby="manualDetailModalLabel{{ $transaction->id }}"
                            aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"
                                            id="manualDetailModalLabel{{ $transaction->id }}">Detail
                                            Transaksi manual #{{ $transaction->order_id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6><i class="fa fa-qrcode"></i> Detail manual</h6>
                                                <hr class="mt-1 mb-2">
                                                <dl class="row">
                                                    <dt class="col-sm-4">Payment URL:</dt>
                                                    <dd class="col-sm-8">
                                                        {{-- Cek dulu apakah $manualDetail tersedia sebelum mengakses propertinya --}}
                                                        @if (isset($manualDetail->payment_url))
                                                            <a href="{{ $manualDetail->payment_url }}"
                                                                target="_blank">{{ Str::limit($manualDetail->payment_url, 40) }}</a>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </dd>
                                                </dl>
                                                <div class="text-center mt-3">
                                                    @if (isset($manualDetail->qr_code_image))
                                                        <img src="{{ asset('storage/' . $manualDetail->qr_code_image) }}"
                                                            alt="QR Code" class="img-thumbnail"
                                                            style="max-width: 200px; height: auto;">
                                                    @else
                                                        <p class="text-muted">Gambar QR Code tidak
                                                            tersedia.</p>
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

                                                    {{-- Tampilkan Jumlah Unit di Modal --}}
                                                    <dt class="col-sm-4">Jumlah Unit:</dt>
                                                    <dd class="col-sm-8">
                                                        <strong>{{ $manualDetail->quantity ?? 1 }}</strong>
                                                        {{ $manualDetail->unit ?? 'Unit' }}
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

                                                    {{-- Display payment method --}}
                                                    <dt class="col-sm-4">Metode Pembayaran:</dt>
                                                    <dd class="col-sm-8">
                                                        {{ ucfirst(str_replace('_', ' ', $manualDetail->payment_method ?? 'N/A')) }}
                                                    </dd>

                                                    <dt class="col-sm-4">Kasir:</dt>
                                                    <dd class="col-sm-8">
                                                        {{ $manualDetail->cashier_name ?? 'N/A' }}
                                                    </dd>

                                                    <dt class="col-sm-4">Estimasi Selesai:</dt>
                                                    <dd class="col-sm-8">
                                                        @if ($manualDetail->estimated_completion_at)
                                                            {{ \Carbon\Carbon::parse($manualDetail->estimated_completion_at)->setTimezone($tz)->format('d-m-Y H:i') }}
                                                            {{ strtoupper($transaction->timezone) }}
                                                        @else
                                                            N/A
                                                        @endif
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
                <td colspan="9" class="text-center">Tidak ada transaksi manual berhasil ditemukan.</td>
            </tr>
        @endforelse
    </tbody>
    {{-- FOOTER TABEL - Menggunakan totalFilteredTransactionsAmount dan totalFilteredTransactionsCount --}}
    <tfoot>
        <tr>
            {{-- Sesuaikan colspan menjadi 4 karena ada tambahan kolom Jumlah & Unit --}}
            <td colspan="6" class="text-end"><strong>Total Jumlah Tagihan</strong></td>
            <td><strong>Rp {{ number_format($totalFilteredTransactionsAmount, 0, ',', '.') }}</strong></td>
            <td colspan="5"></td>
        </tr>
        <tr>
            <td colspan="6" class="text-end"><strong>Jumlah Transaksi</strong></td>
            <td><strong>{{ $totalFilteredTransactionsCount }}</strong></td>
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
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" />
    <script type="text/javascript" src="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}">
    </script>

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
                },
                ranges: {
                    'Hari Ini': [moment(), moment()],
                    'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                    '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                    'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],
                    'Tahun Lalu': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
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
