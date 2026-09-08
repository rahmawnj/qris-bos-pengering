@extends('layouts.dashboard.app')
@php
    $feature = getData();
@endphp

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card overflow-hidden mb-4 rounded-4 shadow-sm border-0">
                    {{-- Card Header - Warna Lebih Soft & Elegan --}}
                    <div class="card-header p-4 d-flex flex-column flex-md-row align-items-md-center align-items-start bg-light"
                        style="border-bottom: 1px solid #dee2e6;">
                        <div class="d-flex align-items-center mb-3 mb-md-0 me-md-4">
                            {{-- <img src="{{ asset( path: $outlet->owner->brand_logo) }}" alt="Brand Logo"
                                class="me-3 rounded-circle border border-2 border-primary p-1"
                                style="width: 80px; height: 80px; object-fit: contain; background-color: white;"> --}}
                            <div>
                                <h1 class="mb-1 text-dark fw-bold fs-4">{{ $outlet->outlet_name }}</h1>
                                <p class="mb-0 text-muted fs-6">Kode: #{{ $outlet->code }}</p>
                            </div>
                        </div>



                        <div class="ms-md-auto mt-3 mt-md-0 d-flex align-items-center gap-2">
    <span class="text-muted fw-semibold">Status Outlet:</span>
    @if($outlet->has_overdue_billing)
        <span class="badge bg-danger p-2 px-3 rounded-pill"><i class="fas fa-times-circle me-1"></i> Terblokir (Jatuh Tempo)</span>
    @elseif($outlet->status == 1)
        <span class="badge bg-success p-2 px-3 rounded-pill"><i class="fas fa-check-circle me-1"></i> Buka</span>
    @else
        <span class="badge bg-secondary p-2 px-3 rounded-pill"><i class="fas fa-minus-circle me-1"></i> Tutup</span>
    @endif
</div>

