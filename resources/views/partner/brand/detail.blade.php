@extends('layouts.dashboard.app')
@php
    $feature = getData();
@endphp

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card overflow-hidden mb-4">
                    {{-- Card Header - Biru Pastel --}}
                    <div class="card-header p-4 rounded-top-4 d-flex flex-column flex-md-row align-items-md-center align-items-start"
                        style="background-color: #c6dadc; border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                        <div class="d-flex align-items-center mb-3 mb-md-0 me-md-4">
                            @if ($outlet->owner && $outlet->owner->brand_logo)
                                <img src="{{ asset('storage/' . $outlet->owner->brand_logo) }}" alt="Brand Logo"
                                    class="me-3 rounded-circle border border-primary p-1"
                                    style="width: 80px; height: 80px; object-fit: contain; background-color: white;">
                            @else
                                <i class="fas fa-store-alt fa-4x me-4 text-primary"></i>
                            @endif
                            <div>
                                <h1 class="mb-1 text-dark fw-bold">{{ $outlet->outlet_name }}</h1>
                                <p class="mb-0 text-dark fs-5">Kode: #{{ $outlet->code }}</p>
                            </div>
                        </div>


                        {{-- <div class="ms-md-auto mt-3 mt-md-0 text-md-end">
                            <form action="{{ route('partner.outlets.update-status', $outlet->id) }}" method="POST"
                                class="d-inline-flex align-items-center" id="outletStatusForm">
                                @csrf
                                @method('PATCH')

                                <select class="form-select form-select-sm rounded-pill w-auto" id="outlet_status_select"
                                    name="status">
                                    <option value="1" {{ $outlet->status ? 'selected' : '' }}>Buka</option>
                                    <option value="0" {{ !$outlet->status ? 'selected' : '' }}>Tutup</option>
                                </select>

                            </form>
                        </div> --}}
                    </div>

                    {{-- Card Body --}}
                    <div class="card-body p-4 p-md-5" style="background-color: rgb(223, 237, 255)">

                        <ul class="nav nav-tabs outlet-tabs mb-4" id="outletTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ request()->query('tab', 'profile') == 'profile' ? 'active' : '' }}"
                                    id="profile-tab"
                                    href="{{ route('partner.outlets.detail', ['outlet' => $outlet->id, 'tab' => 'profile']) }}"
                                    role="tab" aria-controls="profile"
                                    aria-selected="{{ request()->query('tab', 'profile') == 'profile' ? 'true' : 'false' }}">
                                    <i class="fas fa-info-circle me-1"></i> Profil & Ringkasan
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ request()->query('tab') == 'services' ? 'active' : '' }}"
                                    id="services-tab"
                                    href="{{ route('partner.outlets.detail', ['outlet' => $outlet->id, 'tab' => 'services']) }}"
                                    role="tab" aria-controls="services"
                                    aria-selected="{{ request()->query('tab') == 'services' ? 'true' : 'false' }}">
                                    <i class="fas fa-hand-holding-usd me-1"></i> Layanan
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ request()->query('tab') == 'edit-profile' ? 'active' : '' }}"
                                    id="edit-profile-tab"
                                    href="{{ route('partner.outlets.detail', ['outlet' => $outlet->id, 'tab' => 'edit-profile']) }}"
                                    role="tab" aria-controls="edit-profile"
                                    aria-selected="{{ request()->query('tab') == 'edit-profile' ? 'true' : 'false' }}">
                                    <i class="fas fa-edit me-1"></i> Edit Profil
                                </a>
                            </li>
                        </ul>

                        {{-- Tab Content based on query parameter (This part remains unchanged) --}}
                        {{-- ... your existing @if/@elseif/@endif block for tab content ... --}}
                        {{-- Tab Content based on query parameter --}}
                        @if (request()->query('tab', 'profile') == 'profile')
                            {{-- Profile & Summary Tab Content --}}
                            <div class="row g-4">
                                {{-- Login & Timezone --}}
                                <div class="col-lg-6">
                                    <div class="card card-body h-100 shadow-sm border-0">
                                        <h5 class="text-secondary mb-3"><i class="fas fa-user-lock me-2"></i>Informasi Login
                                        </h5>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <p class="mb-1 text-muted small">Outlet</p>
                                                <p class="fs-5 fw-semibold text-dark">{{ $outlet->outlet_name ?? '-' }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1 text-muted small">Zona Waktu</p>
                                                <p class="fs-5 fw-semibold text-dark">{{ $outlet->timezone }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Contact & Location --}}
                                <div class="col-lg-6">
                                    <div class="card card-body h-100 shadow-sm border-0">
                                        <h5 class="text-secondary mb-3"><i class="fas fa-map-marker-alt me-2"></i>Detail
                                            Kontak & Lokasi</h5>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <p class="mb-1 text-muted small">Alamat Lengkap</p>
                                                <p class="fs-5 fw-semibold text-dark">{{ $outlet->address ?? '-' }}</p>
                                            </div>
                                            <div class="col-12">
                                                <p class="mb-1 text-muted small">Nomor Telepon Outlet</p>
                                                <p class="fs-5 fw-semibold text-dark">{{ $outlet->phone_number ?? '-' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Summary Statistics --}}
                                <div class="col-lg-12 mt-4">
                                    <div class="card card-body shadow-sm border-0">
                                        <h5 class="text-secondary mb-3"><i class="fas fa-chart-bar me-2"></i>Ringkasan
                                            Statistik</h5>
                                        <div class="row text-center g-3">
                                            <div class="col-md-4">
                                                <div class="statistic-box p-3 border rounded-3 bg-light">
                                                    <i class="fas fa-desktop fa-2x text-primary mb-2"></i>
                                                    <h3 class="fw-bold mb-0">{{ $outlet->devices->count() ?? 0 }}</h3>
                                                    <p class="text-muted mb-0">Mesin Terhubung</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="statistic-box p-3 border rounded-3 bg-light">
                                                    <i class="fas fa-users-cog fa-2x text-info mb-2"></i>
                                                    <h3 class="fw-bold mb-0">{{ $outlet->cashiers->count() ?? 0 }}</h3>
                                                    <p class="text-muted mb-0">Akun Kasir</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="statistic-box p-3 border rounded-3 bg-light">
                                                    <i class="fas fa-tags fa-2x text-warning mb-2"></i>
                                                    <h3 class="fw-bold mb-0">{{ $outlet->services->count() ?? 0 }}</h3>
                                                    <p class="text-muted mb-0">Jumlah Layanan</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if ($outlet->owner)
                                    {{-- Brand Owner Info --}}
                                    <div class="col-lg-12 mt-4">
                                        <div class="card card-body shadow-sm border-0">
                                            <h5 class="text-secondary mb-3"><i class="fas fa-building me-2"></i>Informasi
                                                Pemilik Brand</h5>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <p class="mb-1 text-muted small">Nama Brand</p>
                                                    <p class="fs-5 fw-semibold text-dark">
                                                        {{ $outlet->owner->brand_name ?? '-' }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1 text-muted small">Email Brand</p>
                                                    <p class="fs-5 fw-semibold text-dark">
                                                        {{ $outlet->owner->brand_email ?? '-' }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1 text-muted small">Nomor Telepon Brand</p>
                                                    <p class="fs-5 fw-semibold text-dark">
                                                        {{ $outlet->owner->brand_phone ?? '-' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @elseif (request()->query('tab') == 'services')
                            {{-- Services Tab Content --}}
                            <h5 class="text-secondary mb-4"><i class="fas fa-hand-holding-usd me-2"></i>Daftar Layanan
                            </h5>

                            <form action="{{ route('partner.outlets.services.update', $outlet->id) }}" method="POST"
                                id="servicesForm">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="outlet_id" value="{{ $outlet->id }}">

                                <div id="serviceRowsContainer">
                                    @forelse($outlet->services as $service)
                                        <div class="row g-3 mb-3 align-items-center service-row existing-service"
                                            data-service-id="{{ $service->id }}">
                                            <div class="col-md-4">
                                                <label for="service_name_{{ $service->id }}"
                                                    class="form-label visually-hidden">Nama Layanan</label>
                                                <input type="text" class="form-control form-control-lg rounded-pill"
                                                    id="service_name_{{ $service->id }}"
                                                    name="services[{{ $service->id }}][name]"
                                                    value="{{ $service->name }}" placeholder="Nama Layanan" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="service_price_{{ $service->id }}"
                                                    class="form-label visually-hidden">Harga Layanan</label>
                                                <div class="input-group input-group-lg rounded-pill-group">
                                                    <span class="input-group-text rounded-start-pill">Rp</span>
                                                    <input type="number" class="form-control rounded-end-pill"
                                                        id="service_price_{{ $service->id }}"
                                                        name="services[{{ $service->id }}][price]"
                                                        value="{{ number_format($service->price, 2, '.', '') }}"
                                                        placeholder="Harga" step="0.01" min="0" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch mt-2 mb-2">
                                                    <input class="form-check-input service-type-toggle" type="checkbox"
                                                        role="switch" id="toggle_type_{{ $service->id }}"
                                                        {{ $service->serviceTypes->isNotEmpty() ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="toggle_type_{{ $service->id }}">Dengan Tipe
                                                        Layanan</label>
                                                </div>
                                                <div class="service-type-checkboxes mt-2"
                                                    {{ $service->serviceTypes->isNotEmpty() ? '' : 'style="display:none;"' }}>
                                                    <p class="text-muted small mb-1">Pilih Tipe:</p>
                                                    @foreach ($serviceTypes as $type)
                                                        <div class="form-check">
                                                            <input class="form-check-input service-type-checkbox"
                                                                type="checkbox"
                                                                name="services[{{ $service->id }}][service_type_ids][]"
                                                                id="service_{{ $service->id }}_type_{{ $type['id'] }}"
                                                                value="{{ $type['id'] }}"
                                                                {{ $service->serviceTypes->contains('id', $type['id']) ? 'checked' : '' }}
                                                                {{ $service->serviceTypes->isNotEmpty() ? '' : 'disabled' }}>
                                                            <label class="form-check-label"
                                                                for="service_{{ $service->id }}_type_{{ $type['id'] }}">
                                                                {{ $type['name'] }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="col-md-1 text-end">
                                                <button type="button"
                                                    class="btn btn-outline-danger rounded-circle btn-sm remove-service-row"
                                                    data-service-id="{{ $service->id }}" data-type="existing">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <input type="hidden" name="services[{{ $service->id }}][_delete]"
                                                    value="0" class="delete-flag">
                                            </div>
                                        </div>
                                    @empty
                                        <div class="alert alert-info text-center" role="alert" id="noServicesMessage">
                                            Belum ada layanan yang terdaftar untuk outlet ini.
                                        </div>
                                    @endforelse
                                </div>

                                {{-- HIDDEN TEMPLATE for new service rows --}}
                                <template id="newServiceRowTemplate">
                                    <div class="row g-3 mb-3 align-items-center service-row new-service">
                                        <div class="col-md-4">
                                            <label for="new_service_name" class="form-label visually-hidden">Nama
                                                Layanan</label>
                                            <input type="text" class="form-control form-control-lg rounded-pill"
                                                name="new_services[KEY][name]" placeholder="Nama Layanan" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="new_service_price" class="form-label visually-hidden">Harga
                                                Layanan</label>
                                            <div class="input-group input-group-lg rounded-pill-group">
                                                <span class="input-group-text rounded-start-pill">Rp</span>
                                                <input type="number" class="form-control rounded-end-pill"
                                                    name="new_services[KEY][price]" placeholder="Harga" step="0.01"
                                                    min="0" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check form-switch mt-2 mb-2">
                                                <input class="form-check-input service-type-toggle" type="checkbox"
                                                    role="switch" id="new_toggle_type_KEY">
                                                <label class="form-check-label" for="new_toggle_type_KEY">Dengan Tipe
                                                    Layanan</label>
                                            </div>
                                            <div class="service-type-checkboxes mt-2" style="display:none;">
                                                <p class="text-muted small mb-1">Pilih Tipe:</p>
                                                @foreach ($serviceTypes as $type)
                                                    <div class="form-check">
                                                        <input class="form-check-input service-type-checkbox"
                                                            type="checkbox" name="new_services[KEY][service_type_ids][]"
                                                            id="new_service_KEY_type_{{ $type['id'] }}"
                                                            value="{{ $type['id'] }}" disabled>
                                                        <label class="form-check-label"
                                                            for="new_service_KEY_type_{{ $type['id'] }}">
                                                            {{ $type['name'] }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-md-1 text-end">
                                            <button type="button"
                                                class="btn btn-outline-danger rounded-circle btn-sm remove-service-row"
                                                data-type="new">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-primary rounded-pill py-2 mt-3"
                                        id="addServiceRow">
                                        <i class="fas fa-plus-circle me-2"></i> Tambah Layanan Baru
                                    </button>
                                    <button type="submit" class="btn btn-success rounded-pill py-2 mt-2">
                                        <i class="fas fa-save me-2"></i> Simpan Perubahan Layanan
                                    </button>
                                </div>
                            </form>
                        @elseif (request()->query('tab') == 'edit-profile')
                            {{-- Edit Profile Tab Content --}}
                            <h5 class="text-secondary mb-4"><i class="fas fa-edit me-2"></i>Edit Profil Outlet</h5>
                            <div class="card p-5">
                                <form action="{{ route('partner.outlets.update', $outlet->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-3">
                                        <label for="outlet_name" class="form-label">Nama Outlet</label>
                                        <input type="text" class="form-control" id="outlet_name" name="outlet_name"
                                            value="{{ old('outlet_name', $outlet->outlet_name) }}" required>
                                        @error('outlet_name')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="address" class="form-label">Alamat Lengkap</label>
                                        <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $outlet->address) }}</textarea>
                                        @error('address')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone_number" class="form-label">Nomor Telepon Outlet</label>
                                        <input type="text" class="form-control" id="phone_number" name="phone_number"
                                            value="{{ old('phone_number', $outlet->phone_number) }}">
                                        @error('phone_number')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="timezone" class="form-label">Zona Waktu</label>
                                        <select class="form-select" id="timezone" name="timezone" required>
                                            <option value="WIB"
                                                {{ old('timezone', $outlet->timezone) == 'WIB' ? 'selected' : '' }}>WIB
                                                (Western Indonesian Time)</option>
                                            <option value="WITA"
                                                {{ old('timezone', $outlet->timezone) == 'WITA' ? 'selected' : '' }}>WITA
                                                (Central Indonesian Time)</option>
                                            <option value="WIT"
                                                {{ old('timezone', $outlet->timezone) == 'WIT' ? 'selected' : '' }}>WIT
                                                (Eastern Indonesian Time)</option>
                                        </select>
                                        @error('timezone')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>


                                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2">
                                        <i class="fas fa-save me-2"></i> Simpan Perubahan Profil
                                    </button>
                                </form>
                            </div>
                        @endif
                        {{-- END Tab Content --}}

                        @if ($feature->can('partner.outlets.destroy'))
                            <div class="mt-5 pt-4 border-top d-flex justify-content-end flex-wrap gap-3">
                                <form action="{{ route('partner.outlets.destroy', $outlet->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger rounded-pill px-4 py-2"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus outlet {{ $outlet->outlet_name }}? Aksi ini tidak bisa dibatalkan.');">
                                        <i class="fas fa-trash-alt me-2"></i> Hapus Outlet
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        #outlet_status_select {
            background-color: #f8f9fa;
            /* A light grey default */
            color: #212529;
            /* Dark text */
            border-color: #ced4da;
            /* Default border */
        }

        /* Style for 'Buka' (Open) status */
        #outlet_status_select.status-buka {
            background-color: #d4edda;
            /* Light green */
            color: #155724;
            /* Dark green text */
            border-color: #c3e6cb;
        }

        /* Style for 'Tutup' (Closed) status */
        #outlet_status_select.status-tutup {
            background-color: #f8d7da;
            /* Light red */
            color: #721c24;
            /* Dark red text */
            border-color: #f5c6cb;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }

        .container-fluid {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        .text-white-75 {
            color: rgba(255, 255, 255, 0.75) !important;
        }

        .badge.rounded-pill {
            padding: 0.6em 1.2em;
            font-size: 0.95rem;
        }

        .text-muted.small {
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #7b8a9d !important;
        }

        .fs-5.fw-semibold {
            font-size: 1.25rem !important;
            font-weight: 600 !important;
            color: #343a40;
        }

        .fs-6.fw-semibold {
            font-size: 1rem !important;
            font-weight: 600 !important;
            color: #343a40;
        }


        h5.text-secondary {
            color: #6c757d !important;
            font-weight: 600;
            margin-top: 0;
            /* Adjusted for card-body layout */
            margin-bottom: 1rem;
        }

        h6.text-primary {
            color: #007bff !important;
            font-weight: 600;
        }

        .btn.rounded-pill {
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }

        .btn.rounded-pill:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        hr.my-4 {
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            margin-top: 2.5rem !important;
            margin-bottom: 2.5rem !important;
        }

        /* Styles for service form */
        .service-row {
            background-color: #ffffff;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
        }

        .service-row+.service-row {
            margin-top: 1rem;
        }

        .input-group.rounded-pill-group .input-group-text,
        .input-group.rounded-pill-group .form-control {
            border-radius: 0.5rem;
            height: calc(2.8rem + 2px);
        }

        .input-group.rounded-pill-group .input-group-text {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        .input-group.rounded-pill-group .form-control {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }

        .form-control-lg.rounded-pill {
            border-radius: 0.5rem !important;
        }

        .form-select-lg.rounded-pill {
            border-radius: 0.5rem !important;
            height: calc(2.8rem + 2px);
            /* Match form-control-lg height */
        }

        .service-type-checkboxes {
            border: 1px solid #e9ecef;
            padding: 0.75rem;
            border-radius: 0.5rem;
            background-color: #f8f9fa;
            margin-top: 0.75rem;
        }

        .service-type-checkboxes .form-check {
            margin-bottom: 0.5rem;
        }

        .service-type-checkboxes .form-check:last-child {
            margin-bottom: 0;
        }

        /* Style for deleted rows to visually hide them */
        .service-row.deleted {
            opacity: 0.5;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .service-row.deleted input,
        .service-row.deleted select,
        .service-row.deleted .form-check-input {
            text-decoration: line-through;
            pointer-events: none;
            /* Disable interaction */
        }

        /* New styles for compact cards in profile tab */
        .card.card-body.h-100 {
            height: 100%;
            border-radius: 12px;
        }

        .statistic-box {
            background-color: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .statistic-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .card-header {
                padding: 1.5rem !important;
                flex-direction: column;
                align-items: flex-start !important;
                text-align: left;
            }

            .card-header .d-flex.align-items-center.mb-3 {
                width: 100%;
                justify-content: flex-start;
                margin-bottom: 1.5rem !important;
            }

            .card-header img,
            .card-header .fa-store-alt {
                margin-bottom: 1rem;
            }

            .card-header .d-flex.flex-column.flex-grow-1 {
                width: 100%;
            }

            .card-header .ms-md-auto {
                margin-left: 0 !important;
                margin-top: 1.5rem;
                width: 100%;
                text-align: left !important;
            }

            .card-body {
                padding: 2rem 1.5rem !important;
            }

            .d-flex.border-bottom.flex-wrap {
                justify-content: center;
                /* Center tabs on small screens */
            }

            .d-flex.border-bottom.flex-wrap .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .service-row .col-md-4,
            .service-row .col-md-3,
            .service-row .col-md-1 {
                width: 100%;
            }

            .service-row .col-md-4:not(:first-child),
            .service-row .col-md-3 {
                margin-top: 0.5rem;
            }

            .service-row .col-md-1 {
                text-align: center !important;
                margin-top: 1rem;
            }

            .outlet-tabs {
                border-bottom: none;
                /* Remove default Bootstrap bottom border */
                gap: 10px;
                /* Adjust gap between tabs */
                flex-wrap: wrap;
                /* Ensure tabs wrap on smaller screens */
            }

            .outlet-tabs .nav-item {
                margin-bottom: 0;
                /* Important: remove default margin that pushes tabs down */
            }

            .outlet-tabs .nav-link {
                /* Default/Inactive Tab Styles */
                border: 1px solid #CFE8FC;
                /* Lighter border for inactive tabs, close to header blue */
                border-bottom-left-radius: 0.5rem;
                border-bottom-right-radius: 0.5rem;
                border-top-left-radius: 0.5rem;
                border-top-right-radius: 0.5rem;
                padding: 10px 18px;
                /* Adjusted padding */
                color: #4CAF50;
                /* A soft green for inactive text */
                background-color: #F0F8FA;
                /* Very light background, almost like your header */
                transition: all 0.2s ease-in-out;
                font-weight: 500;
                margin-bottom: -1px;
                /* Offset to make active tab border look seamless */
                text-align: center;
                text-decoration: none;
                /* Ensure no underline */
            }

            .outlet-tabs .nav-link:hover:not(.active) {
                border-color: #A7D9FC;
                /* Slightly darker hover border */
                color: #388E3C;
                /* Darker green on hover */
                background-color: #E6F3F7;
                /* Slightly darker light blue on hover */
                transform: translateY(-2px);
                /* Slight lift on hover */
                box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
                /* Subtle shadow on hover */
            }

            .outlet-tabs .nav-link.active {
                /* Active Tab Styles */
                color: #212529;
                /* Darker text for active tab for better readability */
                background-color: #E0F7FA;
                /* Match your card-header's pastel blue */
                border-color: #E0F7FA;
                /* Match border to background */
                border-bottom-color: transparent;
                /* KEY: Makes the bottom appear flat and connected */
                position: relative;
                /* For z-index if needed */
                z-index: 1;
                /* Ensures active tab is on top */
                font-weight: 600;
                transform: none;
                /* Remove hover transform for active tab */
                box-shadow: none;
                /* Remove hover shadow for active tab */
                color: #007bff;
                /* Keep primary blue for active text */
                border-top: 2px solid #007bff;
                /* Add a top border for active tab with primary blue */
            }

            /* Optional: Adjust for smaller screens if needed */
            @media (max-width: 767.98px) {
                .outlet-tabs {
                    flex-direction: row;
                    /* Keep row for wrapping */
                    justify-content: flex-start;
                    gap: 8px;
                    /* Slightly smaller gap on small screens */
                }

                .outlet-tabs .nav-link {
                    padding: 8px 14px;
                    /* Smaller padding on small screens */
                    font-size: 0.85rem;
                }
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const outletStatusSelect = document.getElementById('outlet_status_select');
            const outletStatusForm = document.getElementById('outletStatusForm');

            if (outletStatusSelect && outletStatusForm) {
                outletStatusSelect.addEventListener('change', function() {
                    // Submit the form automatically when the selection changes
                    outletStatusForm.submit();
                });
            }


            let newServiceKey = 0; // To ensure unique names for new service inputs

            // Function to enable/disable checkboxes based on toggle switch
            function toggleServiceTypeCheckboxes(toggleInput) {
                const parentRow = toggleInput.closest('.service-row');
                const checkboxesContainer = parentRow.querySelector('.service-type-checkboxes');
                const checkboxes = checkboxesContainer.querySelectorAll('.service-type-checkbox');

                if (toggleInput.checked) {
                    checkboxesContainer.style.display = 'block';
                    checkboxes.forEach(cb => cb.removeAttribute('disabled'));
                } else {
                    checkboxesContainer.style.display = 'none';
                    checkboxes.forEach(cb => {
                        cb.setAttribute('disabled', 'disabled');
                        cb.checked = false; // Uncheck all when disabled
                    });
                }
            }

            // Initial setup for existing service rows
            document.querySelectorAll('.service-row.existing-service .service-type-toggle').forEach(toggle => {
                toggleServiceTypeCheckboxes(toggle); // Set initial state
                toggle.addEventListener('change', function() {
                    toggleServiceTypeCheckboxes(this);
                });
            });

            // Add new service row
            document.getElementById('addServiceRow').addEventListener('click', function() {
                const template = document.getElementById('newServiceRowTemplate');
                const clone = template.content.cloneNode(true);
                const newRow = clone.firstElementChild;

                // Replace KEY placeholder with unique key
                const inputs = newRow.querySelectorAll('[name*="KEY"]');
                inputs.forEach(input => {
                    input.name = input.name.replace('KEY', newServiceKey);
                    input.id = input.id.replace('KEY', newServiceKey); // Update IDs for labels
                });
                const labels = newRow.querySelectorAll('[for*="KEY"]');
                labels.forEach(label => {
                    label.htmlFor = label.htmlFor.replace('KEY', newServiceKey);
                });

                // Update toggle switch ID and label
                const newToggle = newRow.querySelector('.service-type-toggle');
                newToggle.id = `new_toggle_type_${newServiceKey}`;
                newRow.querySelector(`label[for="new_toggle_type_KEY"]`).htmlFor =
                    `new_toggle_type_${newServiceKey}`;

                // Add event listener for the new toggle switch
                newToggle.addEventListener('change', function() {
                    toggleServiceTypeCheckboxes(this);
                });

                document.getElementById('serviceRowsContainer').appendChild(newRow);
                newServiceKey++;

                // Hide "Belum ada layanan" message if present
                const noServicesMessage = document.getElementById('noServicesMessage');
                if (noServicesMessage) {
                    noServicesMessage.style.display = 'none';
                }
            });

            // Remove service row (both existing and new)
            document.getElementById('serviceRowsContainer').addEventListener('click', function(event) {
                if (event.target.closest('.remove-service-row')) {
                    const button = event.target.closest('.remove-service-row');
                    const serviceRow = button.closest('.service-row');
                    const serviceType = button.dataset.type;

                    if (serviceType === 'existing') {
                        // For existing services, mark for deletion
                        if (confirm(
                                'Apakah Anda yakin ingin menghapus layanan ini? Ini akan ditandai untuk dihapus saat Anda menyimpan.'
                            )) {
                            serviceRow.classList.toggle('deleted');
                            const deleteFlag = serviceRow.querySelector('.delete-flag');
                            if (deleteFlag) {
                                deleteFlag.value = serviceRow.classList.contains('deleted') ? '1' : '0';
                            }
                            // Optionally change button appearance
                            button.classList.toggle('btn-outline-danger');
                            button.classList.toggle('btn-outline-warning');
                            button.querySelector('i').classList.toggle('fa-trash');
                            button.querySelector('i').classList.toggle('fa-undo');
                        }
                    } else if (serviceType === 'new') {
                        // For new services, remove the row completely
                        serviceRow.remove();

                        // Show "Belum ada layanan" message if no services left
                        if (this.querySelectorAll('.service-row').length === 0) {
                            const noServicesMessage = document.getElementById('noServicesMessage');
                            if (noServicesMessage) {
                                noServicesMessage.style.display = 'block';
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>
    <script>
        @if (session('success'))
            swal({
                title: "Berhasil!",
                text: "{{ session('success') }}",
                icon: "success",
                button: "OK",
            });
        @endif

        @if (session('error'))
            swal({
                title: "Gagal!",
                text: "{{ session('error') }}",
                icon: "error",
                button: "Coba Lagi",
            });
        @endif
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const serviceRowsContainer = document.getElementById('serviceRowsContainer');
            const addServiceRowButton = document.getElementById('addServiceRow');
            const newServiceRowTemplate = document.getElementById('newServiceRowTemplate');
            const noServicesMessage = document.getElementById('noServicesMessage');
            let newRowIndex = 0;

            function handleServiceTypeToggle(event) {
                const toggle = event.target;
                const serviceRow = toggle.closest('.service-row');
                const checkboxesContainer = serviceRow.querySelector('.service-type-checkboxes');
                const checkboxes = checkboxesContainer.querySelectorAll('.service-type-checkbox');

                if (toggle.checked) {
                    checkboxesContainer.style.display = 'block';
                    checkboxes.forEach(checkbox => checkbox.removeAttribute('disabled'));
                } else {
                    checkboxesContainer.style.display = 'none';
                    checkboxes.forEach(checkbox => {
                        checkbox.setAttribute('disabled', 'disabled');
                        checkbox.checked = false;
                    });
                }
            }

            // Function to add a new service row
            addServiceRowButton.addEventListener('click', function() {
                if (noServicesMessage && serviceRowsContainer.contains(noServicesMessage)) {
                    noServicesMessage.remove(); // Remove "No services" message if present
                }

                const templateContent = newServiceRowTemplate.content.cloneNode(true);
                const newRow = templateContent.querySelector('.service-row');

                // Update names and IDs to be unique for new rows
                const nameInput = newRow.querySelector('input[name*="[name]"]');
                const priceInput = newRow.querySelector('input[name*="[price]"]');
                const typeToggle = newRow.querySelector('.service-type-toggle');
                const typeToggleLabel = newRow.querySelector(`label[for*="new_toggle_type_KEY"]`);
                const checkboxes = newRow.querySelectorAll('.service-type-checkbox');

                nameInput.name = `new_services[${newRowIndex}][name]`;
                priceInput.name = `new_services[${newRowIndex}][price]`;

                // Update IDs and names for checkboxes
                checkboxes.forEach(checkbox => {
                    // Update name to include [] for array
                    checkbox.name = `new_services[${newRowIndex}][service_type_ids][]`;
                    checkbox.id = `new_service_${newRowIndex}_type_${checkbox.value}`;
                    const label = newRow.querySelector(
                        `label[for*="new_service_KEY_type_${checkbox.value}"]`);
                    if (label) {
                        label.setAttribute('for', checkbox.id);
                    }
                });

                // Update IDs for toggle and its label
                typeToggle.id = `new_toggle_type_${newRowIndex}`;
                if (typeToggleLabel) {
                    typeToggleLabel.setAttribute('for', `new_toggle_type_${newRowIndex}`);
                }

                serviceRowsContainer.appendChild(newRow);
                newRowIndex++;

                // Attach event listeners for the new row's elements
                newRow.querySelector('.remove-service-row').addEventListener('click',
                    handleRemoveServiceRow);
                newRow.querySelector('.service-type-toggle').addEventListener('change',
                    handleServiceTypeToggle);

                // For new rows, service type checkboxes should be disabled by default if the toggle is off.
                // The template already handles the display:none and disabled attributes correctly initially.
            });

            // Function to handle removing a service row
            function handleRemoveServiceRow(event) {
                const rowToRemove = event.currentTarget.closest('.service-row');
                const type = event.currentTarget.dataset.type; // 'existing' or 'new'

                if (type === 'existing') {
                    const deleteFlag = rowToRemove.querySelector('.delete-flag');
                    const toggleButton = event.currentTarget; // The delete/undo button

                    if (rowToRemove.classList.contains('deleted')) {
                        // Restore: remove 'deleted' class and reset flag
                        rowToRemove.classList.remove('deleted');
                        deleteFlag.value = '0';

                        // Re-enable inputs and toggles
                        rowToRemove.querySelectorAll(
                                'input:not(.service-type-checkbox), .service-type-toggle'
                            ) // Exclude service-type-checkbox from blanket re-enable
                            .forEach(input => input.removeAttribute('disabled'));

                        // Restore correct state of checkboxes based on toggle
                        const toggle = rowToRemove.querySelector('.service-type-toggle');
                        const checkboxes = rowToRemove.querySelectorAll('.service-type-checkbox');
                        const checkboxesContainer = rowToRemove.querySelector('.service-type-checkboxes');

                        if (toggle.checked) {
                            checkboxesContainer.style.display = 'block';
                            checkboxes.forEach(checkbox => checkbox.removeAttribute('disabled'));
                        } else {
                            checkboxesContainer.style.display = 'none';
                            checkboxes.forEach(checkbox => checkbox.setAttribute('disabled', 'disabled'));
                        }

                        toggleButton.classList.remove('btn-danger');
                        toggleButton.classList.add('btn-outline-danger');
                        toggleButton.innerHTML = '<i class="fas fa-trash"></i>';
                    } else {
                        // Mark for deletion: add 'deleted' class and set flag
                        if (confirm(
                                'Apakah Anda yakin ingin menghapus layanan ini? Ini akan ditandai untuk dihapus saat Anda menyimpan perubahan.'
                            )) {
                            rowToRemove.classList.add('deleted');
                            deleteFlag.value = '1';
                            // Disable all inputs in the row except the delete flag
                            rowToRemove.querySelectorAll('input:not(.delete-flag), select').forEach(input => input
                                .setAttribute(
                                    'disabled', 'disabled'));
                            rowToRemove.querySelector('.service-type-checkboxes').style.display =
                                'none'; // Hide checkboxes
                            toggleButton.classList.remove('btn-outline-danger');
                            toggleButton.classList.add('btn-danger');
                            toggleButton.innerHTML = '<i class="fas fa-undo"></i>'; // Change icon to undo
                        }
                    }
                } else {
                    // For newly added rows, just remove from UI
                    rowToRemove.remove();
                }

                // If no visible services are left, show the "No services" message
                const visibleServiceRows = serviceRowsContainer.querySelectorAll('.service-row:not(.deleted)');
                if (visibleServiceRows.length === 0 && !document.getElementById('noServicesMessage')) {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-info text-center';
                    alertDiv.role = 'alert';
                    alertDiv.id = 'noServicesMessage';
                    alertDiv.textContent = 'Belum ada layanan yang terdaftar untuk outlet ini.';
                    serviceRowsContainer.appendChild(alertDiv);
                } else if (visibleServiceRows.length > 0 && document.getElementById('noServicesMessage')) {
                    document.getElementById('noServicesMessage').remove();
                }
            }

            // Attach event listeners to all initially existing elements
            document.querySelectorAll('.remove-service-row').forEach(button => {
                button.addEventListener('click', handleRemoveServiceRow);
            });
            document.querySelectorAll('.service-type-toggle').forEach(toggle => {
                toggle.addEventListener('change', handleServiceTypeToggle);
                // Ensure initial state is correct on page load
                const checkboxesContainer = toggle.closest('.service-row').querySelector(
                    '.service-type-checkboxes');
                const checkboxes = checkboxesContainer.querySelectorAll('.service-type-checkbox');
                if (!toggle.checked) {
                    checkboxesContainer.style.display = 'none';
                    checkboxes.forEach(checkbox => checkbox.setAttribute('disabled', 'disabled'));
                } else {
                    // If toggle is checked, ensure checkboxes are enabled
                    checkboxesContainer.style.display = 'block';
                    checkboxes.forEach(checkbox => checkbox.removeAttribute('disabled'));
                }
            });

            // Initial check for no services message if page loads with no services
            // This is important to correctly show/hide the message on initial load
            const initialVisibleServiceRows = serviceRowsContainer.querySelectorAll('.service-row:not(.deleted)');
            if (initialVisibleServiceRows.length === 0 && !document.getElementById('noServicesMessage')) {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-info text-center';
                alertDiv.role = 'alert';
                alertDiv.id = 'noServicesMessage';
                alertDiv.textContent = 'Belum ada layanan yang terdaftar untuk outlet ini.';
                serviceRowsContainer.appendChild(alertDiv);
            } else if (initialVisibleServiceRows.length > 0 && document.getElementById('noServicesMessage')) {
                // If there are services but the message is still there (e.g., from a previous empty state), remove it.
                document.getElementById('noServicesMessage').remove();
            }
        });
    </script>
@endpush
