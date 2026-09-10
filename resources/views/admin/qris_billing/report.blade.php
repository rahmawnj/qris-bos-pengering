@props([
    'items' => ['Admin', 'Laporan', 'Laporan Perpanjangan QRIS'],
    'title' => 'Laporan Perpanjangan QRIS',
    'subtitle' => 'Pantau riwayat perpanjangan QRIS per periode, owner, outlet, dan status pembayaran.',
])

@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    @include('qris_billing._summary', ['summary' => $summary, 'showDue' => false, 'totalLabel' => 'Total Data'])

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">Filter Laporan</h4>
            <div class="panel-heading-btn">
                <a href="{{ route('admin.qris-billing.index', request()->query()) }}" class="btn btn-xs btn-primary">
                    <i class="fa fa-file-invoice-dollar me-1"></i> Perpanjangan
                </a>
            </div>
        </div>
        <div class="panel-body">
            <form method="GET" action="{{ route('admin.qris-billing.report') }}" class="mb-3">
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
                    <a href="{{ route('admin.qris-billing.report') }}" class="btn btn-default btn-sm">Reset</a>
                </div>
            </form>

            <div class="alert alert-light border d-flex justify-content-between flex-wrap gap-2 mb-3">
                <span>Total perpanjangan: <strong>Rp {{ number_format($summary['amount'], 0, ',', '.') }}</strong></span>
                <span>Sudah lunas: <strong>Rp {{ number_format($summary['paid_amount'], 0, ',', '.') }}</strong></span>
            </div>

            @include('qris_billing._report_table', [
                'payments' => $payments,
            ])
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
