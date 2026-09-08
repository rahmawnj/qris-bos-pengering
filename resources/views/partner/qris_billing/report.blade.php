@props([
    'items' => ['Partner', 'Laporan', 'Laporan Perpanjangan QRIS'],
    'title' => 'Laporan Perpanjangan QRIS',
    'subtitle' => 'Ringkasan perpanjangan QRIS outlet berdasarkan periode dan status.',
])

@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    @include('qris_billing._summary', ['summary' => $summary, 'showDue' => false, 'totalLabel' => 'Total Data'])

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">Laporan Perpanjangan</h4>
        </div>
        <div class="panel-body">
            <form method="GET" action="{{ route('partner.qris-billing.report') }}" class="mb-3">
                <div class="d-flex flex-wrap align-items-end gap-2">
                    <div>
                        <label class="form-label mb-1 fw-bold">Periode</label>
                        <input type="month" name="period" class="form-control form-control-sm" value="{{ $period ?? '' }}">
                    </div>
                    <div>
                        <label class="form-label mb-1 fw-bold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Aktif</option>
                        </select>
                    </div>
                    <div style="min-width: 240px; flex: 1;">
                        <label class="form-label mb-1 fw-bold">Pencarian</label>
                        <input type="text" name="search" class="form-control form-control-sm" value="{{ $search }}" placeholder="Outlet atau kode">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter me-1"></i> Filter</button>
                    <a href="{{ route('partner.qris-billing.report') }}" class="btn btn-default btn-sm">Reset</a>
                </div>
            </form>

            <div class="alert alert-light border d-flex justify-content-between flex-wrap gap-2 mb-3">
                <span>Total perpanjangan: <strong>Rp {{ number_format($summary['amount'], 0, ',', '.') }}</strong></span>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>Outlet</th>
                            <th class="text-center">Periode</th>
                            <th class="text-center">Tgl. Bayar</th>
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
                                $periodStart = $payment->period_start;
                                $periodEnd = $payment->period_end;
                                $periodText = $periodStart && $periodEnd
                                    ? ($periodStart->format('d/m/Y') === $periodEnd->format('d/m/Y')
                                        ? $periodEnd->format('d/m/Y')
                                        : $periodStart->format('d/m/Y') . ' - ' . $periodEnd->format('d/m/Y'))
                                    : '-';
                                $statusClass = [
                                    'paid' => 'success',
                                    'pending' => 'warning text-dark',
                                    'due' => 'danger',
                                ][$payment->status] ?? 'secondary';
                                $statusLabel = [
                                    'paid' => 'Aktif',
                                    'pending' => 'Menunggu Konfirmasi',
                                    'due' => 'Perlu Perpanjangan',
                                ][$payment->status] ?? ucfirst($payment->status);
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $outlet->outlet_name ?? '-' }}</div>
                                    <div class="text-muted small">{{ $outlet->code ?? '-' }}</div>
                                </td>
                                <td class="text-center">{{ $periodText }}</td>
                                <td class="text-center">
                                    @if ($payment->paid_at)
                                        <div class="fw-bold text-dark">{{ $payment->paid_at->format('d/m/Y') }}</div>
                                        <div class="text-muted small">{{ $payment->paid_at->format('H:i') }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $periodEnd ? $periodEnd->format('d/m/Y') : '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span>
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
                                    <a href="{{ route('partner.qris-billing.payment.show', $payment) }}" class="btn btn-info btn-xs">
                                        <i class="fa fa-search me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Tidak ada data perpanjangan.</td>
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
