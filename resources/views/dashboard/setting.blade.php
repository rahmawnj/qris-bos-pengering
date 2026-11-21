@php
    $title = 'Pengaturan Sistem';
@endphp

@extends('layouts.dashboard.app')

@section('title', $title)

@section('content')
    <ol class="breadcrumb float-xl-end">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">{{ $title }}</li>
    </ol>
    <h1 class="page-header">{{ $title }} <small>Konfigurasi parameter sistem</small></h1>

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fa fa-cog fa-fw me-1"></i> Form Pengaturan</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i class="fa fa-expand"></i></a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i class="fa fa-redo"></i></a>
            </div>
        </div>
        <div class="panel-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4">
                    <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('admin.setting.submit') }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="row mb-3">
                    <label for="app_name" class="col-md-3 col-form-label text-md-end">Nama Aplikasi</label>
                    <div class="col-md-7">
                        <input type="text" class="form-control @error('app_name') is-invalid @enderror" id="app_name" name="app_name" value="{{ old('app_name', env('APP_NAME')) }}" required>
                        @error('app_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="qris_default_provider" class="col-md-3 col-form-label text-md-end">Provider QRIS</label>
                    <div class="col-md-7">
                        <select class="form-select @error('qris_default_provider') is-invalid @enderror" id="qris_default_provider" name="qris_default_provider" required>
                            @foreach ($supportedProviders as $provider)
                                <option value="{{ $provider }}" {{ old('qris_default_provider', $qrisDefaultProvider) === $provider ? 'selected' : '' }}>{{ strtoupper($provider) }}</option>
                            @endforeach
                        </select>
                        @error('qris_default_provider') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="qris_billing_price_per_device" class="col-md-3 col-form-label text-md-end">Biaya / Device</label>
                    <div class="col-md-7">
                        <input type="number" min="0" step="1000" class="form-control @error('qris_billing_price_per_device') is-invalid @enderror" id="qris_billing_price_per_device" name="qris_billing_price_per_device" value="{{ old('qris_billing_price_per_device', $qrisBillingPricePerDevice) }}" required>
                        @error('qris_billing_price_per_device') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="qris_billing_advance_days" class="col-md-3 col-form-label text-md-end">Buka Pembayaran Sebelum Jatuh Tempo</label>
                    <div class="col-md-7">
                        <div class="input-group">
                            <input type="number" min="0" max="31" class="form-control @error('qris_billing_advance_days') is-invalid @enderror" id="qris_billing_advance_days" name="qris_billing_advance_days" value="{{ old('qris_billing_advance_days', $qrisBillingAdvanceDays) }}" required>
                            <span class="input-group-text">hari</span>
                        </div>
                        <div class="form-text">Contoh 5: pembayaran perpanjangan mulai tersedia 5 hari sebelum tanggal aktif sampai.</div>
                        @error('qris_billing_advance_days') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="qris_billing_max_fee_mode" class="col-md-3 col-form-label text-md-end">Batas Iuran</label>
                    <div class="col-md-7">
                        <select class="form-select @error('qris_billing_max_fee_mode') is-invalid @enderror" id="qris_billing_max_fee_mode" name="qris_billing_max_fee_mode" required>
                            <option value="capped" {{ old('qris_billing_max_fee_mode', $qrisBillingMaxFeeMode) === 'capped' ? 'selected' : '' }}>Pakai maksimal biaya</option>
                            <option value="unlimited" {{ old('qris_billing_max_fee_mode', $qrisBillingMaxFeeMode) === 'unlimited' ? 'selected' : '' }}>Unlimited</option>
                        </select>
                        @error('qris_billing_max_fee_mode') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3" id="qris_billing_max_fee_row">
                    <label for="qris_billing_max_fee" class="col-md-3 col-form-label text-md-end">Maksimal Iuran</label>
                    <div class="col-md-7">
                        <input type="number" min="0" step="1000" class="form-control @error('qris_billing_max_fee') is-invalid @enderror" id="qris_billing_max_fee" name="qris_billing_max_fee" value="{{ old('qris_billing_max_fee', $qrisBillingMaxFee) }}" required>
                        @error('qris_billing_max_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <hr class="my-4">
                <div class="row mb-3">
                    <div class="col-md-7 offset-md-3">
                        <h5 class="mb-1">Biaya Layanan QRIS per Provider</h5>
                        <div class="form-text">Masukkan persentase fee. Contoh 0,7 berarti 0,7%. Nilai disimpan ke file .env.</div>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="xendit_qris_fee_rate" class="col-md-3 col-form-label text-md-end">Fee Xendit</label>
                    <div class="col-md-7">
                        <div class="input-group">
                            <input type="number" min="0" max="100" step="0.001" class="form-control @error('xendit_qris_fee_rate') is-invalid @enderror" id="xendit_qris_fee_rate" name="xendit_qris_fee_rate" value="{{ old('xendit_qris_fee_rate', $xenditQrisFeeRate) }}" required>
                            <span class="input-group-text">%</span>
                        </div>
                        @error('xendit_qris_fee_rate') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="midtrans_qris_fee_rate" class="col-md-3 col-form-label text-md-end">Fee Midtrans</label>
                    <div class="col-md-7">
                        <div class="input-group">
                            <input type="number" min="0" max="100" step="0.001" class="form-control @error('midtrans_qris_fee_rate') is-invalid @enderror" id="midtrans_qris_fee_rate" name="midtrans_qris_fee_rate" value="{{ old('midtrans_qris_fee_rate', $midtransQrisFeeRate) }}" required>
                            <span class="input-group-text">%</span>
                        </div>
                        @error('midtrans_qris_fee_rate') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="midtrans_partner_qris_fee_rate" class="col-md-3 col-form-label text-md-end">Fee Midtrans Partner</label>
                    <div class="col-md-7">
                        <div class="input-group">
                            <input type="number" min="0" max="100" step="0.001" class="form-control @error('midtrans_partner_qris_fee_rate') is-invalid @enderror" id="midtrans_partner_qris_fee_rate" name="midtrans_partner_qris_fee_rate" value="{{ old('midtrans_partner_qris_fee_rate', $midtransPartnerQrisFeeRate) }}" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Default 0% agar transaksi partner tidak dipotong fee.</div>
                        @error('midtrans_partner_qris_fee_rate') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                <hr class="my-4">
                <div class="row mb-3"><div class="col-md-7 offset-md-3"><h5 class="mb-0">Rekening Pembayaran</h5></div></div>

                <div class="row mb-4">
                    <label class="col-md-3 col-form-label text-md-end">Metode Pembayaran</label>
                    <div class="col-md-7">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="billing-method-choice w-100">
                                    <input type="radio" name="qris_billing_payment_method" value="manual" {{ old('qris_billing_payment_method', $qrisBillingPaymentMethod) === 'manual' ? 'checked' : '' }}>
                                    <span><i class="fa fa-receipt fa-lg d-block mb-2"></i><strong>Manual</strong><small>Transfer + bukti</small></span>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="billing-method-choice w-100">
                                    <input type="radio" name="qris_billing_payment_method" value="virtual_account" {{ old('qris_billing_payment_method', $qrisBillingPaymentMethod) === 'virtual_account' ? 'checked' : '' }}>
                                    <span><i class="fa fa-university fa-lg d-block mb-2"></i><strong>Virtual Account</strong><small>Midtrans otomatis</small></span>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="billing-method-choice w-100">
                                    <input type="radio" name="qris_billing_payment_method" value="both" {{ old('qris_billing_payment_method', $qrisBillingPaymentMethod) === 'both' ? 'checked' : '' }}>
                                    <span><i class="fa fa-random fa-lg d-block mb-2"></i><strong>Keduanya</strong><small>Manual + VA</small></span>
                                </label>
                            </div>
                        </div>
                        @error('qris_billing_payment_method') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        <div class="form-text mt-2">Pilih metode yang ingin ditampilkan pada halaman perpanjangan QRIS outlet.</div>
                    </div>
                </div>

                <div id="qris_manual_bank_fields">
                    <div class="row mb-3">
                        <label for="qris_billing_payment_bank_name" class="col-md-3 col-form-label text-md-end">Nama Bank</label>
                        <div class="col-md-7"><input type="text" class="form-control @error('qris_billing_payment_bank_name') is-invalid @enderror" id="qris_billing_payment_bank_name" name="qris_billing_payment_bank_name" value="{{ old('qris_billing_payment_bank_name', $qrisBillingPaymentBankName) }}" required>@error('qris_billing_payment_bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                    </div>
                    <div class="row mb-3">
                        <label for="qris_billing_payment_account_number" class="col-md-3 col-form-label text-md-end">Nomor Rekening</label>
                        <div class="col-md-7"><input type="text" class="form-control @error('qris_billing_payment_account_number') is-invalid @enderror" id="qris_billing_payment_account_number" name="qris_billing_payment_account_number" value="{{ old('qris_billing_payment_account_number', $qrisBillingPaymentAccountNumber) }}" required>@error('qris_billing_payment_account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                    </div>
                    <div class="row mb-3">
                        <label for="qris_billing_payment_account_holder" class="col-md-3 col-form-label text-md-end">Atas Nama</label>
                        <div class="col-md-7"><input type="text" class="form-control @error('qris_billing_payment_account_holder') is-invalid @enderror" id="qris_billing_payment_account_holder" name="qris_billing_payment_account_holder" value="{{ old('qris_billing_payment_account_holder', $qrisBillingPaymentAccountHolder) }}" required>@error('qris_billing_payment_account_holder') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                    </div>
                </div>

                <div class="row mt-4"><div class="col-md-7 offset-md-3"><button type="submit" class="btn btn-primary px-4"><i class="fa fa-save me-1"></i> Simpan Perubahan</button></div></div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
<style>
.billing-method-choice{display:block;cursor:pointer;position:relative;margin:0}
.billing-method-choice input{position:absolute;opacity:0;pointer-events:none}
.billing-method-choice span{display:flex;min-height:124px;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:3px;padding:12px;border:1px solid #d9dee5;border-radius:10px;background:#fff;transition:.18s ease}
.billing-method-choice span i{color:#64748b}
.billing-method-choice span small{font-size:12px;color:#6b7280}
.billing-method-choice input:checked+span{border:2px solid #348fe2;background:rgba(52,143,226,.07);box-shadow:0 0 0 2px rgba(52,143,226,.08)}
.billing-method-choice input:focus-visible+span{outline:3px solid rgba(52,143,226,.25);outline-offset:2px}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const paymentMethodInputs = document.querySelectorAll('input[name="qris_billing_payment_method"]');
    const manualBankFields = document.getElementById('qris_manual_bank_fields');
    function syncManualBankFields() {
        const selected = document.querySelector('input[name="qris_billing_payment_method"]:checked')?.value;
        const showManual = selected === 'manual' || selected === 'both';
        manualBankFields.style.display = showManual ? '' : 'none';
    }
    const manualBankInputs = manualBankFields.querySelectorAll('input');
    function syncManualBankFields() {
        const selected = document.querySelector('input[name="qris_billing_payment_method"]:checked')?.value;
        const showManual = selected === 'manual' || selected === 'both';
        manualBankFields.style.display = showManual ? '' : 'none';
        manualBankInputs.forEach((input) => input.required = showManual);
    }
    paymentMethodInputs.forEach((input) => input.addEventListener('change', syncManualBankFields));
    syncManualBankFields();

    const modeSelect = document.getElementById('qris_billing_max_fee_mode');
    const maxFeeRow = document.getElementById('qris_billing_max_fee_row');
    const maxFeeInput = document.getElementById('qris_billing_max_fee');
    function syncMaxFeeField() {
        const isUnlimited = modeSelect.value === 'unlimited';
        maxFeeRow.style.display = isUnlimited ? 'none' : '';
        maxFeeInput.required = !isUnlimited;
    }
    modeSelect.addEventListener('change', syncMaxFeeField);
    syncMaxFeeField();
});
</script>
@endpush
