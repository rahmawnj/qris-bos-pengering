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
                <div class="row mb-3"><div class="col-md-7 offset-md-3"><h5 class="mb-0">Rekening Pembayaran</h5></div></div>

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

                <div class="row mt-4"><div class="col-md-7 offset-md-3"><button type="submit" class="btn btn-primary px-4"><i class="fa fa-save me-1"></i> Simpan Perubahan</button></div></div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
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
