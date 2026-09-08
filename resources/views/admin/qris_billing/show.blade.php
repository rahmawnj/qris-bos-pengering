@props([
    'items' => ['Admin', 'QRIS', 'Detail Perpanjangan'],
    'title' => 'Detail Perpanjangan QRIS',
    'subtitle' => 'Verifikasi bukti pembayaran dan konfirmasi perpanjangan.',
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

    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Informasi Outlet</h4>
                </div>
                <div class="panel-body">
                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase">Nama Outlet</label>
                        <div class="fw-bold fs-16px text-dark">{{ $outlet->outlet_name }}</div>
                        <div class="text-muted">{{ $outlet->code }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase">Owner / Brand</label>
                        <div class="fw-bold">{{ $outlet->owner->brand_name ?? $outlet->owner->name ?? '-' }}</div>
                        <div class="text-muted">{{ $outlet->owner->user->email ?? '-' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase">Jumlah Device</label>
                        <div>{{ $outlet->devices->count() }} Device</div>
                    </div>
                    <hr />
                    <a href="{{ route('admin.outlets.edit', $outlet) }}" class="btn btn-default btn-sm w-100">
                        <i class="fa fa-edit me-1"></i> Edit Outlet
                    </a>
                </div>
            </div>

            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Detail Perpanjangan</h4>
                </div>
                <div class="panel-body">
                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase">Periode</label>
                        <div class="fw-bold text-dark">{{ $outlet->billing_period }}</div>
                        @if (($billingBreakdown['month_count'] ?? 0) > 0)
                            <div class="text-muted small">{{ $billingBreakdown['month_count'] }} bulan belum lunas</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase">Nominal Perpanjangan</label>
                        <div class="fw-bold text-primary fs-18px">Rp {{ number_format($outlet->billing_amount, 0, ',', '.') }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase">Rincian Perhitungan</label>
                        <div class="border rounded p-3 bg-light">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Jumlah device</span>
                                <strong>{{ $billingBreakdown['device_count'] }} device</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Harga per device</span>
                                <strong>Rp {{ number_format($billingBreakdown['price_per_device'], 0, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <strong>Rp {{ number_format($billingBreakdown['subtotal'], 0, ',', '.') }}</strong>
                            </div>
                            @if (($billingBreakdown['max_fee_mode'] ?? 'capped') === 'unlimited')
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Maksimal biaya</span>
                                    <strong>Unlimited</strong>
                                </div>
                            @else
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Maksimal biaya</span>
                                    <strong>Rp {{ number_format($billingBreakdown['max_fee'], 0, ',', '.') }}</strong>
                                </div>
                            @endif
                            @if ($billingBreakdown['discount_by_cap'] > 0)
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Potongan batas maksimal</span>
                                    <strong>- Rp {{ number_format($billingBreakdown['discount_by_cap'], 0, ',', '.') }}</strong>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between border-top pt-2 mt-2 fs-16px">
                                <span class="fw-bold">Total per bulan</span>
                                <strong class="text-primary">Rp {{ number_format($billingBreakdown['total'], 0, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between border-top pt-2 mt-2 fs-16px">
                                <span class="fw-bold">Total {{ $billingBreakdown['month_count'] }} bulan</span>
                                <strong class="text-danger">Rp {{ number_format($billingBreakdown['grand_total'], 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                    @if (!empty($billingBreakdown['periods']))
                        <div class="mb-3">
                            <label class="text-muted small fw-bold text-uppercase">Rincian Perpanjangan</label>
                            <div class="border rounded overflow-hidden">
                                @foreach ($billingBreakdown['periods'] as $item)
                                    <div class="d-flex justify-content-between align-items-center px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <div>
                                            <div class="fw-bold">{{ \Carbon\Carbon::createFromFormat('Y-m', $item['period'])->translatedFormat('F Y') }}</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold">Rp {{ number_format($item['amount'], 0, ',', '.') }}</div>
                                            <span class="badge bg-{{ $item['status'] === 'pending' ? 'warning text-dark' : 'danger' }}">
                                                {{ $item['status'] === 'pending' ? 'Pending' : 'Perlu Perpanjangan' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if (in_array($outlet->billing_status, ['paid', 'not_due'], true))
                        <div class="mb-3">
                            <label class="text-muted small fw-bold text-uppercase">Aktif Sampai</label>
                            <div class="fw-bold">{{ $outlet->billing_due_date ? $outlet->billing_due_date->format('d/m/Y') : '-' }}</div>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase">Status</label>
                        <div>
                            @php
                                $statusClass = [
                                    'paid' => 'success',
                                    'pending' => 'warning text-dark',
                                    'due' => 'danger',
                                    'not_due' => 'info',
                                ][$outlet->billing_status] ?? 'secondary';
                                $statusLabel = [
                                    'paid' => 'Aktif',
                                    'pending' => 'Menunggu Konfirmasi',
                                    'due' => 'Perlu Perpanjangan',
                                    'not_due' => 'Aktif',
                                ][$outlet->billing_status] ?? ucfirst($outlet->billing_status);
                            @endphp
                            <span class="badge bg-{{ $statusClass }} fs-12px">{{ $statusLabel }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Bukti Pembayaran & Konfirmasi</h4>
                </div>
                <div class="panel-body text-center p-4">
                    @if ($outlet->billing_payment && $outlet->billing_payment->proof_of_payment)
                        <div class="mb-4">
                            <h5 class="text-start mb-3">Bukti Transfer</h5>
                            <a href="{{ asset('storage/' . $outlet->billing_payment->proof_of_payment) }}" target="_blank">
                                <img src="{{ asset('storage/' . $outlet->billing_payment->proof_of_payment) }}" 
                                    class="img-fluid rounded border shadow-sm" style="max-height: 500px;" alt="Bukti Pembayaran">
                            </a>
                        </div>

                        @php
                            $hasPendingWithdrawal = $outlet->owner && $outlet->owner->withdrawals()->where('status', 'pending')->exists();
                        @endphp

                        @if ($outlet->billing_status !== 'paid')
                            <div class="alert alert-warning mb-4 text-start">
                                <i class="fa fa-info-circle me-1"></i> Harap pastikan bukti transfer di atas valid dan nominal sudah masuk ke rekening sebelum melakukan konfirmasi.
                            </div>

                            @if ($hasPendingWithdrawal)
                                <div class="alert alert-secondary text-start mb-4">
                                    <i class="fa fa-lock me-1"></i> Owner ini masih memiliki penarikan yang sedang menunggu diproses, jadi tombol konfirmasi di nonaktifkan sementara.
                                </div>
                            @endif

                            <form action="{{ route('admin.qris-billing.mark-paid', $outlet) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="billing_period" value="{{ $period }}">
                                <input type="hidden" name="payment_id" value="{{ $outlet->billing_payment->id }}">
                                
                                <button type="submit" class="btn btn-primary btn-lg px-5 w-100" 
                                    {{ $hasPendingWithdrawal ? 'disabled' : '' }}
                                    onclick="return {{ $hasPendingWithdrawal ? 'false' : "confirm('Konfirmasi perpanjangan QRIS ini?')" }};">
                                    <i class="fa fa-check-circle me-1"></i> ACC Perpanjangan
                                </button>
                            </form>
                        @else
                            <div class="alert alert-success">
                                <h5 class="alert-heading"><i class="fa fa-check-circle me-1"></i> Pembayaran Terkonfirmasi</h5>
                                <p class="mb-0">Perpanjangan ini sudah dikonfirmasi pada <strong>{{ $outlet->billing_payment->paid_at->format('d/m/Y H:i') }}</strong>.</p>
                            </div>
                        @endif
                    @else
                        <div class="py-5">
                            <i class="fa fa-image fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">Belum Ada Bukti Pembayaran</h4>
                            <p class="text-muted">Outlet ini belum mengunggah bukti pembayaran untuk periode ini.</p>
                            
                            @if ($outlet->billing_status === 'due' || $outlet->billing_status === 'not_due')
                                <form action="{{ route('admin.qris-billing.mark-paid', $outlet) }}" method="POST" class="mt-4">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="billing_period" value="{{ $period }}">
                                    @if ($outlet->billing_payment)
                                        <input type="hidden" name="payment_id" value="{{ $outlet->billing_payment->id }}">
                                    @endif
                                    
                                    <button type="submit" class="btn btn-outline-primary btn-sm px-4" 
                                        onclick="return confirm('Konfirmasi perpanjangan tanpa bukti pembayaran?')">
                                        ACC Manual
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.qris-billing.index', ['period' => $period]) }}" class="btn btn-default">
                    <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
@endsection
