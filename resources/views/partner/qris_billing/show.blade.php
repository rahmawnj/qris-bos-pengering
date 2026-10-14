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
<div class="alert alert-info border-0 shadow-none mb-0"><h6 class="alert-heading fw-bold mb-2"><i class="fa fa-info-circle me-1"></i> Instruksi Pembayaran</h6><p class="small mb-2">Silakan transfer sesuai nominal di atas ke rekening berikut:</p><div class="bg-white p-3 rounded border mb-2"><div class="text-muted small">{{ $paymentInstruction['bank_name'] }}</div><div class="fw-bold fs-16px mb-1">{{ $paymentInstruction['account_number'] }}</div><div class="text-muted small">a/n {{ $paymentInstruction['account_holder'] }}</div></div><p class="small mb-0 text-muted">Setelah transfer, mohon unggah bukti pembayaran di form sebelah kanan.</p></div>
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
    <h5 class="fw-bold mb-3">Pilih Metode Pembayaran</h5>
    <div class="border rounded-3 p-3 mb-4">
        <h6 class="fw-bold"><i class="fa fa-university me-1 text-primary"></i> Bayar dengan Virtual Account</h6>
        <p class="text-muted small">Nomor VA dibuat otomatis dan status diperbarui setelah pembayaran terkonfirmasi.</p>
        <form action="{{ route('partner.qris-billing.virtual-account', $outlet) }}" method="POST" class="mb-3">
            @csrf
            <label for="va_bank" class="form-label">Pilih Bank</label>
            <select name="va_bank" id="va_bank" class="form-select mb-3" required>
                <option value="bca">BCA</option><option value="bni">BNI</option><option value="bri">BRI</option>
                <option value="permata">Permata</option><option value="cimb">CIMB Niaga</option><option value="danamon">Danamon</option>
            </select>
            <button type="submit" class="btn btn-success w-100"><i class="fa fa-credit-card me-1"></i> Buat Virtual Account</button>
        </form>
    </div>
    <h6 class="fw-bold mb-2">Atau upload bukti transfer manual</h6>
    <p class="text-muted mb-4">Pilih cara manual jika sudah transfer ke rekening yang tertera.</p>
    <form action="{{ route('partner.outlets.billing.upload',$outlet) }}" method="POST" enctype="multipart/form-data">@csrf<div class="upload-zone p-5 text-center border-2 border-dashed rounded-4 mb-4 bg-light"><i class="fa fa-cloud-upload-alt fa-4x text-muted mb-3"></i><h6 class="mb-2">Klik untuk memilih gambar bukti transfer</h6><p class="text-muted small mb-3">Format: JPG, PNG (Maks 5MB)</p><input type="file" name="proof_of_payment" class="form-control" accept="image/*" required></div><button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm"><i class="fa fa-paper-plane me-1"></i> Kirim Konfirmasi Pembayaran</button></form>@endif
</div></div></div></div>
<div class="mt-4"><a href="{{ route('partner.qris-billing.report') }}" class="btn btn-default"><i class="fa fa-arrow-left me-1"></i> Kembali ke Laporan</a></div>
@endsection
@push('styles')<style>.bg-primary-transparent-1{background-color:rgba(0,172,172,.1)}.upload-zone{transition:all .3s ease;border:2px dashed #ddd}.upload-zone:hover{border-color:#00acac;background-color:rgba(0,172,172,.02)}</style>@endpush
