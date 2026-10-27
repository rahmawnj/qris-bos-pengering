@props(['items'=>['Partner','QRIS','Detail Perpanjangan'],'title'=>'Perpanjangan QRIS','subtitle'=>'Upload bukti pembayaran untuk memperpanjang masa aktif QRIS outlet Anda.'])
@extends('layouts.dashboard.app')
@section('content')
<x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="row"><div class="col-md-5"><div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4">
<div class="d-flex align-items-center gap-3 mb-4"><div class="bg-primary-transparent-1 text-primary p-3 rounded-circle"><i class="fa fa-store fa-2x"></i></div><div><h4 class="mb-0">{{ $outlet->outlet_name }}</h4><span class="text-muted small">ID: {{ $outlet->code }}</span></div></div>
<div class="p-3 bg-light rounded-3 mb-4"><div class="row text-center"><div class="col-6 border-end"><div class="text-muted small mb-1">Periode</div><div class="fw-bold text-dark">{{ $outlet->billing_period }}</div></div><div class="col-6"><div class="text-muted small mb-1">Status</div>@php $statusClass=['paid'=>'success','pending'=>'warning text-dark','due'=>'danger','prepay'=>'warning text-dark','not_due'=>'info'][$outlet->billing_status]??'secondary';$statusLabel=['paid'=>'Aktif','pending'=>'Menunggu Verifikasi','due'=>'Perlu Perpanjangan','prepay'=>'Bisa Bayar Lebih Awal','not_due'=>'Aktif'][$outlet->billing_status]??ucfirst($outlet->billing_status); @endphp<span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span></div></div></div>
<div class="mb-4"><div class="text-muted small mb-1">Total yang harus dibayar</div><div class="display-6 fw-bold text-primary">Rp {{ number_format($outlet->billing_amount,0,',','.') }}</div>@if($outlet->billing_due_date)<div class="text-muted small mt-1">Aktif sampai: <span class="fw-bold">{{ $outlet->billing_due_date->format('d/m/Y') }}</span></div>@endif</div>
@if($outlet->billing_status==='prepay')<div class="alert alert-warning"><i class="fa fa-clock me-1"></i> Pembayaran sudah dibuka lebih awal. Jika dibayar sekarang, masa aktif tetap dihitung dari tanggal aktif sampai saat ini, bukan dari tanggal pembayaran.</div>@endif
@if(!empty($outlet->billing_unpaid_periods))<div class="mb-4"><div class="text-muted small fw-bold text-uppercase mb-2">Rincian Bulan</div><div class="border rounded overflow-hidden">@foreach($outlet->billing_unpaid_periods as $item)<div class="d-flex justify-content-between px-3 py-2 {{ !$loop->last?'border-bottom':'' }}"><div class="fw-bold">{{ \Carbon\Carbon::createFromFormat('Y-m',$item['period'])->translatedFormat('F Y') }}</div><div class="fw-bold">Rp {{ number_format($item['amount'],0,',','.') }}</div></div>@endforeach</div></div>@endif
<div class="alert alert-info border-0 shadow-none mb-0"><h6 class="alert-heading fw-bold mb-2"><i class="fa fa-info-circle me-1"></i> Pembayaran Virtual Account</h6><p class="small mb-0">Pilih bank pada panel pembayaran. Nomor Virtual Account akan dibuat otomatis sesuai tagihan. Bayar menggunakan nomor VA tersebut sebelum batas waktu berakhir; status akan diperbarui setelah Midtrans mengonfirmasi pembayaran.</p></div>
</div></div></div>
<div class="col-md-7"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4">
@if($outlet->billing_status==='paid')<div class="text-center py-5"><div class="text-success mb-3"><i class="fa fa-check-circle fa-5x"></i></div><h3>QRIS Aktif</h3><p class="text-muted">Terima kasih. Perpanjangan QRIS telah dikonfirmasi oleh administrator.</p>@if($outlet->billing_payment?->paid_at)<div class="badge bg-light text-dark p-2 border">Diaktifkan pada: {{ $outlet->billing_payment->paid_at->format('d/m/Y H:i') }}</div>@endif</div>
@elseif($outlet->billing_status==='pending')
    @if($outlet->billing_payment?->payment_method === 'virtual_account')
        <div class="text-center py-4">
            <div class="text-primary mb-3"><i class="fa fa-university fa-4x"></i></div>
            <h3>Virtual Account Menunggu Pembayaran</h3>
            <p class="text-muted">Selesaikan pembayaran melalui bank sebelum batas waktu berakhir.</p>
            <div class="border rounded-3 p-4 text-start mt-3">
                <div class="text-muted small">Bank</div><div class="fw-bold mb-3">{{ strtoupper($outlet->billing_payment->va_bank ?? '-') }}</div>
                <div class="text-muted small">Nomor Virtual Account</div>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="fw-bold fs-4 text-primary" id="qris-billing-va">{{ $outlet->billing_payment->va_number ?? '-' }}</div>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('qris-billing-va').innerText)">Salin</button>
                </div>
                <div class="text-muted small">Total pembayaran</div>
                <div class="fw-bold fs-4 mb-2">Rp {{ number_format($outlet->billing_payment->amount,0,',','.') }}</div>
                @if($outlet->billing_payment->expires_at)<div class="text-danger small">Batas pembayaran: {{ $outlet->billing_payment->expires_at->format('d/m/Y H:i') }}</div>@endif
                <div class="small text-muted mt-3">Status akan diperbarui otomatis setelah gateway mengonfirmasi pembayaran.</div>
            </div>
        </div>
    @else
        <div class="text-center py-5"><div class="text-warning mb-3"><i class="fa fa-clock fa-5x"></i></div><h3>Menunggu Verifikasi</h3><p class="text-muted">Bukti pembayaran Anda sedang diproses oleh tim kami.</p>@if($outlet->billing_payment?->proof_of_payment)<div class="mt-4 text-start"><label class="fw-bold mb-2">Bukti yang telah diunggah:</label><a href="{{ asset('storage/'.$outlet->billing_payment->proof_of_payment) }}" target="_blank" class="d-block"><img src="{{ asset('storage/'.$outlet->billing_payment->proof_of_payment) }}" class="img-fluid rounded border" style="max-height:200px;"></a></div>@endif</div>
    @endif
