@props([
    'items' => ['Admin', 'Manajemen Outlet', 'Edit Outlet'],
    'title' => 'Edit Outlet',
    'subtitle' => 'Perbarui informasi Outlet',
])
@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title ?? '' }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand">
                    <i class="fa fa-expand"></i>
                </a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload">
                    <i class="fa fa-redo"></i>
                </a>
            </div>
        </div>
        <div class="panel-body">
            <div class="d-flex justify-content-center mb-4">
                <img id="brand_logo_preview" src="{{ asset($outlet->owner->brand_logo ?? '') }}" alt="Brand Logo"
                    style="max-height: 150px; display: {{ $outlet->owner->brand_logo ? 'block' : 'none' }}; border: 2px solid #ddd; border-radius: 10%;">
            </div>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.outlets.update', $outlet) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Pilih Owner -->
                    <div class="col-md-6 mb-3">
                        <label for="owner_id" class="form-label">Owner <span class="text-danger">*</span></label>
                        <select name="owner_id" id="owner_id" class="form-control @error('owner_id') is-invalid @enderror"
                            required onchange="updateLogoPreview()">
                            <option value="">-- Pilih Owner --</option>
                            @foreach ($owners as $owner)
                                <option value="{{ $owner->id }}"
                                    {{ old('owner_id', $outlet->owner_id ?? '') == $owner->id ? 'selected' : '' }}
                                    data-logo="{{ asset($owner->brand_logo) }}">
                                    {{ $owner->brand_name ?? $owner->brand_email }}
                                </option>
                            @endforeach
                        </select>
                        @error('owner_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Outlet Name -->
                    <div class="col-md-6 mb-3">
                        <label for="outlet_name" class="form-label">Nama Outlet <span class="text-danger">*</span></label>
                        <input type="text" name="outlet_name" id="outlet_name"
                            class="form-control @error('outlet_name') is-invalid @enderror"
                            value="{{ old('outlet_name', $outlet->outlet_name ?? '') }}" required>
                        @error('outlet_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>


                <!-- Data Outlet -->
                <div class="mb-3">
                    <label for="address" class="form-label">Alamat <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" required>{{ old('address', $outlet->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Field Timezone -->
                <div class="mb-3">
                    <label for="timezone" class="form-label">Timezone <span class="text-danger">*</span></label>
                    <select name="timezone" id="timezone" class="form-control @error('timezone') is-invalid @enderror"
                        required>
                        <option value="">-- Pilih Timezone --</option>
                        <option value="WIB" {{ old('timezone', $outlet->timezone) == 'WIB' ? 'selected' : '' }}>
                            WIB (GMT+7)
                        </option>
                        <option value="WITA" {{ old('timezone', $outlet->timezone) == 'WITA' ? 'selected' : '' }}>
                            WITA (GMT+8)
                        </option>
                        <option value="WIT" {{ old('timezone', $outlet->timezone) == 'WIT' ? 'selected' : '' }}>
                            WIT (GMT+9)
                        </option>
                    </select>
                    @error('timezone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror"
                        required>
                        <option value="1" {{ old('status', $outlet->status) == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('status', $outlet->status) == '0' ? 'selected' : '' }}>Tidak Aktif
                        </option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="qris_billing_enabled" class="form-label">Perpanjangan QRIS <span class="text-danger">*</span></label>
                    <select name="qris_billing_enabled" id="qris_billing_enabled"
                        class="form-control @error('qris_billing_enabled') is-invalid @enderror" required>
                        <option value="1" {{ old('qris_billing_enabled', $outlet->qris_billing_enabled) == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('qris_billing_enabled', $outlet->qris_billing_enabled) == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    <div class="form-text text-muted">
                        Pilih Tidak Aktif untuk outlet yang tidak menggunakan transaksi QRIS.
                    </div>
	                    @error('qris_billing_enabled')
	                        <div class="invalid-feedback">{{ $message }}</div>
	                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update
                </button>
                <a href="{{ route('admin.outlets.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </form>
        </div>
    </div>
    <script>
        function updateLogoPreview() {
            const ownerSelect = document.getElementById('owner_id');
            const selectedOption = ownerSelect.options[ownerSelect.selectedIndex];
            const logoPreview = document.getElementById('brand_logo_preview');
            const logoUrl = selectedOption.getAttribute('data-logo');

            if (logoUrl) {
                logoPreview.src = logoUrl;
                logoPreview.style.display = 'block';
            } else {
                logoPreview.style.display = 'none';
            }
        }

	        // Initialize the logo preview on page load
	        document.addEventListener('DOMContentLoaded', () => {
	            updateLogoPreview();

		        });
		    </script>
	@endsection
