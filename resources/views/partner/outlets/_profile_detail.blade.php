@push('styles')
    {{-- Hapus semua style terkait Leaflet dan map --}}
    <style>
        /* Hanya style yang tidak spesifik map/operational hours, jika ada yang lain */
    </style>
@endpush

<h5 class="text-secondary mb-4"><i class="fas fa-edit me-2"></i>Edit Profil Outlet</h5>
<div class="card p-5">
    <form action="{{ route('partner.outlets.update', $outlet->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- 1. Nama Outlet --}}
        <div class="mb-3">
            <label for="outlet_name" class="form-label">Nama Outlet</label>
            <input type="text" class="form-control" id="outlet_name" name="outlet_name"
                value="{{ old('outlet_name', $outlet->outlet_name) }}" required
                {{ !$feature->can('partner.outlets.update') ? 'disabled' : '' }}>
            @error('outlet_name')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        {{-- 2. Alamat Lengkap --}}
        <div class="mb-3">
            <label for="address" class="form-label">Alamat Lengkap</label>
            <textarea class="form-control" id="address" name="address" rows="3"
                {{ !$feature->can('partner.outlets.update') ? 'disabled' : '' }}>{{ old('address', $outlet->address) }}</textarea>
            @error('address')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        {{-- 3. Nomor Telepon Outlet --}}
        <div class="mb-3">
            <label for="phone_number" class="form-label">Nomor Telepon Outlet</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number"
                value="{{ old('phone_number', $outlet->phone_number) }}"
                {{ !$feature->can('partner.outlets.update') ? 'disabled' : '' }}>
            @error('phone_number')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        {{-- 4. Zona Waktu --}}
        <div class="mb-3">
            <label for="timezone" class="form-label">Zona Waktu</label>
            <select class="form-select" id="timezone" name="timezone" required
                {{ !$feature->can('partner.outlets.update') ? 'disabled' : '' }}>
                <option value="WIB" {{ old('timezone', $outlet->timezone) == 'WIB' ? 'selected' : '' }}>
                    WIB (Western Indonesian Time)
                </option>
                <option value="WITA" {{ old('timezone', $outlet->timezone) == 'WITA' ? 'selected' : '' }}>
                    WITA (Central Indonesian Time)
                </option>
                <option value="WIT" {{ old('timezone', $outlet->timezone) == 'WIT' ? 'selected' : '' }}>
                    WIT (Eastern Indonesian Time)
                </option>
            </select>
            @error('timezone')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        {{-- Tombol Simpan --}}
        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2"
            {{ !$feature->can('partner.outlets.update') ? 'disabled' : '' }}>
            <i class="fas fa-save me-2"></i> Simpan Perubahan Profil
        </button>
    </form>
</div>

@push('scripts')
    {{-- Hapus semua script terkait Leaflet, Geocoding, Image Preview, dan Operational Hours --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Jika ada script lain yang perlu dipertahankan, letakkan di sini.
            // Saat ini, semua script terkait fitur yang dihapus sudah dibuang.
        });
    </script>
@endpush
