@php
    $title = 'Edit Profil Brand'; // Ubah title agar lebih deskriptif
    $currentPage = request()->query('page', 'profile'); // Get current page from query parameter, default to 'profile'
@endphp
@extends('layouts.dashboard.app')

@push('styles')
    <link href="{{ asset('assets/plugins/gritter/css/jquery.gritter.css') }}" rel="stylesheet" />
    {{-- Tambahkan style jika ada untuk tampilan form owner, atau gunakan Bootstrap default --}}
    <style>
        .brand-tabs {
            border-bottom: 1px solid #e9ecef;
            background: transparent;
            padding: 0;
            gap: 8px;
        }
        .brand-tabs .nav-link {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 8px 16px;
            background: #ffffff;
            color: #495057;
            font-weight: 600;
        }
        .brand-tabs .nav-link:hover {
            background: #eef5ff;
            border-color: #bcd3ff;
        }
        .brand-tabs .nav-link.active {
            background: #0d6efd;
            color: #ffffff;
            border-color: #0d6efd;
        }
        .brand-tab-content {
            background: transparent;
            border: 0;
            border-radius: 0;
            padding: 0;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/gritter/js/jquery.gritter.js') }}"></script>
    <script>
        @if (session('success'))
            $.gritter.add({
                title: 'Success!',
                text: '{{ session('success') }}',
                sticky: false,
                time: 3000,
                class_name: 'gritter-light'
            });
        @endif

        @if (session('error'))
            $.gritter.add({
                title: 'Error!',
                text: '{{ session('error') }}',
                sticky: false,
                time: 3000,
                class_name: 'gritter-light'
            });
        @endif

        function previewImageBrand(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('brandLogoPreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
@endpush

@section('content')

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title }}</h4>
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

            <ul class="nav nav-tabs brand-tabs">
                <li class="nav-item">
                    <a class="nav-link {{ $currentPage == 'profile' ? 'active' : '' }}"
                        href="{{ route('partner.brand.profile.edit', ['page' => 'profile']) }}">Informasi Brand</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentPage == 'bank' ? 'active' : '' }}"
                        href="{{ route('partner.brand.profile.edit', ['page' => 'bank']) }}">Informasi Bank</a>
                </li>
            </ul>
            <div class="tab-content pt-3 brand-tab-content">
                <div class="tab-pane fade {{ $currentPage == 'profile' ? 'show active' : '' }}" id="profile-tab-pane">
                    <form action="{{ route('partner.brand.profile.update', ['page' => 'profile']) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3 text-primary"><i class="fas fa-building me-2"></i>Informasi Brand (Owner)</h5>

                        <div class="mb-3">
                            <label for="brand_name" class="form-label">Nama Brand <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('brand_name') is-invalid @enderror" id="brand_name"
                                name="brand_name" value="{{ old('brand_name', getBrand()->brand_name) }}" required>
                            @error('brand_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="brand_email" class="form-label">Email Brand <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('brand_email') is-invalid @enderror" id="brand_email"
                                name="brand_email" value="{{ old('brand_email', getBrand()->brand_email) }}" required>
                            @error('brand_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="brand_phone" class="form-label">Nomor Telepon Brand</label>
                            <input type="text" class="form-control @error('brand_phone') is-invalid @enderror" id="brand_phone"
                                name="brand_phone" value="{{ old('brand_phone', getBrand()->brand_phone) }}">
                            @error('brand_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="brand_address" class="form-label">Alamat Brand</label>
                            <textarea class="form-control @error('brand_address') is-invalid @enderror" id="brand_address" name="brand_address"
                                rows="3">{{ old('brand_address', getBrand()->address) }}</textarea>
                            @error('brand_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="brand_logo" class="form-label">Logo Brand</label>
                            <input type="file" class="form-control @error('brand_logo') is-invalid @enderror" id="brand_logo"
                                name="brand_logo" accept="image/*" onchange="previewImageBrand(event)">
                            <small class="form-text text-muted">Ukuran maksimal 2MB. Format: JPG, PNG, GIF.</small>
                            @error('brand_logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="mt-2">
                                <img id="brandLogoPreview"
                                    src="{{ getBrand()->brand_logo ? asset('storage/' . getBrand()->brand_logo) : asset('assets/img/default-brand-logo.png') }}"
                                    alt="Brand Logo Preview" class="img-thumbnail" style="max-width: 200px; height: auto;">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Update Informasi Brand</button>
                    </form>
                </div>

                <div class="tab-pane fade {{ $currentPage == 'bank' ? 'show active' : '' }}" id="bank-tab-pane">
                    <form action="{{ route('partner.brand.profile.update', ['page' => 'bank']) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3 text-primary"><i class="fas fa-money-check-alt me-2"></i>Informasi Bank untuk Penarikan Dana</h5>

                        <div class="mb-3">
                            <label for="bank_name" class="form-label">Nama Bank</label>
                            <input type="text" class="form-control @error('bank_name') is-invalid @enderror" id="bank_name"
                                name="bank_name" value="{{ old('bank_name', getBrand()->bank_name) }}">
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="bank_account_number" class="form-label">Nomor Rekening</label>
                            <input type="text" class="form-control @error('bank_account_number') is-invalid @enderror" id="bank_account_number"
                                name="bank_account_number" value="{{ old('bank_account_number', getBrand()->bank_account_number) }}">
                            @error('bank_account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="bank_account_holder_name" class="form-label">Nama Pemilik Rekening</label>
                            <input type="text" class="form-control @error('bank_account_holder_name') is-invalid @enderror" id="bank_account_holder_name"
                                name="bank_account_holder_name" value="{{ old('bank_account_holder_name', getBrand()->bank_account_holder_name) }}">
                            @error('bank_account_holder_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Update Informasi Bank</button>
                    </form>
                </div>
            </div>
            </div>
    </div>
@endsection

@push('scripts')
    <script>
        // No changes needed here, the existing previewImageBrand function is sufficient.
    </script>
@endpush
