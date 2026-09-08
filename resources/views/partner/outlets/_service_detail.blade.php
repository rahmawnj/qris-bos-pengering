<h5 class="text-secondary mb-4"><i class="fas fa-hand-holding-usd me-2"></i>Daftar Layanan
</h5>

<form action="{{ route('partner.outlets.services.update', $outlet->id) }}" method="POST" id="servicesForm">
    @csrf
    @method('PATCH')
    <input type="hidden" name="outlet_id" value="{{ $outlet->id }}">

    <div id="serviceRowsContainer">
        @forelse($outlet->services as $service)
            <div class="row g-3 mb-3 align-items-center service-row existing-service"
                data-service-id="{{ $service->id }}">
                <div class="col-md-4">
                    <label for="service_name_{{ $service->id }}" class="form-label visually-hidden">Nama
                        Layanan</label>
                    <input type="text" class="form-control form-control-lg rounded-pill"
                        id="service_name_{{ $service->id }}" name="services[{{ $service->id }}][name]"
                        value="{{ $service->name }}" placeholder="Nama Layanan" required>
                </div>
                {{-- Hanya Kolom Price (Non-Member Price Dihapus) --}}
                <div class="col-md-3">
                    <label for="service_price_{{ $service->id }}" class="form-label visually-hidden">Harga</label>
                    <div class="input-group input-group-lg rounded-pill-group">
                        <span class="input-group-text rounded-start-pill">Rp</span>
                        <input type="number" class="form-control rounded-end-pill"
                            id="service_price_{{ $service->id }}"
                            name="services[{{ $service->id }}][price]"
                            value="{{ number_format($service->price, 0, '.', '') }}"
                            placeholder="Harga Layanan" step="1" min="0" required>
                    </div>
                </div>
                {{-- Kolom Unit Dipertahankan --}}
                <div class="col-md-1">
                    <label for="service_unit_{{ $service->id }}" class="form-label visually-hidden">Unit</label>
                    <select class="form-select form-select-lg rounded-pill" name="services[{{ $service->id }}][unit]"
                        id="service_unit_{{ $service->id }}" required>
                        <option value="kg" {{ $service->unit == 'kg' ? 'selected' : '' }}>kg</option>
                        <option value="pcs" {{ $service->unit == 'pcs' ? 'selected' : '' }}>pcs</option>
                        <option value="liter" {{ $service->unit == 'liter' ? 'selected' : '' }}>liter</option>
                        <option value="hour" {{ $service->unit == 'hour' ? 'selected' : '' }}>hour</option>
                        <option value="unit" {{ $service->unit == 'unit' ? 'selected' : '' }}>unit</option>
                    </select>
                </div>
                {{-- Kolom Tipe Layanan Dipertahankan --}}
                <div class="col-md-3">
                    <div class="form-check form-switch mt-2 mb-2">
                        <input class="form-check-input service-type-toggle" type="checkbox" role="switch"
                            id="toggle_type_{{ $service->id }}"
                            {{ $service->serviceTypes->isNotEmpty() ? 'checked' : '' }}>
                        <label class="form-check-label" for="toggle_type_{{ $service->id }}">Dengan Tipe
                            Layanan</label>
                    </div>
                    <div class="service-type-checkboxes mt-2"
                        {{ $service->serviceTypes->isNotEmpty() ? '' : 'style="display:none;"' }}>
                        <p class="text-muted small mb-1">Pilih Tipe:</p>
                        @foreach ($serviceTypes as $type)
                            <div class="form-check">
                                <input class="form-check-input service-type-checkbox" type="checkbox"
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
                    <button type="button" class="btn btn-outline-danger rounded-circle btn-sm remove-service-row"
                        data-service-id="{{ $service->id }}" data-type="existing"
                        {{ !$feature->can('partner.outlets.services.update') ? 'disabled' : '' }}>
                        <i class="fas fa-trash"></i>
                    </button>
                    <input type="hidden" name="services[{{ $service->id }}][_delete]" value="0"
                        class="delete-flag">
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
                <input type="text" class="form-control form-control-lg rounded-pill" name="new_services[KEY][name]"
                    placeholder="Nama Layanan" required
                    {{ !$feature->can('partner.outlets.services.update') ? 'disabled' : '' }}>
            </div>
            {{-- Hanya Kolom Price di Template --}}
            <div class="col-md-3">
                <label for="new_service_price" class="form-label visually-hidden">Harga</label>
                <div class="input-group input-group-lg rounded-pill-group">
                    <span class="input-group-text rounded-start-pill">Rp</span>
                    <input type="number" class="form-control rounded-end-pill" name="new_services[KEY][price]"
                        placeholder="Harga Layanan" step="1" min="0" required
                        {{ !$feature->can('partner.outlets.services.update') ? 'disabled' : '' }}>
                </div>
            </div>
            {{-- Kolom Unit di Template --}}
            <div class="col-md-1">
                <label for="new_service_unit_KEY" class="form-label visually-hidden">Unit</label>
                <select class="form-select form-select-lg rounded-pill" name="new_services[KEY][unit]"
                    id="new_service_unit_KEY" required
                    {{ !$feature->can('partner.outlets.services.update') ? 'disabled' : '' }}>
                    <option value="kg">kg</option>
                    <option value="pcs">pcs</option>
                    <option value="liter">liter</option>
                    <option value="hour">hour</option>
                    <option value="unit">unit</option>
                </select>
            </div>
            {{-- Kolom Tipe Layanan di Template --}}
            <div class="col-md-3">
                <div class="form-check form-switch mt-2 mb-2">
                    <input class="form-check-input service-type-toggle" type="checkbox" role="switch"
                        id="new_toggle_type_KEY"
                        {{ !$feature->can('partner.outlets.services.update') ? 'disabled' : '' }}>
                    <label class="form-check-label" for="new_toggle_type_KEY">Dengan Tipe
                        Layanan</label>
                </div>
                <div class="service-type-checkboxes mt-2" style="display:none;">
                    <p class="text-muted small mb-1">Pilih Tipe:</p>
                    @foreach ($serviceTypes as $type)
                        <div class="form-check">
                            <input class="form-check-input service-type-checkbox" type="checkbox"
                                name="new_services[KEY][service_type_ids][]"
                                id="new_service_KEY_type_{{ $type['id'] }}" value="{{ $type['id'] }}" disabled
                                {{ !$feature->can('partner.outlets.services.update') ? 'disabled' : '' }}>
                            <label class="form-check-label" for="new_service_KEY_type_{{ $type['id'] }}">
                                {{ $type['name'] }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-outline-danger rounded-circle btn-sm remove-service-row"
                    data-type="new" {{ !$feature->can('partner.outlets.services.update') ? 'disabled' : '' }}>
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </template>

    <div class="d-grid gap-2">
        <button type="button" class="btn btn-outline-primary rounded-pill py-2 mt-3" id="addServiceRow"
            {{ !$feature->can('partner.outlets.services.update') ? 'disabled' : '' }}>
            <i class="fas fa-plus-circle me-2"></i> Tambah Layanan Baru
        </button>
        <button type="submit" class="btn btn-success rounded-pill py-2 mt-2"
            {{ !$feature->can('partner.outlets.services.update') ? 'disabled' : '' }}>
            <i class="fas fa-save me-2"></i> Simpan Perubahan Layanan
        </button>
    </div>
</form>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // (Optional: Logic for outlet status form - keeping it here if needed)
            const outletStatusSelect = document.getElementById('outlet_status_select');
            const outletStatusForm = document.getElementById('outletStatusForm');
            if (outletStatusSelect && outletStatusForm) {
                outletStatusSelect.addEventListener('change', function() {
                    outletStatusForm.submit();
                });
            }

            const serviceRowsContainer = document.getElementById('serviceRowsContainer');
            const addServiceRowButton = document.getElementById('addServiceRow');
            const newServiceRowTemplate = document.getElementById('newServiceRowTemplate');
            const noServicesMessage = document.getElementById('noServicesMessage');

            let newRowIndex = 0;

            /**
             * Handles enabling/disabling service type checkboxes based on the toggle switch.
             * @param {HTMLElement} toggleInput - The checkbox toggle element.
             */
            function toggleServiceTypeCheckboxes(toggleInput) {
                const serviceRow = toggleInput.closest('.service-row');
                const checkboxesContainer = serviceRow.querySelector('.service-type-checkboxes');
                const checkboxes = checkboxesContainer.querySelectorAll('.service-type-checkbox');

                if (toggleInput.checked) {
                    checkboxesContainer.style.display = 'block';
                    // Re-enable checkboxes unless the entire row is marked for deletion (not handled here)
                    const isRowDeleted = serviceRow.classList.contains('deleted');
                    if (!isRowDeleted) {
                        checkboxes.forEach(cb => cb.removeAttribute('disabled'));
                    }
                } else {
                    checkboxesContainer.style.display = 'none';
                    checkboxes.forEach(cb => {
                        cb.setAttribute('disabled', 'disabled');
                        cb.checked = false; // Uncheck all when disabled
                    });
                }
            }

            /**
             * Handles adding a new service row to the UI.
             */
            addServiceRowButton.addEventListener('click', function() {
                // Remove "Belum ada layanan" message if it exists
                if (noServicesMessage && serviceRowsContainer.contains(noServicesMessage)) {
                    noServicesMessage.remove();
                }

                const templateContent = newServiceRowTemplate.content.cloneNode(true);
                const newRow = templateContent.querySelector('.service-row');

                // Update names and IDs to be unique for new rows
                newRow.querySelectorAll('[name*="KEY"]').forEach(input => {
                    input.name = input.name.replace('KEY', newRowIndex);
                });
                newRow.querySelectorAll('[id*="KEY"]').forEach(input => {
                    input.id = input.id.replace('KEY', newRowIndex);
                });
                newRow.querySelectorAll('[for*="KEY"]').forEach(label => {
                    label.htmlFor = label.htmlFor.replace('KEY', newRowIndex);
                });

                // Specifically handle checkboxes to ensure array naming
                const checkboxes = newRow.querySelectorAll('.service-type-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.name = `new_services[${newRowIndex}][service_type_ids][]`;
                });

                // Set the data-type for newly added rows
                newRow.querySelector('.remove-service-row').dataset.type = 'new';


                serviceRowsContainer.appendChild(newRow);
                newRowIndex++; // Increment for the next new row

                // Attach event listeners for elements within the new row
                const newToggle = newRow.querySelector('.service-type-toggle');
                newToggle.addEventListener('change', function() {
                    toggleServiceTypeCheckboxes(this);
                });

                const newRemoveButton = newRow.querySelector('.remove-service-row');
                newRemoveButton.addEventListener('click', handleRemoveServiceRow);

                // Ensure initial state for new row's checkboxes based on its toggle (should be disabled by default if template has toggle off)
                toggleServiceTypeCheckboxes(newToggle);
            });


            /**
             * Handles removing or marking for deletion a service row.
             * @param {Event} event - The click event from the remove button.
             */
            function handleRemoveServiceRow(event) {
                const button = event.currentTarget;
                const serviceRow = button.closest('.service-row');
                const serviceType = button.dataset.type; // 'existing' or 'new'

                if (serviceType === 'existing') {
                    const deleteFlag = serviceRow.querySelector('.delete-flag');
                    if (!deleteFlag) {
                        console.error('Delete flag not found for existing service row!');
                        return;
                    }

                    if (serviceRow.classList.contains('deleted')) {
                        // Restore: remove 'deleted' class and reset flag
                        serviceRow.classList.remove('deleted');
                        deleteFlag.value = '0';

                        // Re-enable inputs and toggles
                        serviceRow.querySelectorAll('input:not(.service-type-checkbox):not(.delete-flag), select')
                            .forEach(input => input.removeAttribute('disabled'));

                        // Restore correct state of checkboxes based on toggle
                        toggleServiceTypeCheckboxes(serviceRow.querySelector('.service-type-toggle'));

                        // Change button appearance back to delete
                        button.classList.remove('btn-danger');
                        button.classList.add('btn-outline-danger');
                        button.innerHTML = '<i class="fas fa-trash"></i>';
                    } else {
                        // Mark for deletion: add 'deleted' class and set flag
                        if (confirm(
                                'Apakah Anda yakin ingin menghapus layanan ini? Ini akan ditandai untuk dihapus saat Anda menyimpan perubahan.'
                            )) {
                            serviceRow.classList.add('deleted');
                            deleteFlag.value = '1';
                            // Disable all inputs in the row except the delete flag
                            serviceRow.querySelectorAll('input:not(.delete-flag), select')
                                .forEach(input => input.setAttribute('disabled', 'disabled'));
                            // Hide checkboxes container for deleted rows
                            serviceRow.querySelector('.service-type-checkboxes').style.display = 'none';

                            // Change button appearance to undo
                            button.classList.remove('btn-outline-danger');
                            button.classList.add('btn-danger');
                            button.innerHTML = '<i class="fas fa-undo"></i>';
                        }
                    }
                } else if (serviceType === 'new') {
                    // For newly added rows, just remove from UI
                    serviceRow.remove();
                }

                // Update "No services" message visibility after any removal/restoration
                updateNoServicesMessageVisibility();
            }

            /**
             * Checks and updates the visibility of the "no services" message.
             */
            function updateNoServicesMessageVisibility() {
                const visibleServiceRows = serviceRowsContainer.querySelectorAll('.service-row:not(.deleted)');
                if (visibleServiceRows.length === 0) {
                    if (!document.getElementById('noServicesMessage')) {
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-info text-center';
                        alertDiv.role = 'alert';
                        alertDiv.id = 'noServicesMessage';
                        alertDiv.textContent = 'Belum ada layanan yang terdaftar untuk outlet ini.';
                        serviceRowsContainer.appendChild(alertDiv);
                    }
                } else {
                    if (document.getElementById('noServicesMessage')) {
                        document.getElementById('noServicesMessage').remove();
                    }
                }
            }


            // --- Initial Setup on Page Load ---

            // Attach event listeners to all existing service rows (if any)
            document.querySelectorAll('.service-row').forEach(row => {
                // Attach toggle listener
                const toggle = row.querySelector('.service-type-toggle');
                if (toggle) {
                    toggle.addEventListener('change', function() {
                        toggleServiceTypeCheckboxes(this);
                    });
                    // Set initial state of checkboxes based on toggle state
                    toggleServiceTypeCheckboxes(toggle);
                }

                // Attach remove button listener
                const removeButton = row.querySelector('.remove-service-row');
                if (removeButton) {
                    removeButton.addEventListener('click', handleRemoveServiceRow);
                }
            });

            // Initial check for "no services" message visibility
            updateNoServicesMessageVisibility();
        });
    </script>
@endpush
