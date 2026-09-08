@props([
    'items' => ['Admin', 'Manajemen Add-on', 'Edit Add-on'],
    'title' => 'Edit Add-on',
    'subtitle' => 'Perbarui informasi Add-on',
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

            <form action="{{ route('partner.addons.update', $addon->id) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label for="outlet_id" class="form-label">Outlet <span class="text-danger">*</span></label>
                    <select name="outlet_id" id="outlet_id"
                        class="form-control default-select2 @error('outlet_id') is-invalid @enderror" required readonly>
                        <option value="">-- Pilih Outlet --</option>
                        @foreach ($outlets as $outlet)
                            <option value="{{ $outlet->id }}"
                                {{ old('outlet_id', $addon->outlet_id) == $outlet->id ? 'selected' : '' }}>
                                {{ $outlet->owner->brand_name }} | {{ $outlet->outlet_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('outlet_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nama Add-on -->
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Add-on <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                        name="name" value="{{ old('name', $addon->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Kategori dengan Autocomplete (Perubahan di sini) -->
                <div class="mb-3">
                    <label for="category" class="form-label">Kategori</label>
                    <input type="text" class="form-control @error('category') is-invalid @enderror" id="category"
                        name="category" value="{{ old('category', $addon->category) }}"
                        placeholder="Masukkan atau pilih kategori yang sudah ada">
                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                <!-- Deskripsi -->
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                        rows="3">{{ old('description', $addon->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Harga -->
                <div class="mb-3">
                    <label for="price" class="form-label">Harga <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price"
                        name="price" value="{{ old('price', $addon->price) }}" min="0" step="0.01" required>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Status Aktif -->
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input @error('is_active') is-invalid @enderror" id="is_active"
                        name="is_active" value="1" {{ old('is_active', $addon->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Aktif</label>
                    @error('is_active')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Perbarui
                </button>
                <a href="{{ route('partner.addons.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <link href="{{ asset('assets/plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/plugins/jquery-ui/jquery-ui.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $(".default-select2").select2();

            const existingCategories = @json($existingCategories);

            $("#category").autocomplete({
                source: existingCategories,
                minLength: 0,
                select: function(event, ui) {
                    console.log("Kategori dipilih: " + ui.item.value);
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });

            $('#is_active').prop('checked', {{ old('is_active', $addon->is_active) ? 'true' : 'false' }});
        });
    </script>
@endpush
