@props(['items'=>['Partner','QRIS','Detail Perpanjangan'],'title'=>'Perpanjangan QRIS','subtitle'=>'Upload bukti pembayaran untuk memperpanjang masa aktif QRIS outlet Anda.'])
@extends('layouts.dashboard.app')
@section('content')
<x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="row"><div class="col-md-5"><div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4">
<div class="d-flex align-items-center gap-3 mb-4"><div class="bg-primary-transparent-1 text-primary p-3 rounded-circle"><i class="fa fa-store fa-2x"></i></div><div><h4 class="mb-0">{{ $outlet->outlet_name }}</h4><span class="text-muted small">ID: {{ $outlet->code }}</span></div></div>
<div class="p-3 bg-light rounded-3 mb-4"><div class="row text-center"><div class="col-6 border-end"><div class="text-muted small mb-1">Periode</div><div class="fw-bold text-dark">{{ $outlet->billing_period }}</div></div><div class="col-6"><div class="text-muted small mb-1">Status</div>@php $statusClass=['paid'=>'success','pending'=>'warning text-dark','rejected'=>'danger','due'=>'danger','prepay'=>'warning text-dark','not_due'=>'info'][$outlet->billing_status]??'secondary';$statusLabel=['paid'=>'Aktif','pending'=>'Menunggu Verifikasi','rejected'=>'Pembayaran Ditolak','due'=>'Perlu Perpanjangan','prepay'=>'Bisa Bayar Lebih Awal','not_due'=>'Aktif'][$outlet->billing_status]??ucfirst($outlet->billing_status); @endphp<span id="qris-billing-status" class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span></div></div></div>
<div class="mb-4"><div class="text-muted small mb-1">Total yang harus dibayar</div><div class="display-6 fw-bold text-primary">Rp {{ number_format($outlet->billing_amount,0,',','.') }}</div>@if($outlet->billing_due_date)<div class="text-muted small mt-1">Aktif sampai: <span id="qris-billing-due-date" class="fw-bold">{{ $outlet->billing_due_date->format('d/m/Y') }}</span></div>@endif</div>
@if($outlet->billing_status==='prepay')<div class="alert alert-warning"><i class="fa fa-clock me-1"></i> Pembayaran sudah dibuka lebih awal. Jika dibayar sekarang, masa aktif tetap dihitung dari tanggal aktif sampai saat ini, bukan dari tanggal pembayaran.</div>@endif
@if(!empty($outlet->billing_unpaid_periods))<div class="mb-4"><div class="text-muted small fw-bold text-uppercase mb-2">Rincian Bulan</div><div class="border rounded overflow-hidden">@foreach($outlet->billing_unpaid_periods as $item)<div class="d-flex justify-content-between px-3 py-2 {{ !$loop->last?'border-bottom':'' }}"><div class="fw-bold">{{ \Carbon\Carbon::createFromFormat('Y-m',$item['period'])->translatedFormat('F Y') }}</div><div class="fw-bold">Rp {{ number_format($item['amount'],0,',','.') }}</div></div>@endforeach</div></div>@endif
<div id="qris-va-general-instructions" class="alert alert-info border-0 shadow-none mb-0"><h6 class="alert-heading fw-bold mb-2"><i class="fa fa-info-circle me-1"></i> Pembayaran Virtual Account</h6><p class="small mb-0">Pilih bank pada panel pembayaran. Nomor Virtual Account akan dibuat otomatis sesuai tagihan. Bayar menggunakan nomor VA tersebut sebelum batas waktu berakhir; status akan diperbarui setelah Midtrans mengonfirmasi pembayaran.</p></div>
</div></div></div>
<div class="col-md-7"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4">
@if($outlet->billing_status==='paid')<div class="text-center py-5"><div class="text-success mb-3"><i class="fa fa-check-circle fa-5x"></i></div><h3>QRIS Aktif</h3><p class="text-muted">Terima kasih. Perpanjangan QRIS telah dikonfirmasi oleh administrator.</p>@if($outlet->billing_payment?->paid_at)<div class="badge bg-light text-dark p-2 border">Diaktifkan pada: {{ $outlet->billing_payment->paid_at->format('d/m/Y H:i') }}</div>@endif</div>
@elseif($outlet->billing_status==='pending')
    @if($outlet->billing_payment?->payment_method === 'virtual_account')
        <div id="qris-va-pending-view" class="text-center py-4">
            <div class="text-primary mb-3"><i class="fa fa-university fa-4x"></i></div>
            <h3>Virtual Account Menunggu Pembayaran</h3>
            <p class="text-muted">Selesaikan pembayaran melalui bank sebelum batas waktu berakhir.</p>
            <div id="qris-va-payment-panel" class="border rounded-3 p-4 text-start mt-3">
                <div class="text-muted small">Bank</div><div class="fw-bold mb-3">{{ strtoupper($outlet->billing_payment->va_bank ?? '-') }}</div>
                <div class="text-muted small">Nomor Virtual Account</div>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="fw-bold fs-4 text-primary" id="qris-billing-va">{{ $outlet->billing_payment->va_number ?? '-' }}</div>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('qris-billing-va').innerText)">Salin</button>
                </div>
                <div class="text-muted small">Total pembayaran</div>
                <div class="fw-bold fs-4 mb-2">Rp {{ number_format($outlet->billing_payment->amount,0,',','.') }}</div>
                @if($outlet->billing_payment->expires_at)
                    <div class="text-danger small">Batas pembayaran: {{ $outlet->billing_payment->expires_at->format('d/m/Y H:i:s') }}</div>
                    <div class="small text-danger fw-bold mt-2" id="qris-va-countdown" aria-live="polite">Menghitung sisa waktu...</div>
                    <script>
                    (function () {
                        const expiryIso = @json($outlet->billing_payment->expires_at->toIso8601String());
                        const statusUrl = @json(route('partner.qris-billing.payment.status', $outlet->billing_payment));
                        const expiryTimestamp = Date.parse(expiryIso);
                        const countdown = document.getElementById('qris-va-countdown');
                        const pendingView = document.getElementById('qris-va-pending-view');
                        let reloadStarted = false;
                        let paymentResolved = false;

                        function showPaidState(data) {
                            paymentResolved = true;
                            if (countdown) countdown.classList.add('d-none');

                            const statusBadge = document.getElementById('qris-billing-status');
                            if (statusBadge) {
                                statusBadge.className = 'badge bg-success';
                                statusBadge.textContent = 'Aktif';
                            }

                            const dueDate = document.getElementById('qris-billing-due-date');
                            if (dueDate && data.period_end) {
                                dueDate.textContent = new Date(data.period_end + 'T12:00:00').toLocaleDateString('id-ID');
                            }

                            const instructions = document.getElementById('qris-va-general-instructions');
                            if (instructions) instructions.classList.add('d-none');

                            if (pendingView) {
                                pendingView.innerHTML = '<div class="text-success mb-3"><i class="fa fa-check-circle fa-5x"></i></div>'
                                    + '<h3>Pembayaran Berhasil!</h3>'
                                    + '<p class="text-muted">Pembayaran VA sudah dikonfirmasi. Masa aktif outlet berhasil diperbarui.</p>'
                                    + (data.paid_at
                                        ? '<div class="badge bg-light text-dark p-2 border">Dikonfirmasi pada: '
                                            + new Date(data.paid_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })
                                            + '</div>'
                                        : '');
                            }
                        }

                        async function checkPaymentStatus() {
                            if (paymentResolved) return;
                            try {
                                const response = await fetch(statusUrl, {
                                    method: 'GET',
                                    headers: { Accept: 'application/json' },
                                    cache: 'no-store',
                                });
                                if (response.ok) {
                                    const data = await response.json();
                                    if (data.status === 'paid') {
                                        showPaidState(data);
                                        return;
                                    }
                                    if (['expired', 'failed', 'cancelled', 'canceled'].includes(data.status)) {
                                        window.location.reload();
                                        return;
                                    }
                                }
                            } catch (error) {
                                // Kegagalan polling sementara tidak mengganggu countdown.
                            }
                            if (!paymentResolved) window.setTimeout(checkPaymentStatus, 2000);
                        }

                        function refreshVaExpiry() {
                            if (paymentResolved) return;
                            if (!Number.isFinite(expiryTimestamp) || !countdown) return;
                            const remaining = expiryTimestamp - Date.now();
                            if (remaining <= 0) {
                                countdown.classList.remove('text-danger');
                                countdown.classList.add('text-warning');
                                countdown.textContent = 'Masa berlaku VA habis. Sedang memverifikasi status pembayaran ke Midtrans...';
                                // Jangan tandai gagal atau reload berulang hanya karena countdown habis.
                                // checkPaymentStatus tetap polling; status berubah hanya setelah dikonfirmasi gateway.
                                return;
                            }

                            const totalSeconds = Math.floor(remaining / 1000);
                            const hours = Math.floor(totalSeconds / 3600);
                            const minutes = Math.floor((totalSeconds % 3600) / 60);
                            const seconds = totalSeconds % 60;
                            countdown.textContent = 'Sisa waktu pembayaran: '
                                + hours + ' jam ' + minutes + ' menit ' + seconds + ' detik';
                            window.setTimeout(refreshVaExpiry, 1000);
                        }

                        checkPaymentStatus();
                        refreshVaExpiry();
                    })();
                    </script>
                @endif
                <form action="{{ route('partner.qris-billing.payment.cancel', $outlet->billing_payment) }}" method="POST" class="mt-4 d-flex justify-content-end" onsubmit="return confirm('Batalkan Virtual Account ini? Setelah dibatalkan, Anda dapat memilih metode atau gateway pembayaran lain.');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm" style="border-radius:6px;"><i class="fa fa-times-circle me-1"></i> Batalkan Pembayaran</button>
                </form>
            </div>
        </div>
    @else
        <div class="text-center py-5"><div class="text-warning mb-3"><i class="fa fa-clock fa-5x"></i></div><h3>Menunggu Verifikasi</h3><p class="text-muted">Bukti pembayaran Anda sedang diproses oleh tim kami.</p>@if($outlet->billing_payment?->proof_of_payment)<div class="mt-4 text-start"><label class="fw-bold mb-2">Bukti yang telah diunggah:</label><a href="{{ asset('storage/'.$outlet->billing_payment->proof_of_payment) }}" target="_blank" class="d-block"><img src="{{ asset('storage/'.$outlet->billing_payment->proof_of_payment) }}" class="img-fluid rounded border" style="max-height:200px;"></a></div>@endif</div>
    @endif
