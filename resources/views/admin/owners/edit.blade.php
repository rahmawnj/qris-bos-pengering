@props([
    'items' => ['Admin', 'Brand Management', 'Brand List'],
    'title' => 'Brand List',
    'subtitle' => 'Manage registered Brand here'
])
@extends('layouts.dashboard.app')
@section('title', $title ?? '')
@section('content')
<x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />


    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title ?? '' }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i
                        class="fa fa-expand"></i></a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i
                        class="fa fa-redo"></i></a>

            </div>
        </div>
        <div class="panel-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('admin.owners.update', $owner) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3 text-center">
                    <img id="imagePreview" src="{{ $owner->user->image ? asset($owner->user->image) : asset('assets/img/default-user.png') }}" style="width: 80px; height: 80px; cursor: pointer; border-radius: 50%; object-fit: cover;" onclick="document.getElementById('image').click();">
                    <input type="file" class="d-none @error('image') is-invalid @enderror" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                    <button type="button" class="btn btn-xs btn-purple btn-secondary mt-2" onclick="document.getElementById('image').click();">Ganti Foto</button>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Pemilik <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                           name="name" value="{{ old('name', $owner->user->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                           name="email" value="{{ old('email', $owner->user->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password dan Konfirmasi Password dalam satu baris (opsional) -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                               name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
                        <input type="password" class="form-control" id="password_confirmation"
                               name="password_confirmation" placeholder="Kosongkan jika tidak ingin mengubah">
                    </div>
                </div>




                <div class="mb-3 text-center">
                    <img id="brandLogoPreview" src="{{ $owner->brand_logo ? asset($owner->brand_logo) : asset('assets/img/default-img.png') }}" style="height: 80px; cursor: pointer; border-radius: 10%; object-fit: cover;" onclick="document.getElementById('brand_logo').click();">
                    <input type="file" class="d-none @error('brand_logo') is-invalid @enderror" id="brand_logo" name="brand_logo" accept="image/*" onchange="previewBrandLogo(event)">
                    <button type="button" class="btn btn-xs btn-purple btn-secondary mt-2" onclick="document.getElementById('brand_logo').click();">Ganti Logo</button>
                    @error('brand_logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                <div class="mb-3">
                    <label for="brand_name" class="form-label">Nama Brand <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('brand_name') is-invalid @enderror" id="brand_name"
                           name="brand_name" value="{{ old('brand_name', $owner->brand_name) }}" required>
                    @error('brand_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="brand_email" class="form-label">Email Brand <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('brand_email') is-invalid @enderror" id="brand_email"
                           name="brand_email" value="{{ old('brand_email', $owner->brand_email) }}" required>
                    @error('brand_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="payment_account_type" class="form-label">Tipe Akun Pembayaran <span class="text-danger">*</span></label>
                    <select class="form-select @error('payment_account_type') is-invalid @enderror" id="payment_account_type" name="payment_account_type" required onchange="toggleMerchantId()">
                        <option value="general" {{ old('payment_account_type', $owner->payment_account_type) === 'general' ? 'selected' : '' }}>General (Bos Pengering)</option>
                        <option value="owner" {{ old('payment_account_type', $owner->payment_account_type) === 'owner' ? 'selected' : '' }}>Owner (Akun Sendiri)</option>
                    </select>
                    <div class="form-text">Pilih general untuk mengikuti akun Bos Pengering atau owner untuk menggunakan merchant ID sendiri.</div>
                    @error('payment_account_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3" id="merchant_id_group">
                    <label for="merchant_id" class="form-label">Merchant ID Owner <span class="text-danger" id="merchant_id_required">*</span></label>
                    <input type="text" class="form-control @error('merchant_id') is-invalid @enderror" id="merchant_id"
                           name="merchant_id" value="{{ old('merchant_id', $owner->merchant_id) }}" maxlength="100">
                    <div class="form-text">Isi merchant ID jika menggunakan akun owner sendiri.</div>
                    @error('merchant_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="withdrawal_fee_charged" class="form-label">Apakah saat tarik uang dikenakan admin? <span class="text-danger">*</span></label>
                    <select class="form-select @error('withdrawal_fee_charged') is-invalid @enderror" id="withdrawal_fee_charged" name="withdrawal_fee_charged" required>
                        <option value="1" {{ old('withdrawal_fee_charged', $owner->withdrawal_fee_charged ? 1 : 0) == '1' ? 'selected' : '' }}>Ya, dikenakan</option>
                        <option value="0" {{ old('withdrawal_fee_charged', $owner->withdrawal_fee_charged ? 1 : 0) == '0' ? 'selected' : '' }}>Tidak, tidak dikenakan</option>
                    </select>
                    @error('withdrawal_fee_charged')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Alamat <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address"
                              name="address" required>{{ old('address', $owner->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.owners.index') }}" class="btn btn-default">Batal</a>
            </form>



        </div>
    </div>
    <!-- END panel -->
@endsection
@push('scripts')
<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const imgPreview = document.getElementById('imagePreview');
            imgPreview.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    function previewBrandLogo(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const logoPreview = document.getElementById('brandLogoPreview');
            logoPreview.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    function toggleMerchantId() {
        const accountType = document.getElementById('payment_account_type').value;
        const merchantIdInput = document.getElementById('merchant_id');
        const merchantIdRequired = document.getElementById('merchant_id_required');
        const serverKeyInput = document.getElementById('merchant_server_key');
        const serverKeyRequired = document.getElementById('merchant_server_key_required');

        if (accountType === 'owner') {
            merchantIdInput.required = true;
            merchantIdRequired.style.display = 'inline';
            serverKeyInput.required = true;
            serverKeyRequired.style.display = 'inline';
        } else {
            merchantIdInput.required = false;
            merchantIdRequired.style.display = 'none';
            serverKeyInput.required = false;
            serverKeyRequired.style.display = 'none';
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', toggleMerchantId);
</script>
@endpush
