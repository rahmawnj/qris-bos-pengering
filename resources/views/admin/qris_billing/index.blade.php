@props([
    'items' => ['Admin', 'QRIS', 'Perpanjangan QRIS'],
    'title' => 'Perpanjangan QRIS',
    'subtitle' => 'Konfirmasi pembayaran perpanjangan QRIS outlet yang expired atau menunggu verifikasi.',
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
            <h4 class="panel-title">Menunggu Konfirmasi</h4>
        </div>
        <div class="panel-body">
            <form method="GET" action="{{ route('admin.qris-billing.index') }}" class="mb-3">
                <div class="d-flex flex-wrap align-items-end gap-2">
                    <div>
                        <label class="form-label mb-1 fw-bold">Periode</label>
                        <input type="month" name="period" class="form-control form-control-sm" value="{{ $period }}">
                    </div>
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
                    <a href="{{ route('admin.qris-billing.index') }}" class="btn btn-default btn-sm">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>Outlet</th>
                            <th>Owner</th>
                            <th class="text-center">Periode</th>
                            <th class="text-center">Tgl. Upload</th>
                            <th class="text-end">Perpanjangan</th>
                            <th class="text-center">Aktif Sampai</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Bukti</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            @php
                                $outlet = $payment->outlet;
                                $owner = $outlet?->owner;
                                $hasPendingWithdrawal = $owner && $owner->withdrawals()->where('status', 'pending')->exists();
                                $periodStart = $payment->period_start;
                                $periodEnd = $payment->period_end;
                                $periodText = $periodStart && $periodEnd
                                    ? ($periodStart->format('d/m/Y') === $periodEnd->format('d/m/Y')
                                        ? $periodEnd->format('d/m/Y')
                                        : $periodStart->format('d/m/Y') . ' - ' . $periodEnd->format('d/m/Y'))
                                    : '-';
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $outlet->outlet_name ?? '-' }}</div>
                                    <div class="text-muted small">{{ $outlet->code ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $owner->brand_name ?? $owner->name ?? '-' }}</div>
                                    <div class="text-muted small">{{ $owner?->user?->email ?? '-' }}</div>
                                </td>
                                <td class="text-center">{{ $periodText }}</td>
                                <td class="text-center">
                                    <div class="fw-bold text-dark">{{ $payment->created_at?->format('d/m/Y') ?? '-' }}</div>
                                    @if ($payment->created_at)
                                        <div class="text-muted small">{{ $payment->created_at->format('H:i') }}</div>
                                    @endif
                                </td>
                                <td class="text-end fw-bold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $periodEnd ? $periodEnd->format('d/m/Y') : '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">Menunggu Konfirmasi</span>
                                </td>
                                <td class="text-center">
                                    @if ($payment->proof_of_payment)
                                        <a href="{{ asset('storage/' . $payment->proof_of_payment) }}" target="_blank" class="btn btn-outline-primary btn-xs">
                                            <i class="fa fa-image me-1"></i> Lihat
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($hasPendingWithdrawal)
                                        <button type="button" class="btn btn-secondary btn-xs" disabled title="Owner masih memiliki penarikan yang sedang menunggu diproses.">
                                            <i class="fa fa-lock me-1"></i> Tertunda
                                        </button>
                                    @else
                                        <a href="{{ route('admin.qris-billing.payment.show', $payment) }}" class="btn btn-info btn-xs">
                                            <i class="fa fa-check-circle me-1"></i> Konfirmasi
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Tidak ada pembayaran yang menunggu konfirmasi.</td>
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