{{-- Script dihapus karena toggle sudah tidak digunakan oleh owner --}}

                    </div>

                    {{-- Card Body dengan Background Putih Bersih --}}
                    <div class="card-body p-4 p-md-5 bg-white">
                        <ul class="nav nav-tabs mb-4 border-bottom-0" id="outletTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link rounded-top-3 {{ request()->query('tab', 'profile') == 'profile' ? 'active' : '' }}"
                                    id="profile-tab"
                                    href="{{ route('partner.outlets.detail', ['outlet' => $outlet->id, 'tab' => 'profile']) }}"
                                    role="tab" aria-controls="profile"
                                    aria-selected="{{ request()->query('tab', 'profile') == 'profile' ? 'true' : 'false' }}">
                                    <i class="fas fa-info-circle me-1"></i> Profil & Ringkasan
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link rounded-top-3 {{ request()->query('tab') == 'services' ? 'active' : '' }}"
                                    id="services-tab"
                                    href="{{ route('partner.outlets.detail', ['outlet' => $outlet->id, 'tab' => 'services']) }}"
                                    role="tab" aria-controls="services"
                                    aria-selected="{{ request()->query('tab') == 'services' ? 'true' : 'false' }}">
                                    <i class="fas fa-hand-holding-usd me-1"></i> Layanan
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link rounded-top-3 {{ request()->query('tab') == 'edit-profile' ? 'active' : '' }}"
                                    id="edit-profile-tab"
                                    href="{{ route('partner.outlets.detail', ['outlet' => $outlet->id, 'tab' => 'edit-profile']) }}"
                                    role="tab" aria-controls="edit-profile"
                                    aria-selected="{{ request()->query('tab') == 'edit-profile' ? 'true' : 'false' }}">
                                    <i class="fas fa-edit me-1"></i> Edit Profil
                                </a>
                            </li>
                        </ul>

                        {{-- Tab Content based on query parameter --}}
                        @if (request()->query('tab', 'profile') == 'profile')
                            {{-- Profile & Summary Tab Content --}}
                            <div class="row g-4">
                                {{-- Detail Outlet & Owner --}}
                                <div class="col-lg-6">
                                    <div class="card card-body h-100 shadow-sm border-0 rounded-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-store me-2"></i>Informasi Outlet
                                        </h5>
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-3">
                                                <small class="text-muted d-block">Nama Outlet</small>
                                                <p class="fs-6 fw-semibold mb-0 text-dark">{{ $outlet->outlet_name }}</p>
                                            </li>
                                            <li class="mb-3">
                                                <small class="text-muted d-block">Alamat Lengkap</small>
                                                <p class="fs-6 fw-semibold mb-0 text-dark">{{ $outlet->address ?? '-' }}</p>
                                            </li>
                                            <li class="mb-3">
                                                <small class="text-muted d-block">Nama Kota</small>
                                                <p class="fs-6 fw-semibold mb-0 text-dark">{{ $outlet->city_name ?? '-' }}</p>
                                            </li>
                                            <li class="mb-3">
                                                <small class="text-muted d-block">Nomor Telepon</small>
                                                <p class="fs-6 fw-semibold mb-0 text-dark">{{ $outlet->phone_number ?? '-' }}</p>
                                            </li>
                                            <li class="mb-3">
                                                <small class="text-muted d-block">Zona Waktu</small>
                                                <p class="fs-6 fw-semibold mb-0 text-dark">{{ $outlet->timezone }}</p>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                {{-- Brand Owner Info --}}
                                <div class="col-lg-12">
                                    <div class="card card-body shadow-sm border-0 rounded-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-building me-2"></i>Informasi Pemilik Brand</h5>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <p class="mb-1 text-muted small">Nama Brand</p>
                                                <p class="fs-6 fw-semibold text-dark">{{ $outlet->owner->brand_name ?? '-' }}</p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-1 text-muted small">Email Brand</p>
                                                <p class="fs-6 fw-semibold text-dark">{{ $outlet->owner->brand_email ?? '-' }}</p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-1 text-muted small">Nomor Telepon Brand</p>
                                                <p class="fs-6 fw-semibold text-dark">{{ $outlet->owner->brand_phone ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Summary Statistics --}}
                                <div class="col-lg-12">
                                    <div class="card card-body shadow-sm border-0 rounded-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-chart-bar me-2"></i>Ringkasan Statistik</h5>
                                        <div class="row text-center g-3">
                                            <div class="col-md-4">
                                                <div class="p-3 bg-light rounded-3 h-100 border shadow-sm">
                                                    <i class="fas fa-desktop fa-2x text-primary mb-2"></i>
                                                    <h3 class="fw-bold mb-0">{{ $feature->devices->count() ?? 0 }}</h3>
                                                    <p class="text-muted mb-0">Mesin Terhubung</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="p-3 bg-light rounded-3 h-100 border shadow-sm">
                                                    <i class="fas fa-users-cog fa-2x text-info mb-2"></i>
                                                    <h3 class="fw-bold mb-0">{{ $feature->cashiers->count() ?? 0 }}</h3>
                                                    <p class="text-muted mb-0">Akun Kasir</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="p-3 bg-light rounded-3 h-100 border shadow-sm">
                                                    <i class="fas fa-tags fa-2x text-warning mb-2"></i>
                                                    <h3 class="fw-bold mb-0">{{ $feature->services->count() ?? 0 }}</h3>
                                                    <p class="text-muted mb-0">Jumlah Layanan</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif (request()->query('tab') == 'services')
                            @include('partner.outlets._service_detail')
                        @elseif (request()->query('tab') == 'edit-profile')
                            @include('partner.outlets._profile_detail')
                        @endif

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
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }

        /* Override Bootstrap's nav-link to match the original style without custom classes */
        .nav-tabs .nav-link {
            border: 1px solid #dee2e6;
            border-bottom: none;
            background-color: #f8f9fa;
            color: #495057;
            padding: 12px 20px;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }

        .nav-tabs .nav-link:hover {
            border-color: #ced4da;
        }

        .nav-tabs .nav-link.active {
            color: #ffffff;
            background-color: #0d6efd;
            border-color: #0d6efd;
            font-weight: 600;
        }
    </style>
@endpush

@push('scripts')
    {{-- SweetAlert script --}}
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
@endpush
