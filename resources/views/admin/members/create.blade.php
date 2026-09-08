@props([
    'items' => ['Admin', 'Manajemen Member', 'Tambah Member'],
    'title' => 'Tambah Member',
    'subtitle' => 'Tambahkan Member Baru'
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
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.members.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Field Nama Lengkap -->
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                        name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Field Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                        name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password dan Konfirmasi Password -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                            name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation"
                            name="password_confirmation" required>
                    </div>
                </div>

                <!-- Field Gambar (Opsional) -->
                <div class="mb-3">
                    <label for="image" class="form-label">Gambar </label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image"
                        name="image" accept="image/*" onchange="previewImage(event)">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="mt-2">
                        <img id="imagePreview" src="#" class="img-thumbnail d-none" style="max-width: 150px;">
                    </div>
                </div>


                <button type="submit" class="btn btn-primary">Create</button>
                <a href="{{ route('admin.members.index') }}" class="btn btn-default">Cancel</a>
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
            imgPreview.classList.remove('d-none');
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endpush
