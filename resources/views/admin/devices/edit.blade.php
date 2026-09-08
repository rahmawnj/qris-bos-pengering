@props([
    'items' => ['Admin', 'Manajemen Device', 'Edit Device'],
    'title' => 'Edit Device',
    'subtitle' => 'Perbarui informasi Device',
])
@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title }}</h4>
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

            <form action="{{ route('admin.devices.update', $device->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Nama Device -->
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Device <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                        name="name" value="{{ old('name', $device->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Pilih Outlet -->
                <div class="mb-3">
                    <label for="outlet_id" class="form-label">Outlet <span class="text-danger">*</span></label>
                    <select name="outlet_id" id="outlet_id" class="form-control default-select2 @error('outlet_id') is-invalid @enderror"
                        required>
                        <option value="">-- Pilih Outlet --</option>
                        @foreach ($outlets as $outlet)
                            <option value="{{ $outlet->id }}"
                                {{ old('outlet_id', $device->outlet_id) == $outlet->id ? 'selected' : '' }}>
                                {{ $outlet->owner->brand_name }} | {{ $outlet->outlet_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('outlet_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Perbarui
                </button>
                <a href="{{ route('admin.devices.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
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
    </script>
@endpush
