@props([
    'items' => ['Admin', 'QRIS', 'Outlet Diblokir'],
    'title' => 'Outlet Diblokir (Belum Bayar Perpanjangan)',
    'subtitle' => 'Daftar outlet yang melewati jatuh tempo perpanjangan QRIS dan belum melakukan pembayaran.',
])

@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @include('qris_billing._summary', ['summary' => $summary])

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="fa fa-ban text-danger me-1"></i> Outlet Diblokir
            </h4>
            <div class="panel-heading-btn">
                <a href="{{ route('admin.qris-billing.index') }}" class="btn btn-xs btn-primary">
                    <i class="fa fa-clock me-1"></i> Menunggu Konfirmasi
                </a>
                <a href="{{ route('admin.qris-billing.report') }}" class="btn btn-xs btn-default">
                    <i class="fa fa-file-invoice me-1"></i> Laporan
                </a>
            </div>
        </div>
        <div class="panel-body">
            <form method="GET" action="{{ route('admin.qris-billing.blocked') }}" class="mb-3">
                <div class="d-flex flex-wrap align-items-end gap-2">
                    <div style="min-width: 220px;">
                        <label class="form-label mb-1 fw-bold">Owner</label>
                        <select name="owner_id" class="form-select form-select-sm">
                            <option value="">Semua Owner</option>
                            @foreach ($owners as $owner)
                                <option value="{{ $owner->id }}" {{ (string) $ownerId === (string) $owner->id ? 'selected' : '' }}>
                                    {{ $owner->brand_name ?? $owner->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div style="min-width: 240px; flex: 1;">
                        <label class="form-label mb-1 fw-bold">Pencarian</label>
                        <input type="text" name="search" class="form-control form-control-sm" value="{{ $search }}" placeholder="Outlet, kode, owner">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter me-1"></i> Filter</button>
                    <a href="{{ route('admin.qris-billing.blocked') }}" class="btn btn-default btn-sm">Reset</a>
                </div>
            </form>

            <div class="alert alert-danger bg-danger text-white border-0 d-flex justify-content-between flex-wrap gap-2 mb-3">
                <span><i class="fa fa-exclamation-triangle me-1"></i> Outlet pada halaman ini belum membayar perpanjangan QRIS dan sudah melewati tanggal jatuh tempo.</span>
                <span>Total tunggakan: <strong>Rp {{ number_format($summary['unpaid_amount'] ?? 0, 0, ',', '.') }}</strong></span>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>Outlet</th>
                            <th>Owner</th>
                            <th class="text-center">Device</th>
                            <th class="text-center">Jatuh Tempo</th>
                            <th class="text-center">Terhitung</th>
                            <th class="text-end">Tagihan</th>
                            <th class="text-center">Status</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($outlets as $outlet)
                            @php
                                $dueDate = $outlet->billing_due_date;
                                $overdueDays = $dueDate ? (int) $dueDate->copy()->startOfDay()->diffInDays(now()->copy()->startOfDay(), false) : null;
                                $overdueLabel = $dueDate === null
                                    ? 'Belum pernah diperpanjang'
                                    : ($overdueDays > 0 ? 'Overdue ' . $overdueDays . ' hari' : 'Jatuh tempo hari ini');
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $outlet->outlet_name }}</div>
                                    <div class="text-muted small">{{ $outlet->code }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $outlet->owner->brand_name ?? $outlet->owner->name ?? '-' }}</div>
                                    <div class="text-muted small">{{ $outlet->owner->user->email ?? '-' }}</div>
                                </td>
                                <td class="text-center">{{ $outlet->devices_count ?? 0 }} device</td>
                                <td class="text-center">
                                    @if ($dueDate)
                                        <div class="fw-bold text-dark">{{ $dueDate->format('d/m/Y') }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger">{{ $overdueLabel }}</span>
                                </td>
                                <td class="text-end fw-bold">Rp {{ number_format($outlet->billing_amount, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="badge bg-danger">Diblokir</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.qris-billing.show', ['outlet' => $outlet, 'period' => $outlet->billing_action_period ?? now()->format('Y-m')]) }}" class="btn btn-info btn-xs">
                                        <i class="fa fa-search me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fa fa-check-circle text-success fa-2x mb-2"></i><br>
                                    Tidak ada outlet yang diblokir saat ini. Semua perpanjangan QRIS sudah aktif.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .qris-billing-summary {
            min-height: 96px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
        }
        .summary-icon {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: #fff;
            flex: 0 0 42px;
        }
        .summary-label {
            color: #6b7280;
            font-size: .78rem;
            font-weight: 600;
        }
        .summary-value {
            color: #111827;
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1.1;
        }
    </style>
@endpush
