@extends('layouts.dashboard.app')
@php
    $feature = getData();
@endphp

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h2 class="mb-0 text-dark fw-bold">Detail Outlet</h2>
            <a href="{{ route('partner.outlets.list') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar Outlet
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card overflow-hidden mb-4">
                    <div class="card-header bg-gradient-primary text-white p-4 p-md-5 rounded-top-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-store-alt fa-3x me-4 text-white"></i>
                            <div>
                                <h1 class="mb-1 text-white fw-bold">{{ $outlet->outlet_name }}</h1>
                                <p class="mb-0 text-white-75 fs-5">Kode: #{{ $outlet->code }}</p>
                            </div>
                        </div>
                        @php
                            $statusText = $outlet->status ? 'Aktif' : 'Tidak Aktif';
                            $statusClass = $outlet->status ? 'bg-success' : 'bg-danger';
                        @endphp
                        <span class="badge {{ $statusClass }} rounded-pill px-3 py-2 fs-6 mt-3">
                            <i class="fas fa-circle me-2" style="font-size: 0.7em;"></i>{{ $statusText }}
                        </span>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-2"><i class="fas fa-user me-2"></i>Informasi Login</h6>
                                <p class="mb-1 text-muted small">Name</p>
                                <p class="fs-5 fw-semibold text-dark">{{ $outlet->outlet_name ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-2"><i class="fas fa-clock me-2"></i>Zona Waktu</h6>
                                <p class="mb-1 text-muted small">Pengaturan Zona Waktu</p>
                                <p class="fs-5 fw-semibold text-dark">{{ $outlet->timezone }}</p>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="text-secondary mb-3"><i class="fas fa-info-circle me-2"></i>Detail Kontak & Lokasi</h5>
                        <div class="row g-4 mb-4">
                            <div class="col-md-12">
                                <p class="mb-1 text-muted small">Alamat Lengkap</p>
                                <p class="fs-5 fw-semibold text-dark">{{ $outlet->address ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 text-muted small">Nomor Telepon Outlet</p>
                                <p class="fs-5 fw-semibold text-dark">{{ $outlet->phone_number ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 text-muted small">Jumlah Mesin Terhubung</p>
                                <p class="fs-5 fw-semibold text-dark">{{ $outlet->devices_count ?? 0 }} Unit</p>
                            </div>
                        </div>

                        @if ($outlet->owner)
                            <hr class="my-4">
                            <h5 class="text-secondary mb-3"><i class="fas fa-building me-2"></i>Informasi Pemilik Brand</h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <p class="mb-1 text-muted small">Nama Brand</p>
                                    <p class="fs-5 fw-semibold text-dark">{{ $outlet->owner->brand_name ?? '-' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1 text-muted small">Email Brand</p>
                                    <p class="fs-5 fw-semibold text-dark">{{ $outlet->owner->brand_email ?? '-' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1 text-muted small">Nomor Telepon Brand</p>
                                    <p class="fs-5 fw-semibold text-dark">{{ $outlet->owner->brand_phone ?? '-' }}</p>
                                </div>
                            </div>
                        @endif

                        <hr class="my-4">
                        <h5 class="text-secondary mb-4"><i class="fas fa-hand-holding-usd me-2"></i>Daftar Layanan</h5>

                        <form action="{{ route('partner.outlets.services.update', $outlet->id) }}" method="POST"
                            id="servicesForm">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="outlet_id" value="{{ $outlet->id }}">

                            <div id="serviceRowsContainer">

                                @forelse($outlet->services as $service)
                                    @php
                                        // Pastikan $service->service_type_id adalah array, jika tidak, buat array kosong
                                        $selectedServiceTypes = is_array($service->service_type_id)
                                            ? $service->service_type_id
                                            : [$service->service_type_id];
                                        $hasServiceType =
                                            !empty($selectedServiceTypes) &&
                                            !in_array(null, $selectedServiceTypes, true);
                                    @endphp
                                    <div class="row g-3 mb-3 align-items-center service-row existing-service"
                                        data-service-id="{{ $service->id }}">
                                        <div class="col-md-4">
                                            <label for="service_name_{{ $service->id }}"
                                                class="form-label visually-hidden">Nama Layanan</label>
                                            <input type="text" class="form-control form-control-lg rounded-pill"
                                                id="service_name_{{ $service->id }}"
                                                name="services[{{ $service->id }}][name]" value="{{ $service->name }}"
                                                placeholder="Nama Layanan" required>
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
                                                    {{-- Periksa apakah service ini punya service types yang terhubung --}}
                                                    {{ $service->serviceTypes->isNotEmpty() ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="toggle_type_{{ $service->id }}">Dengan Tipe Layanan</label>
                                            </div>
                                            <div class="service-type-checkboxes mt-2" {{-- Tampilkan/sembunyikan berdasarkan apakah ada service types yang terhubung --}}
                                                {{ $service->serviceTypes->isNotEmpty() ? '' : 'style="display:none;"' }}>
                                                <p class="text-muted small mb-1">Pilih Tipe:</p>
                                                @foreach ($serviceTypes as $type)
                                                    <div class="form-check">
                                                        <input class="form-check-input service-type-checkbox"
                                                            type="checkbox"
                                                            name="services[{{ $service->id }}][service_type_ids][]"
                                                            id="service_{{ $service->id }}_type_{{ $type['id'] }}"
                                                            value="{{ $type['id'] }}" {{-- Ini adalah bagian yang diperbaiki: --}}
                                                            {{ $service->serviceTypes->contains('id', $type['id']) ? 'checked' : '' }}
                                                            {{-- Tambahkan disabled jika toggle_type_{{ $service->id }} tidak checked (sesuai JS Anda) --}}>
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
                                                class="btn btn-outline-danger rounded-pill btn-sm remove-service-row"
                                                data-service-id="{{ $service->id }}" data-type="existing">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            {{-- Hidden field to mark for deletion --}}
                                            <input type="hidden" name="services[{{ $service->id }}][_delete]"
                                                value="0" class="delete-flag">
                                            {{-- Hidden input to store service_type_id when toggle is off (Tidak lagi diperlukan seperti sebelumnya) --}}
                                            {{-- <input type="hidden"
                        name="services[{{ $service->id }}][service_type_id_hidden]"
                        value="{{ $service->service_type_id }}" class="service-type-id-hidden"> --}}
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
                                                    <input class="form-check-input service-type-checkbox" type="checkbox"
                                                        name="new_services[KEY][service_type_ids][]" {{-- Ubah di sini: tambahkan [] --}}
                                                        id="new_service_KEY_type_{{ $type['id'] }}"
                                                        value="{{ $type['id'] }}" disabled> {{-- Disabled by default for new rows --}}
                                                    <label class="form-check-label"
                                                        for="new_service_KEY_type_{{ $type['id'] }}">
                                                        {{ $type['name'] }}
                                                    </label>
                                                </div>
                                            @endforeach
                                            {{-- Ini juga tidak lagi diperlukan --}}
                                            {{-- <div class="form-check">
                        <input class="form-check-input service-type-checkbox" type="checkbox"
                            name="new_services[KEY][service_type_ids][]"
                            id="new_service_KEY_type_none" value="" disabled>
                        <label class="form-check-label text-danger"
                            for="new_service_KEY_type_none">
                            Tidak Ada Tipe
                        </label>
                    </div> --}}
                                        </div>
                                    </div>
                                    <div class="col-md-1 text-end">
                                        <button type="button"
                                            class="btn btn-outline-danger rounded-pill btn-sm remove-service-row"
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
                        {{-- END SECTION: Daftar Layanan --}}

                        @if ($feature->can('partner.outlets.destroy'))
                            <div class="mt-5 pt-4 border-top d-flex justify-content-end flex-wrap gap-3">
                                <a href="" class="btn btn-primary rounded-pill px-4 py-2">
                                    <i class="fas fa-edit me-2"></i> Edit Detail Outlet
                                </a>
                                <form action="" method="POST" class="d-inline">
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

        h5.text-secondary {
            color: #6c757d !important;
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
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
            /* Changed from .service-type-checkboxs */
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

        @media (max-width: 767.98px) {
            .card-header {
                padding: 2.5rem 1.5rem !important;
            }

            .card-title {
                font-size: 1.75rem;
            }

            .card-body {
                padding: 2rem 1.5rem !important;
            }

            .d-flex.justify-content-between.align-items-center {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .d-flex.justify-content-between.align-items-center h2 {
                margin-bottom: 1rem !important;
            }

            .btn.rounded-pill {
                width: 100%;
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
        }
    </style>
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