@else
    @if ($outlet->billing_status === 'rejected')
        <div class="alert alert-danger border-0">
            <h6 class="fw-bold mb-1"><i class="fa fa-times-circle me-1"></i> Bukti Pembayaran Ditolak</h6>
            <p class="small mb-0">Silakan periksa kembali transfer Anda, lalu unggah bukti pembayaran yang benar untuk mengajukan verifikasi ulang.</p>
        </div>
    @endif
    <h5 class="fw-bold mb-2">Pilih Metode Pembayaran</h5>
    <p class="text-muted small mb-4">Kamu bisa membayar otomatis melalui Virtual Account atau transfer manual dengan mengunggah bukti pembayaran.</p>

    <div class="payment-method-grid mb-4">
        @if(in_array($paymentMethod, ['virtual_account', 'both'], true))
        <label class="payment-method-option">
            <input type="radio" name="billing_payment_method" value="virtual_account" {{ $paymentMethod === 'virtual_account' ? 'checked' : ($paymentMethod === 'both' ? 'checked' : '') }}>
            <span class="payment-method-card">
                <i class="fa fa-university fa-2x text-primary"></i>
                <strong>Virtual Account</strong>
                <small class="text-muted">Pembayaran otomatis melalui Midtrans</small>
            </span>
        </label>
        @endif
        @if(in_array($paymentMethod, ['manual', 'both'], true))
        <label class="payment-method-option">
            <input type="radio" name="billing_payment_method" value="manual" {{ $paymentMethod === 'manual' ? 'checked' : '' }}>
            <span class="payment-method-card">
                <i class="fa fa-receipt fa-2x text-success"></i>
                <strong>Transfer Manual</strong>
                <small class="text-muted">Transfer rekening dan upload bukti</small>
            </span>
        </label>
        @endif
    </div>

    <div id="qris-va-method-panel" class="{{ $paymentMethod === 'manual' ? 'd-none' : '' }}">
        <h6 class="fw-bold mb-2">Pilih Bank Virtual Account</h6>
        <form action="{{ route('partner.qris-billing.virtual-account', $outlet) }}" method="POST" id="qris-va-form">
            @csrf
            <div class="bank-grid mb-4">
                <label class="bank-option"><input type="radio" name="va_bank" value="bca"><span class="bank-card"><img src="https://dicoding-web-img.sgp1.digitaloceanspaces.com/original/jobs/dos%3Acompany_logo_pt_bank_central_asia_tbk_200723102046.jpg" alt="Logo BCA"><span>BCA</span></span></label>
                <label class="bank-option"><input type="radio" name="va_bank" value="bni"><span class="bank-card"><img src="https://i.pinimg.com/736x/ef/33/52/ef3352ee1f22a05508f2aa618bac5642.jpg" alt="Logo BNI"><span>BNI</span></span></label>
                <label class="bank-option"><input type="radio" name="va_bank" value="bri"><span class="bank-card"><img src="https://zonalogo.com/assets/logo-bank-bri.webp" alt="Logo BRI"><span>BRI</span></span></label>
                <label class="bank-option"><input type="radio" name="va_bank" value="permata"><span class="bank-card"><img src="https://zonalogo.com/assets/logo-bank-permata.webp" alt="Logo Permata Bank"><span>Permata Bank</span></span></label>
                <label class="bank-option"><input type="radio" name="va_bank" value="cimb"><span class="bank-card"><img src="https://www.itworks.id/wp-content/uploads/2019/06/Logo-Bank-CIMB-Niaga.jpg" alt="Logo CIMB Niaga"><span>CIMB Niaga</span></span></label>
            </div>
            <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill shadow-sm"><i class="fa fa-university me-1"></i> Buat Virtual Account</button>
        </form>
    </div>

    <div id="qris-manual-method-panel" class="{{ $paymentMethod === 'manual' ? '' : 'd-none' }}">
        <div class="alert alert-info border-0">
            <div class="fw-bold mb-2">Rekening Tujuan Transfer</div>
            <div class="small text-muted">Bank</div><div class="fw-bold mb-2">{{ $paymentInstruction['bank_name'] }}</div>
            <div class="small text-muted">Nomor Rekening</div><div class="fw-bold fs-5 mb-2">{{ $paymentInstruction['account_number'] }}</div>
            <div class="small text-muted">Atas Nama</div><div class="fw-bold">{{ $paymentInstruction['account_holder'] }}</div>
        </div>
        <form action="{{ route('partner.qris-billing.manual-transfer', $outlet) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="proof_of_payment" class="form-label fw-bold">Foto Bukti Transfer</label>
                <input type="file" name="proof_of_payment" id="proof_of_payment" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">Format JPG, JPEG, PNG, atau WEBP. Maksimal 5 MB.</div>
                @error('proof_of_payment')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill" onclick="return confirm('Kirim bukti transfer untuk diverifikasi admin?')"><i class="fa fa-upload me-1"></i> Kirim Bukti Transfer</button>
        </form>
    </div>

    <script>
    (function () {
        const radios = document.querySelectorAll('input[name="billing_payment_method"]');
        const vaPanel = document.getElementById('qris-va-method-panel');
        const manualPanel = document.getElementById('qris-manual-method-panel');
        const vaBankInputs = vaPanel ? vaPanel.querySelectorAll('input[name="va_bank"]') : [];
        const proofInput = document.getElementById('proof_of_payment');

        function togglePaymentMethod() {
            const selected = document.querySelector('input[name="billing_payment_method"]:checked')?.value || @json($paymentMethod);
            const useManual = selected === 'manual';
            if (vaPanel) vaPanel.classList.toggle('d-none', useManual);
            if (manualPanel) manualPanel.classList.toggle('d-none', !useManual);
            vaBankInputs.forEach((input) => input.required = !useManual);
            if (proofInput) proofInput.required = useManual;
        }

        radios.forEach((radio) => radio.addEventListener('change', togglePaymentMethod));
        togglePaymentMethod();
    })();
    </script>
@endif
</div></div></div></div>
<div class="mt-4"><a href="{{ route('partner.qris-billing.report') }}" class="btn btn-default"><i class="fa fa-arrow-left me-1"></i> Kembali ke Laporan</a></div>
@endsection
@push('styles')
<style>
.bg-primary-transparent-1{background-color:rgba(0,172,172,.1)}
.payment-method-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.payment-method-option{position:relative;display:block;cursor:pointer;margin:0}
.payment-method-option input{position:absolute;opacity:0;pointer-events:none}
.payment-method-card{min-height:130px;border:1.5px solid #e5e7eb;border-radius:14px;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;padding:14px;text-align:center;transition:border-color .18s ease,box-shadow .18s ease,background .18s ease}
.payment-method-card strong{color:#344054}
.payment-method-option input:checked+.payment-method-card{border-color:#00acac;background:rgba(0,172,172,.06);box-shadow:0 0 0 3px rgba(0,172,172,.12)}
.payment-method-option input:focus-visible+.payment-method-card{outline:3px solid rgba(0,172,172,.3);outline-offset:2px}
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
