@php
    $title = 'Edit Profil Pengguna';
    $profileImage = data_get($user, 'image');
    $profileName = data_get($user, 'name', 'Super Admin');
    $profileEmail = data_get($user, 'email', '');
@endphp
@extends('layouts.dashboard.app')

@push('styles')
<link href="{{ asset('assets/plugins/gritter/css/jquery.gritter.css') }}" rel="stylesheet" />
@endpush

@push('scripts')
<script src="{{ asset('assets/plugins/gritter/js/jquery.gritter.js') }}"></script>
<script>
    @if (session('success'))
        $.gritter.add({
            title: 'Success!',
            text: @json(session('success')),
            sticky: false,
            time: 3000,
            class_name: 'gritter-light'
        });
    @endif

    @if (session('error'))
        $.gritter.add({
            title: 'Error!',
            text: @json(session('error')),
            sticky: false,
            time: 3000,
            class_name: 'gritter-light'
        });
    @endif

    function previewImage(event) {
        const file = event.target.files && event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function () {
            document.getElementById('imagePreview').src = reader.result;
        };
        reader.readAsDataURL(file);
    }
</script>
@endpush

@section('content')
<div class="panel panel-inverse">
    <div class="panel-heading">
        <h4 class="panel-title">{{ $title }}</h4>
        <div class="panel-heading-btn">
            <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i class="fa fa-expand"></i></a>
            <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i class="fa fa-redo"></i></a>
        </div>
    </div>

    <div class="panel-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($isConfigAdmin)
            <div class="alert alert-info">
                Akun Super Admin menggunakan konfigurasi aplikasi. Pengaturan nama dan kredensial akun ini tidak diubah dari halaman profil.
            </div>
        @endif

        <form action="{{ route('profile.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="mb-3 text-center">
                <img
                    id="imagePreview"
                    src="{{ $profileImage ? (str_starts_with($profileImage, 'http') ? $profileImage : asset($profileImage)) : asset('assets/img/default-user.png') }}"
                    alt="Foto profil"
                    style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;"
                >

                @unless ($isConfigAdmin)
                    <div>
                        <input type="file" class="d-none @error('image') is-invalid @enderror" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                        <button type="button" class="btn btn-xs btn-purple btn-secondary mt-2" onclick="document.getElementById('image').click();">Ganti Foto</button>
                        @error('image')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                @endunless
            </div>

            <h5 class="mt-4 mb-3 text-primary"><i class="fas fa-user me-2"></i>Informasi Akun</h5>

            <div class="mb-3">
                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                    value="{{ old('name', $profileName) }}" {{ $isConfigAdmin ? 'disabled' : 'required' }}>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                    value="{{ old('email', $profileEmail) }}" {{ $isConfigAdmin ? 'disabled' : 'required' }}>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @unless ($isConfigAdmin)
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password (Kosongkan jika tidak ingin mengubah)</label>
                        <input autocomplete="new-password" type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input autocomplete="new-password" type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation">
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="current_password" class="form-label">Password Saat Ini (Wajib diisi jika mengubah password)</label>
                    <input autocomplete="current-password" type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password">
                    @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary mt-3">Update</button>
            @endunless

            <a href="{{ route('home') }}" class="btn btn-default mt-3">Kembali</a>
        </form>
    </div>
</div>
@endsection
