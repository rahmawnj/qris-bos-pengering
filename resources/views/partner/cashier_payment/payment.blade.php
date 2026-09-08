@props([
    'items' => ['Partner', 'Kasir', 'Pembayaran Kasir'],
    'title' => 'Pembayaran Kasir',
    'subtitle' => 'Proses pembayaran langsung oleh kasir',
])

@extends('layouts.dashboard.app')

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- Error & Success Alerts --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle fa-lg me-3"></i>
                        <div>
                            <strong>Ups! Ada kesalahan:</strong>
                            <ul class="mb-0 mt-1 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle fa-lg me-3"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                    @if (session('new_transaction'))
                        <div class="mt-2">
                            <x-print-invoice :transaction="session('new_transaction')" />
                        </div>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('partner.cashier.payment.store') }}" method="POST" id="paymentForm">
                @csrf
                <div class="row g-4">
                    {{-- Left Column: Selection Area --}}
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 p-4">
                            {{-- Selection Header --}}
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary text-white rounded-3 p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-shopping-basket fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0">Pilih Layanan & Produk</h5>
                                    <p class="text-muted small mb-0">Lengkapi pesanan Anda dengan memilih layanan dan produk yang tersedia.</p>
                                </div>
                            </div>

                            {{-- Kasir & Outlet Info Boxes --}}
                            <div class="row g-3 mb-5">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px;">Kasir Bertugas</label>
                                    <div class="info-box-custom d-flex align-items-center p-3 rounded-3 bg-light border">
                                        <i class="fas fa-user-circle text-primary me-3 fs-5"></i>
                                        <div class="fw-bold text-dark">{{ Auth::user()->name }}</div>
                                    </div>
                                    <input type="hidden" name="cashier_name" value="{{ Auth::user()->name }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="outletSelect" class="form-label text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px;">Lokasi Outlet <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <select class="form-select border rounded-3 p-3 fw-bold" id="outletSelect" name="outlet_id" required style="padding-left: 45px !important;">
                                            <option value="">-- Pilih Outlet --</option>
                                            @foreach ($outlets as $outlet)
                                                <option value="{{ $outlet->id }}" {{ old('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                                    {{ $outlet->outlet_name }} ({{ $outlet->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-location-dot text-primary position-absolute" style="left: 18px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- Service Selection --}}
                            <div class="mb-5">
                                <label class="form-label text-dark fw-bold text-uppercase mb-3" style="font-size: 13px; letter-spacing: 0.5px;">Layanan Tersedia <span class="text-danger">*</span></label>
                                <div id="serviceRadios" class="row g-3">
                                    <div class="col-12">
                                        <div class="placeholder-box p-5 border-dashed">
                                            <i class="fas fa-store fa-2x text-primary-subtle mb-3"></i>
                                            <h6 class="text-muted mb-0">Silakan pilih outlet dahulu</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Addon Selection --}}
                            <div class="mb-2">
                                <label class="form-label text-dark fw-bold text-uppercase mb-3" style="font-size: 13px; letter-spacing: 0.5px;">Tambahan (Add-on) <span class="text-muted small ms-1">(Opsional)</span></label>
                                <div id="addonCheckboxes" class="row g-3">
                                    <div class="col-12">
                                        <div class="placeholder-box p-5 border-dashed">
                                            <i class="fas fa-puzzle-piece fa-2x text-warning-subtle mb-3"></i>
                                            <h6 class="text-muted mb-0">Pilih outlet untuk melihat add-on</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Order Summary Sidebar --}}
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 position-sticky overflow-hidden" style="top: 1rem;">
                            <div class="card-header bg-primary text-white p-4 border-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-receipt me-3 fs-4"></i>
                                    <div>
                                        <h5 class="mb-0 fw-bold">Detail Pesanan</h5>
                                        <p class="mb-0 small opacity-75">Ringkasan transaksi pelanggan</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-4">
                                {{-- Customer Info Section --}}
                                <div class="mb-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-2">Data Pelanggan</label>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                                        <input type="text" class="form-control border-start-0" id="customer_name" name="customer_name"
                                            value="{{ old('customer_name') }}" placeholder="Nama Pelanggan">
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone-alt text-muted"></i></span>
                                        <input type="text" class="form-control border-start-0" id="customer_phone_number"
                                            name="customer_phone_number" value="{{ old('customer_phone_number') }}"
                                            placeholder="No. WhatsApp / HP (08xx)">
                                    </div>
                                </div>

                                {{-- Device & Time Section --}}
                                <div class="mb-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-2">Perangkat & Waktu</label>
                                    <div id="deviceAssignmentFields" class="row g-2 mb-3"></div>
                                    <div id="deviceAssignmentPlaceholder" class="form-text text-muted mb-3 p-2 bg-light rounded text-center small border">
                                        Pilih layanan terlebih dahulu untuk melihat perangkat.
                                    </div>
                                    
                                    <div class="position-relative">
                                        <input type="datetime-local" class="form-control" id="estimated_completion_at"
                                            name="estimated_completion_at" value="{{ old('estimated_completion_at') }}" required>
                                        <div class="form-text small mt-1">Estimasi selesai pengerjaan</div>
                                    </div>
                                </div>

                                {{-- Order Summary Box --}}
                                <div class="bg-light rounded-3 p-4 mb-4 border-dashed" style="min-height: 120px;">
                                    <div id="orderSummary" class="text-center text-muted">
                                        <i class="far fa-clock fa-2x mb-2 opacity-50"></i>
                                        <p class="small mb-0">Tidak ada layanan terpilih.</p>
                                    </div>

                                    {{-- Qty Input (Shown only when service selected) --}}
                                    <div id="quantityInputContainer" style="display:none;" class="mt-4 pt-3 border-top">
                                        <label for="service_quantity" class="form-label small fw-bold mb-2">Jumlah (<span id="serviceUnitDisplay">Unit</span>)</label>
                                        <input type="number" class="form-control fw-bold text-center" id="service_quantity"
                                            name="quantity" min="1" step="any" value="{{ old('quantity', 1) }}" required>
                                    </div>
                                </div>

                                {{-- Total Amount --}}
                                <div class="d-flex justify-content-between align-items-center mb-4 px-1">
                                    <span class="text-muted fw-bold text-uppercase small">Total Bayar</span>
                                    <span class="fs-2 fw-bold text-primary" id="displayAmount">Rp 0</span>
                                </div>

                                <input type="hidden" id="hiddenAmount" name="amount">
                                <input type="hidden" id="selectedServiceId" name="service_id">
                                <input type="hidden" id="selectedAddonIds" name="addon_ids">

                                {{-- Payment Method Selector --}}
                                <div class="mb-4">
                                    <label class="form-label text-danger small fw-bold text-uppercase mb-2">Metode Pembayaran *</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="radio" class="btn-check" name="payment_method" id="paymentMethodCash" value="cash" {{ old('payment_method', 'cash') == 'cash' ? 'checked' : '' }} required>
                                            <label class="btn btn-outline-primary w-100 py-3 rounded-3 d-flex flex-column align-items-center justify-content-center h-100" for="paymentMethodCash">
                                                <i class="fas fa-money-bill-wave mb-2 fs-4"></i>
                                                <span class="fw-bold">Tunai</span>
                                            </label>
                                        </div>
                                        <div class="col-6">
                                            <input type="radio" class="btn-check" name="payment_method" id="paymentMethodNonCash" value="non_cash" {{ old('payment_method') == 'non_cash' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary w-100 py-3 rounded-3 d-flex flex-column align-items-center justify-content-center h-100" for="paymentMethodNonCash">
                                                <i class="fas fa-qrcode mb-2 fs-4"></i>
                                                <span class="fw-bold">QRIS/TF</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <textarea class="form-control bg-light border" id="notes" name="notes" placeholder="Catatan transaksi (opsional)" rows="2">{{ old('notes') }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 shadow-sm fw-bold">
                                    <i class="fas fa-lock me-2"></i> Proses Transaksi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <link href="{{ asset('assets/plugins/select-picker/dist/picker.min.css') }}" rel="stylesheet" />
    <style>
        :root {
            --primary-blue: #0066ff;
            --bg-light: #f8fafc;
        }

        body { background-color: #f1f5f9; }
        .rounded-4 { border-radius: 1.25rem !important; }
        .border-dashed { border: 2px dashed #e2e8f0 !important; }
        .placeholder-box { border-radius: 12px; }

        /* Card Selection Styling (No Icon per user request) */
        .selection-card-custom {
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1.5px solid #edf2f7;
            background-color: #fff;
            position: relative;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
        }

        .selection-card-custom:hover {
            border-color: var(--primary-blue);
            background-color: #f0f7ff;
            transform: translateY(-2px);
        }

        .selection-card-custom.active {
            border-color: var(--primary-blue);
            background-color: #f0f7ff;
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.08);
        }

        .custom-radio-circle {
            width: 22px;
            height: 22px;
            border: 2px solid #cbd5e0;
            border-radius: 50%;
            margin-right: 15px;
            flex-shrink: 0;
            position: relative;
            transition: all 0.2s;
        }

        .selection-card-custom.active .custom-radio-circle {
            border-color: var(--primary-blue);
        }

        .selection-card-custom.active .custom-radio-circle::after {
            content: '';
            position: absolute;
            width: 12px;
            height: 12px;
            background: var(--primary-blue);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .custom-checkbox-square {
            width: 20px;
            height: 20px;
            border: 2px solid #cbd5e0;
            border-radius: 6px;
            margin-right: 15px;
            flex-shrink: 0;
            position: relative;
            transition: all 0.2s;
        }

        .selection-card-custom.active .custom-checkbox-square {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }

        .selection-card-custom.active .custom-checkbox-square::after {
            content: "\f00c";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            color: white;
            font-size: 10px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .selection-details { flex-grow: 1; }
        .selection-name { font-weight: 700; color: #2d3748; font-size: 15px; margin-bottom: 2px; }
        .selection-price { color: var(--primary-blue); font-weight: 700; font-size: 14px; }
        .selection-meta { font-size: 12px; color: #718096; margin-top: 2px; }

        .btn-check:checked + .btn-outline-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            color: white;
        }

        /* Input overrides */
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(0, 102, 255, 0.1);
        }

        .addon-qty-wrap .form-control {
            max-width: 80px;
            text-align: center;
            font-weight: 700;
        }

        /* Picker Styling Override */
        .picker { width: 100% !important; }
        .picker .pc-select {
            padding: 12px 15px !important;
            border-radius: 10px !important;
            border: 1px solid #dee2e6 !important;
            font-weight: 700 !important;
            background-color: #fff !important;
            font-size: 14px !important;
        }
        .picker .pc-element { 
            border-radius: 12px !important; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
            border: 1px solid #edf2f7 !important;
            margin-top: 5px !important;
        }
        .picker .pc-list li { padding: 10px 15px !important; font-size: 13px !important; }
        .picker .pc-list li:hover { background-color: #f0f7ff !important; color: var(--primary-blue) !important; }
        .picker .pc-list li.pc-selected { background-color: var(--primary-blue) !important; color: #fff !important; }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select-picker/dist/picker.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            const outletServicesData = @json($outletServicesData ?? new stdClass());
            const outletAddonsData = @json($outletAddonsData ?? new stdClass());
            const outletsFullData = @json($outlets->keyBy('id'));

            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(number);
            }

            function getServiceQuantity() {
                const quantity = parseFloat($("#service_quantity").val()) || 1;
                return Math.max(1, quantity);
            }

            function getSelectedServiceData() {
                const selectedOutletId = $("#outletSelect").val();
                const selectedServiceId = $("#selectedServiceId").val();
                if (!selectedOutletId || !selectedServiceId) return null;
                return outletServicesData[selectedOutletId]?.[selectedServiceId] || null;
            }

            function updateOrderSummary() {
                let summaryHtml = '';
                let totalAmount = 0;
                const quantity = getServiceQuantity();

                const selectedServiceRadio = $(".service-radio:checked");
                const $quantityContainer = $("#quantityInputContainer");

                if (selectedServiceRadio.length > 0) {
                    const serviceName = selectedServiceRadio.data('name');
                    const servicePrice = parseFloat(selectedServiceRadio.data('price'));
                    const serviceUnit = selectedServiceRadio.data('unit');
                    const serviceOptionsText = selectedServiceRadio.data('options-text');

                    $quantityContainer.show();
                    $("#serviceUnitDisplay").text(serviceUnit);

                    const serviceSubtotal = servicePrice * quantity;
                    totalAmount += serviceSubtotal;

                    summaryHtml += `
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold small">Layanan: ${serviceName}</span>
                            <span class="fw-bold small">${formatRupiah(serviceSubtotal)}</span>
                        </div>
                        <div class="text-xs text-muted mb-2 ps-2 border-start border-2">
                            ${formatRupiah(servicePrice)} / ${serviceUnit} x ${quantity} ${serviceUnit}
                            ${serviceOptionsText ? `<br>Opsi: ${serviceOptionsText}` : ''}
                        </div>
                    `;

                    const selectedDeviceText = $("#main_device_id option:selected").text();
                    const selectedDeviceId = $("#main_device_id").val();
                    if (selectedDeviceId) {
                        summaryHtml += `
                            <div class="text-xs text-primary mb-3">
                                <i class="fas fa-microchip me-1"></i> Perangkat: <strong>${selectedDeviceText}</strong>
                            </div>
                        `;
                    }
                } else {
                    $quantityContainer.hide();
                    summaryHtml = '<div class="text-center py-4 text-muted"><i class="far fa-clock fa-2x mb-2 opacity-50"></i><p class="small mb-0">Tidak ada layanan terpilih.</p></div>';
                }

                $('.addon-checkbox:checked').each(function() {
                    const addonName = $(this).data('name');
                    const addonPrice = parseFloat($(this).data('price'));
                    const addonId = $(this).val();
                    const addonQty = parseFloat($(`#addon_qty_${addonId}`).val()) || 1;
                    const subtotal = addonPrice * Math.max(1, addonQty);
                    
                    summaryHtml += `
                        <div class="d-flex justify-content-between align-items-center text-xs mt-2 pt-2 border-top border-light">
                            <span>${addonName} x ${addonQty}</span>
                            <span class="fw-bold">${formatRupiah(subtotal)}</span>
                        </div>
                    `;
                    totalAmount += subtotal;
                });

                $("#orderSummary").html(summaryHtml);
                $("#displayAmount").text(formatRupiah(totalAmount));
                $("#hiddenAmount").val(totalAmount);
                
                const selectedAddonIds = $('.addon-checkbox:checked').map(function() { return $(this).val(); }).get().join(',');
                $("#selectedAddonIds").val(selectedAddonIds);
            }

            function updateDeviceSelection() {
                const $deviceFields = $("#deviceAssignmentFields");
                const $devicePlaceholder = $("#deviceAssignmentPlaceholder");
                const selectedOutletId = $("#outletSelect").val();
                const outletData = outletsFullData[selectedOutletId];
                const serviceData = getSelectedServiceData();
                const serviceOptions = serviceData?.service_options || [];
                const devices = outletData?.devices || [];

                $deviceFields.empty();
                $devicePlaceholder.show();

                if (selectedOutletId && serviceOptions.length > 0 && devices.length > 0) {
                    $devicePlaceholder.hide();
                    let optionsHtml = '<option value="">-- Pilih Perangkat --</option>';
                    const oldDeviceId = "{{ old('device_id') }}";

                    devices.forEach((deviceData) => {
                        const isSelected = String(oldDeviceId) === String(deviceData.id) ? 'selected' : '';
                        optionsHtml += `<option value="${deviceData.id}" ${isSelected}>${deviceData.name} (${deviceData.code})</option>`;
                    });

                    $deviceFields.append(`
                        <div class="col-12">
                            <select class="form-select form-select-sm border rounded-3 fw-bold searchable-device-picker" id="main_device_id" name="device_id" required>
                                ${optionsHtml}
                            </select>
                        </div>
                    `);

                    // Initialize Searchable Picker
                    $('#main_device_id').picker({
                        search: true,
                        texts: {
                            placeholder: '-- Pilih Perangkat --',
                            search: 'Cari perangkat...',
                            noResult: 'Perangkat tidak ditemukan'
                        }
                    });

                    $("#main_device_id").on("change", updateOrderSummary);
                } else if (selectedOutletId && serviceOptions.length > 0) {
                    $devicePlaceholder.text('Tidak ada perangkat tersedia.');
                } else if (selectedOutletId && serviceData) {
                    $devicePlaceholder.text('Layanan ini tidak memerlukan IoT.');
                }
            }

            function updateAllSelections() {
                const selectedOutletId = $("#outletSelect").val();
                $("#serviceRadios, #addonCheckboxes").empty();
                $("#selectedServiceId").val('');
                $("#service_quantity").val(1);
                $("#quantityInputContainer").hide();
                updateOrderSummary();
                updateDeviceSelection();

                if (selectedOutletId) {
                    // Render Services
                    const services = outletServicesData[selectedOutletId] || {};
                    if (Object.keys(services).length > 0) {
                        $.each(services, function(id, data) {
                            let opts = data.service_options?.map(o => o.name).join(', ') || 'Tidak ada opsi layanan.';
                            $("#serviceRadios").append(`
                                <div class="col-md-6">
                                    <label class="selection-card-custom" for="service_${id}">
                                        <input class="d-none service-radio" type="radio" name="temp_service_id" id="service_${id}" value="${id}" 
                                            data-price="${data.price}" data-name="${data.name}" data-unit="${data.unit || 'Unit'}" data-options-text="${opts}">
                                        <div class="custom-radio-circle"></div>
                                        <div class="selection-details">
                                            <div class="selection-name">${data.name}</div>
                                            <div class="selection-price">${formatRupiah(data.price)} / ${data.unit || 'Unit'}</div>
                                            <div class="selection-meta">${opts}</div>
                                        </div>
                                    </label>
                                </div>
                            `);
                        });
                    }

                    // Render Addons
                    const addons = outletAddonsData[selectedOutletId] || {};
                    if (Object.keys(addons).length > 0) {
                        $.each(addons, function(id, data) {
                            $("#addonCheckboxes").append(`
                                <div class="col-md-6">
                                    <label class="selection-card-custom" for="addon_${id}">
                                        <input class="d-none addon-checkbox" type="checkbox" id="addon_${id}" value="${id}" data-price="${data.price}" data-name="${data.name}">
                                        <div class="custom-checkbox-square"></div>
                                        <div class="selection-details">
                                            <div class="selection-name">${data.name}</div>
                                            <div class="selection-price">${formatRupiah(data.price)}</div>
                                            <div class="selection-meta">Kategori: ${data.category || '-'}</div>
                                        </div>
                                        <div class="addon-qty-wrap ms-3" style="display:none;">
                                            <input type="number" class="form-control form-control-sm addon-qty" name="addon_qty[${id}]" id="addon_qty_${id}" min="1" value="1">
                                        </div>
                                    </label>
                                </div>
                            `);
                        });
                    }

                    // Attach Listeners
                    $(".service-radio").on("change", function() {
                        $("#selectedServiceId").val($(this).val());
                        $(".service-radio").closest('.selection-card-custom').removeClass('active');
                        $(this).closest('.selection-card-custom').addClass('active');
                        updateOrderSummary();
                        updateDeviceSelection();
                    });

                    $(".addon-checkbox").on("change", function() {
                        const $card = $(this).closest('.selection-card-custom');
                        const $qty = $card.find('.addon-qty-wrap');
                        if ($(this).is(':checked')) { $card.addClass('active'); $qty.show(); }
                        else { $card.removeClass('active'); $qty.hide(); }
                        updateOrderSummary();
                    });

                    $(".addon-qty, #service_quantity").on("input change", updateOrderSummary);
                }
            }

            $("#outletSelect").on("change", updateAllSelections);
            if ($("#outletSelect").val()) updateAllSelections();

            // Native Datetime-local min set
            const now = new Date();
            const tzOffset = now.getTimezoneOffset() * 60000;
            const minTime = (new Date(now - tzOffset)).toISOString().slice(0, 16);
            $('#estimated_completion_at').attr('min', minTime);

            $("#paymentForm").on("submit", function(e) {
                if ($("#selectedServiceId").val() === "") {
                    swal("Peringatan", "Mohon pilih layanan.", "warning"); return false;
                }
                const serviceData = getSelectedServiceData();
                if ((serviceData?.service_options?.length > 0) && !$("#main_device_id")?.val()) {
                    swal("Peringatan", "Mohon pilih perangkat.", "warning"); return false;
                }
                e.preventDefault();
                swal({
                    title: 'Konfirmasi',
                    text: "Proses pembayaran kasir?",
                    icon: 'warning',
                    buttons: { cancel: "Batal", confirm: "Ya, proses!" },
                    dangerMode: true,
                }).then((willSubmit) => { if (willSubmit) { $("#paymentForm").off("submit").submit(); } });
            });
        });
    </script>
@endpush
