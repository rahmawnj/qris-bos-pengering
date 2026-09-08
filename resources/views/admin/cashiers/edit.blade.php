@props([
    'items' => ['Admin', 'Manajemen Kasir', 'Edit Kasir'],
    'title' => 'Edit Kasir',
    'subtitle' => 'Ubah informasi kasir',
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

            <form action="{{ route('admin.cashiers.update', $cashier) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3 text-center">
                    <img id="imagePreview"
                        src="{{ $cashier->user->image ? asset($cashier->user->image) : asset('assets/img/default-user.png') }}"
                        style="width: 80px; height: 80px; cursor: pointer; border-radius: 50%; object-fit: cover;"
                        onclick="document.getElementById('image').click();">
                    <input type="file" class="d-none @error('image') is-invalid @enderror" id="image" name="image"
                        accept="image/*" onchange="previewImage(event)">
                    <button type="button" class="btn btn-xs btn-purple btn-secondary mt-2"
                        onclick="document.getElementById('image').click();">Ganti Foto</button>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Nama Kasir <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                        name="name" value="{{ old('name', $cashier->user->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                <div class="mb-3">
                    <label for="email" class="form-label">Email Kasir <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                        name="email" value="{{ old('email', $cashier->user->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Optional Password -->
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
                        <label for="password_confirmation" class="form-label">Konfirmasi Sandi</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                            placeholder="Kosongkan jika tidak ingin mengubah">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="outlet_id" class="form-label">Pilih Outlet <span class="text-danger">*</span></label>
                    <select name="outlet_id" class="form-control default-select2 @error('outlet_id') is-invalid @enderror"
                        required>
                        <option value="">-- Pilih Outlet --</option>
                        @foreach ($outlets as $outlet)
                            <option value="{{ $outlet->id }}"
                                {{ old('outlet_id', $cashier->outlet_id) == $outlet->id ? 'selected' : '' }}>
                                {{ $outlet->owner->brand_name }} - {{ $outlet->outlet_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('outlet_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.cashiers.index') }}" class="btn btn-default">Batal</a>
            </form>
        </div>
    </div>

@endsection

@push('styles')
    <link href="{{ asset('assets/plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet" />
@endpush
@push('scripts')
    <script src="{{ asset('assets/plugins/select2/dist/js/select2.min.js') }}"></script>
    <script>
        $(".default-select2").select2();
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('imagePreview').src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
@endpush
