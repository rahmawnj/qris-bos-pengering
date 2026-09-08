@props([
    'items' => ['Admin', 'Transaksi', 'Transaksi Member'],
    'title' => 'Transaksi Member',
    'subtitle' => 'Kelola dan pantau transaksi pembayaran oleh member.',
])

@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    {{-- Card Summary --}}
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="widget widget-stats bg-blue">
                <div class="stats-icon"><i class="fa fa-users"></i></div>
                <div class="stats-info">
                    <h4>Total Transaksi (Member)</h4>
                    <p>{{ $totalMemberTransactionsCountOverall }}</p>
                </div>
                <div class="stats-link">
                    <a href="javascript:;">Lihat Detail <i class="fa fa-arrow-alt-circle-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="widget widget-stats bg-green">
                <div class="stats-icon"><i class="fa fa-money-bill-wave"></i></div>
                <div class="stats-info">
                    <h4>Total Jumlah Uang (Member)</h4>
                    <p>Rp {{ number_format($totalMemberTransactionsAmountOverall, 0, ',', '.') }}</p>
                </div>
                <div class="stats-link">
                    <a href="javascript:;">Lihat Detail <i class="fa fa-arrow-alt-circle-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="widget widget-stats bg-yellow">
                <div class="stats-icon"><i class="fa fa-check-circle"></i></div>
                <div class="stats-info">
                    <h4>Transaksi Selesai (Member)</h4>
                    <p>{{ $completedMemberTransactionsCount }}</p>
                </div>
                <div class="stats-link">
                    <a href="javascript:;">Lihat Detail <i class="fa fa-arrow-alt-circle-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="widget widget-stats bg-purple">
                <div class="stats-icon"><i class="fa fa-user-check"></i></div>
                <div class="stats-info">
                    <h4>Member Terverifikasi</h4>
                    <p>{{ $verifiedMembersCount }}</p> {{-- New summary for verified members --}}
                </div>
                <div class="stats-link">
                    <a href="javascript:;">Lihat Detail <i class="fa fa-arrow-alt-circle-right"></i></a>
                </div>
            </div>
        </div>
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
            <form method="GET" action="{{ route('admin.transaction.member') }}" class="mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="input-group input-group-sm" style="max-width: 280px;">
                        <input type="text" name="daterange" class="form-control daterange-picker"
                            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                            value="{{ request('daterange') }}" placeholder="Pilih Rentang Tanggal">
                        <span class="input-group-append">
                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Cari (ID Pesanan, Nama Member, Nomor HP)" value="{{ request('search') }}">
                    </div>
                    <div class="ms-auto">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
                        <a href="{{ route('admin.transaction.member') }}" class="btn btn-default btn-sm"><i
                                class="fa fa-times"></i> Reset</a>
                       
                        <a href="{{ route('export.admin-transactions', array_merge(request()->query(), ['type' => 'member', 'status' => 'success'])) }}"
                            class="btn btn-success btn-sm">
                            <i class="fa fa-file-excel"></i> Export ke Excel
                        </a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="1">#</th>
                            <th>ID Pesanan</th>
                            <th>Member</th>
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
                                <td>{{ $transaction->order_id }}</td>
                                <td>
                                    @if ($transaction->memberTransactionDetail && $transaction->memberTransactionDetail->member)
                                        <strong>{{ $transaction->memberTransactionDetail->member->user->name ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">
                                            No. HP:
                                            {{ $transaction->memberTransactionDetail->member->phone_number ?? 'N/A' }}<br>
                                            Status Verifikasi:
                                            @if ($transaction->memberTransactionDetail->member->is_verify)
                                                <span class="badge bg-success">Terverifikasi</span>
                                            @else
                                                <span class="badge bg-danger">Belum Terverifikasi</span>
                                            @endif
                                        </small>
                                    @else
                                        <small class="text-muted">Member tidak ditemukan</small>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                <td><span class="badge bg-success">{{ ucfirst($transaction->status) }}</span></td>
                                <td>
                                    {{ $transaction->created_at->setTimezone($tz)->format('d-m-Y H:i') }}
                                    {{ strtoupper($transaction->timezone) }}
                                </td>
                                <td>
                                    @if ($transaction->memberTransactionDetail)
                                        <a href="#modal-member-detail-{{ $transaction->id }}" class="btn btn-info btn-sm"
                                            data-bs-toggle="modal">
                                            <i class="fa fa-info-circle"></i> Detail
                                        </a>

                                        <div class="modal fade" id="modal-member-detail-{{ $transaction->id }}"
                                            tabindex="-1" role="dialog"
                                            aria-labelledby="memberDetailModalLabel{{ $transaction->id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="memberDetailModalLabel{{ $transaction->id }}">
                                                            Detail Transaksi Member #{{ $transaction->order_id }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
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
                                                            <div class="col-md-6">
                                                                <h6><i class="fa fa-user"></i> Detail Member</h6>
                                                                <hr class="mt-1 mb-2">
                                                                <dl class="row">
                                                                    <dt class="col-sm-4">Nama Member:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ $transaction->memberTransactionDetail->member->user->name ?? 'N/A' }}
                                                                    </dd>

                                                                    <dt class="col-sm-4">Nomor HP:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ $transaction->memberTransactionDetail->member->phone_number ?? 'N/A' }}
                                                                    </dd>

                                                                    <dt class="col-sm-4">Terverifikasi:</dt>
                                                                    <dd class="col-sm-8">
                                                                        @if ($transaction->memberTransactionDetail->member->is_verify)
                                                                            <span class="badge bg-success">Ya</span>
                                                                        @else
                                                                            <span class="badge bg-danger">Tidak</span>
                                                                        @endif
                                                                    </dd>
                                                                    {{-- Add any other relevant member details here --}}
                                                                </dl>
                                                            </div>
                                                        </div>

                                                        {{-- You might want to add device transaction details if a member transaction can still involve devices,
                                                        or remove this section if it's purely member-centric and doesn't involve device activations.
                                                        For now, I'm keeping a placeholder for it, assuming it might be relevant. --}}
                                                        <h6 class="mt-4"><i class="fa fa-desktop"></i> Detail Transaksi
                                                            Perangkat
                                                        </h6>
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
                                                                untuk transaksi
                                                                ini.</p>
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
                                <td colspan="7" class="text-center">Tidak ada transaksi member ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    {{-- FOOTER TABEL - Menggunakan totalFilteredTransactionsAmount dan totalFilteredTransactionsCount --}}
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total Jumlah (Semua Halaman):</strong></td>
                            <td><strong>Rp
                                    {{ number_format($totalFilteredMemberTransactionsAmount, 0, ',', '.') }}</strong></td>
                            <td colspan="3"></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Jumlah Transaksi (Semua Halaman):</strong></td>
                            <td><strong>{{ $totalFilteredMemberTransactionsCount }}</strong></td>
                            <td colspan="3"></td>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