@else
    <h5 class="fw-bold mb-2">Pilih Bank Pembayaran</h5>
    <p class="text-muted small mb-4">Pilih bank untuk membuat Virtual Account. Kamu tidak perlu mengunggah bukti transfer.</p>
    <form action="{{ route('partner.qris-billing.virtual-account', $outlet) }}" method="POST" id="qris-va-form">
        @csrf
        <div class="bank-grid mb-4">
            <label class="bank-option">
                <input type="radio" name="va_bank" value="bca" required>
                <span class="bank-card"><img src="https://dicoding-web-img.sgp1.digitaloceanspaces.com/original/jobs/dos%3Acompany_logo_pt_bank_central_asia_tbk_200723102046.jpg" alt="Logo BCA"><span>BCA</span></span>
            </label>
            <label class="bank-option">
                <input type="radio" name="va_bank" value="bni" required>
                <span class="bank-card"><img src="https://i.pinimg.com/736x/ef/33/52/ef3352ee1f22a05508f2aa618bac5642.jpg" alt="Logo BNI"><span>BNI</span></span>
            </label>
            <label class="bank-option">
                <input type="radio" name="va_bank" value="bri" required>
                <span class="bank-card"><img src="https://zonalogo.com/assets/logo-bank-bri.webp" alt="Logo BRI"><span>BRI</span></span>
            </label>
            <label class="bank-option">
                <input type="radio" name="va_bank" value="permata" required>
                <span class="bank-card"><img src="https://zonalogo.com/assets/logo-bank-permata.webp" alt="Logo Permata Bank"><span>Permata Bank</span></span>
            </label>
            <label class="bank-option">
                <input type="radio" name="va_bank" value="cimb" required>
                <span class="bank-card"><img src="https://www.itworks.id/wp-content/uploads/2019/06/Logo-Bank-CIMB-Niaga.jpg" alt="Logo CIMB Niaga"><span>CIMB Niaga</span></span>
            </label>
        </div>
        <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill shadow-sm"><i class="fa fa-university me-1"></i> Buat Virtual Account</button>
    </form>
@endif
</div></div></div></div>
<div class="mt-4"><a href="{{ route('partner.qris-billing.report') }}" class="btn btn-default"><i class="fa fa-arrow-left me-1"></i> Kembali ke Laporan</a></div>
@endsection
@push('styles')
<style>
.bg-primary-transparent-1{background-color:rgba(0,172,172,.1)}
.bank-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.bank-option{position:relative;display:block;cursor:pointer;margin:0}
.bank-option input{position:absolute;opacity:0;pointer-events:none}
.bank-card{height:108px;border:1.5px solid #e5e7eb;border-radius:14px;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;padding:12px;transition:border-color .18s ease,box-shadow .18s ease,background .18s ease}
.bank-card img{display:block;width:100%;max-width:132px;height:42px;object-fit:contain}
.bank-card span{font-size:13px;font-weight:700;color:#344054}
.bank-option input:checked+.bank-card{border-color:#00acac;background:rgba(0,172,172,.06);box-shadow:0 0 0 3px rgba(0,172,172,.12)}
.bank-option input:focus-visible+.bank-card{outline:3px solid rgba(0,172,172,.3);outline-offset:2px}
@media(min-width:768px){.bank-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
</style>
@endpush
