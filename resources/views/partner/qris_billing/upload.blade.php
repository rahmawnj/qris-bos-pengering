@props([
    'items' => ['Partner', 'QRIS', 'Upload Bukti'],
    'title' => 'Upload Bukti Perpanjangan QRIS',
    'subtitle' => 'Unggah bukti pembayaran perpanjangan QRIS outlet Anda.',
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
        <div class="col-md-5">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Informasi Perpanjangan</h4>
                </div>
                <div class="panel-body">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="bg-primary text-white d-inline-flex align-items-center justify-content-center rounded" style="width: 48px; height: 48px;">
                            <i class="fa fa-store"></i>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ $outlet->outlet_name }}</h4>
                            <div class="text-muted small">{{ $outlet->code }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase">Periode</label>
                        <div class="fw-bold text-dark">{{ $outlet->billing_period }}</div>
                        @if (($outlet->billing_month_count ?? 0) > 1)
                            <div class="text-muted small">{{ $outlet->billing_month_count }} bulan digabung dalam sekali bayar</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase">Nominal Perpanjangan</label>
                        <div class="fw-bold text-primary fs-18px">Rp {{ number_format($outlet->billing_amount, 0, ',', '.') }}</div>
                    </div>
                    @if ($outlet->billing_status === 'paid' || $outlet->billing_status === 'not_due')
                        <div class="mb-3">
                            <label class="text-muted small fw-bold text-uppercase">Aktif Sampai</label>
                            <div class="fw-bold">{{ $outlet->billing_due_date ? $outlet->billing_due_date->format('d/m/Y') : '-' }}</div>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase">Jumlah Device</label>
                        <div>{{ $outlet->devices->count() }} Device</div>
                    </div>
                    @if (!empty($outlet->billing_unpaid_periods))
                        <div class="mb-3">
                            <label class="text-muted small fw-bold text-uppercase">Rincian Bulan</label>
                            <div class="border rounded overflow-hidden">
                                @foreach ($outlet->billing_unpaid_periods as $item)
                                    <div class="d-flex justify-content-between px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <span>{{ \Carbon\Carbon::createFromFormat('Y-m', $item['period'])->translatedFormat('F Y') }}</span>
                                        <strong>Rp {{ number_format($item['amount'], 0, ',', '.') }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <div class="mb-0">
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
                                    'pending' => 'Menunggu Verifikasi',
                                    'due' => 'Perlu Perpanjangan',
                                    'not_due' => 'Aktif',
                                ][$outlet->billing_status] ?? ucfirst($outlet->billing_status);
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                    </div>

                    <hr />

                    <div class="alert alert-info mb-0">
                        <h6 class="alert-heading fw-bold mb-2"><i class="fa fa-info-circle me-1"></i> Instruksi Pembayaran</h6>
                        <p class="small mb-2">Silakan transfer sesuai nominal perpanjangan ke rekening berikut:</p>
                        <div class="bg-white p-3 rounded border">
                            <div class="text-muted small">{{ $paymentInstruction['bank_name'] }}</div>
                            <div class="fw-bold fs-16px">{{ $paymentInstruction['account_number'] }}</div>
                            <div class="text-muted small">a/n {{ $paymentInstruction['account_holder'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Upload Bukti Pembayaran</h4>
                </div>
                <div class="panel-body p-4">
                    @if ($outlet->billing_status === 'paid')
                        <div class="text-center py-5">
                            <div class="text-success mb-3">
                                <i class="fa fa-check-circle fa-4x"></i>
                            </div>
                            <h4>QRIS Aktif</h4>
                            <p class="text-muted mb-3">Perpanjangan QRIS sudah dikonfirmasi oleh admin.</p>
                            @if ($outlet->billing_payment?->paid_at)
                                <span class="badge bg-light text-dark border p-2">
                                    Diaktifkan pada {{ $outlet->billing_payment->paid_at->format('d/m/Y H:i') }}
                                </span>
                            @endif
                        </div>
                    @else
                        @if ($outlet->billing_status === 'pending')
                            <div class="alert alert-warning">
                                Bukti pembayaran sudah dikirim dan sedang menunggu verifikasi admin. Anda masih dapat mengganti bukti jika diperlukan.
                            </div>
                        @endif

                        @if ($outlet->billing_payment?->proof_of_payment)
                            <div class="mb-4">
                                <label class="fw-bold mb-2">Bukti yang telah diunggah</label>
                                <a href="{{ asset('storage/' . $outlet->billing_payment->proof_of_payment) }}" target="_blank" class="d-block">
                                    <img src="{{ asset('storage/' . $outlet->billing_payment->proof_of_payment) }}"
                                        class="img-fluid rounded border" style="max-height: 260px;" alt="Bukti Pembayaran">
                                </a>
                            </div>
                        @endif

                        <form action="{{ route('partner.outlets.billing.upload', $outlet) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="upload-zone p-4 text-center border rounded mb-3 bg-light">
                                <i class="fa fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <h6 class="mb-2">Pilih gambar bukti transfer</h6>
                                <p class="text-muted small mb-3">Format gambar JPG atau PNG, maksimal 5MB.</p>
                                <input type="file" name="proof_of_payment" class="form-control @error('proof_of_payment') is-invalid @enderror" accept="image/*" required>
                                @error('proof_of_payment')
                                    <div class="invalid-feedback text-start">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fa fa-upload me-1"></i> Upload Bukti Pembayaran
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('partner.qris-billing.report') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left me-1"></i> Kembali ke Laporan
                </a>
                <a href="{{ route('partner.qris-billing.show', $outlet) }}" class="btn btn-outline-secondary">
                    <i class="fa fa-search me-1"></i> Lihat Detail
                </a>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .upload-zone {
            border-style: dashed !important;
            border-color: #d5dce8 !important;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }

        .upload-zone:hover {
            border-color: #00acac !important;
            background-color: rgba(0, 172, 172, 0.03) !important;
        }
    </style>
@endpush
