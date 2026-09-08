@props([
    'items' => ['Admin', 'Withdrawal Management', 'Pending Requests'],
    'title' => 'Permintaan Penarikan Dana (Pending)',
    'subtitle' => 'Lihat daftar permintaan penarikan dana yang perlu diproses.',
])

{{-- Pastikan variabel $pendingWithdrawalCount, $withdrawals, $totalAmountInTable, dan $totalCountInTable sudah di-pass dari Controller. --}}

@push('styles')
    <link href="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />
    <style>
        /* CSS yang relevan untuk halaman ini */
        .summary-card {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: .5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
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

        .icon-circle.bg-warning-dark { 
            background-color: #ffc107 !important;
            color: #343a40 !important;
        }

        .summary-card .card-title {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .summary-card .card-text.h3 {
            font-size: 1.75rem;
            margin-bottom: 0;
            color: #343a40;
        }

        .top-summary-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between; /* Mendorong tombol ke kanan */
            align-items: flex-end; /* Nempel ke bawah */
            margin-bottom: 20px;
        }

        /* Filter & Table Specific Styles */
        .filter-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .filter-group .form-control {
            height: calc(1.5em + .75rem + 2px);
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

        /* Responsive table */
        .table-responsive {
            overflow-x: auto;
        }

        @media (max-width: 575.98px) {
            .top-summary-container {
                display: block;
            }

            .top-summary-container .summary-card {
                width: 100%;
                min-width: 0 !important;
            }

            .top-summary-action {
                width: 100%;
                margin-top: 1rem;
            }

            .top-summary-action .btn {
                width: 100%;
                white-space: normal;
            }
        }
    </style>
@endpush

@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    {{-- Bagian Ringkasan & Tombol Aksi --}}
    <div class="top-summary-container">
        {{-- Summary Card --}}
        <div class="summary-card" style="min-width: 250px;">
            <div class="icon-circle bg-warning-dark">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div>
                <div class="card-title">Total Permintaan Pending</div>
                <div class="card-text h3">{{ $pendingWithdrawalCount }}</div>
            </div>
        </div>

        {{-- Tombol Aksi (Admin Reduce) --}}
        <div class="top-summary-action">
            <a href="{{ route('admin.withdrawal.reduce.create') }}" class="btn btn-danger btn-lg shadow-sm">
                <i class="fas fa-minus-circle me-2"></i> Lakukan Pengurangan Saldo
            </a>
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

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>Owner (Brand)</th>
                            <th>Biaya Admin</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Diajukan</th>
                            <th>Dikonfirmasi</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($withdrawals as $withdrawal)
                            @php
                                $timezoneMap = [
                                    'wib' => 'Asia/Jakarta',
                                    'wita' => 'Asia/Makassar',
                                    'wit' => 'Asia/Jayapura',
                                ];
                                $tzKey = strtolower($withdrawal->timezone ?? 'wib');
                                $tz = $timezoneMap[$tzKey] ?? 'Asia/Jakarta';

                                $statusBadgeClass = '';
                                switch ($withdrawal->status) {
                                    case 'pending':
                                        $statusBadgeClass = 'bg-warning text-dark';
                                        break;
                                    case 'approved':
                                        $statusBadgeClass = 'bg-success';
                                        break;
                                    case 'rejected':
                                        $statusBadgeClass = 'bg-danger';
                                        break;
                                    default:
                                        $statusBadgeClass = 'bg-secondary';
                                        break;
                                }
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $withdrawal->owner->brand_name ?? '-' }}</strong><br>
                                    <small class="text-muted">{{ $withdrawal->owner->user->name ?? '-' }}</small>
                                </td>
                                <td>Rp {{ number_format((int) ($withdrawal->withdrawal_fee ?? 0), 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $statusBadgeClass }}">
                                        {{ ucfirst($withdrawal->status) }}
                                    </span>
                                </td>
                                <td>{{ $withdrawal->notes ?? '-' }}</td>
                                <td>
                                    {{ $withdrawal->created_at->setTimezone($tz)->format('d-m-Y H:i') }}
                                    <small class="text-muted d-block">{{ strtoupper($withdrawal->timezone ?? 'WIB') }}</small>
                                </td>
                                <td>
                                    @if ($withdrawal->approved_at)
                                        {{ \Carbon\Carbon::parse($withdrawal->approved_at)->setTimezone($tz)->format('d-m-Y H:i') }}
                                        <small class="text-muted d-block">{{ strtoupper($withdrawal->timezone ?? 'WIB') }}</small>
                                    @elseif ($withdrawal->rejected_at)
                                        Ditolak pada<br>
                                        {{ \Carbon\Carbon::parse($withdrawal->rejected_at)->setTimezone($tz)->format('d-m-Y H:i') }}
                                        <small class="text-muted d-block">{{ strtoupper($withdrawal->timezone ?? 'WIB') }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($withdrawal->status == 'pending')
                                        <a href="{{ route('admin.withdrawal.request', $withdrawal->id) }}"
                                            class="btn btn-sm btn-info me-1" title="Lihat Detail & Proses">
                                            <i class="fas fa-eye"></i> Proses
                                        </a>
                                    @else
                                        <span class="text-muted">Sudah diproses</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Tidak ada permintaan penarikan dana yang sedang pending.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-end">Total yang Ditampilkan:</th>
                            <th>Rp {{ number_format((int) $withdrawals->sum('withdrawal_fee'), 0, ',', '.') }}</th>
                            <th>Rp {{ number_format($totalAmountInTable, 0, ',', '.') }}</th>
                            <th colspan="4"></th>
                            <th class="text-end">Jumlah Permintaan (Filter):</th>
                            <th>{{ $totalCountInTable }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Optional: Pagination, jika Anda menggunakan paginate() di controller --}}
            {{-- <div class="pagination-container">
                {{ $withdrawals->links() }}
            </div> --}}

        </div>
    </div>
@endsection

@push('scripts')
    {{-- Jika Anda membutuhkan JavaScript untuk daterangepicker atau modal, tambahkan di sini --}}
    <script src="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
@endpush
