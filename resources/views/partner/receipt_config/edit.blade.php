{{-- resources/views/partner/receipt_config/edit.blade.php --}}

@extends('layouts.dashboard.app')

@push('styles')
    <link href="{{ asset('assets/plugins/gritter/css/jquery.gritter.css') }}" rel="stylesheet" />
    <style>
        /* Optional: Styling untuk area preview */
        .receipt-preview {
            border: 1px solid #ddd;
            padding: 15px;
            background-color: #fff;
            min-height: 500px;
            /* Atur tinggi minimal agar preview terlihat */
            overflow-y: auto;
            /* Jika struk panjang, bisa di-scroll */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            /* Mulai dari atas */
        }

        /* Style untuk kontainer struk di dalam preview */
        .receipt-preview #live-invoice-content {
            width: 300px;
            /* Lebar sesuai struk asli (sekitar 80mm) */
            font-family: 'Courier New', Courier, monospace;
            padding: 0; /* Hilangkan padding agar konten rapat */
            color: #000;
            /* Pastikan teks hitam */
            margin: 0 auto;
        }

        /* Style untuk wrapper body struk, tempat font-size akan diterapkan */
        .receipt-preview #receipt_body_wrapper {
            /* Font size akan diatur oleh JS secara langsung */
            /* Pastikan elemen di dalam wrapper ini mewarisi font-size */
        }

        /* Reset default margin for elements inside receipt */
        .receipt-preview #live-invoice-content h4,
        .receipt-preview #live-invoice-content p,
        .receipt-preview #live-invoice-content ul {
            margin: 0;
            padding: 0;
        }

        .receipt-preview #live-invoice-content ul li {
            margin-left: 0;
            /* Remove default list padding */
            list-style-type: none;
            /* Remove bullet points */
        }

        .receipt-preview #live-invoice-content .text-center {
            text-align: center;
        }

        .receipt-preview #live-invoice-content .text-left {
            text-align: left;
        }

        .receipt-preview #live-invoice-content .text-justify {
            text-align: justify;
        }

        .receipt-preview #live-invoice-content .dashed-line {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .receipt-preview #live-invoice-content small {
            font-size: inherit;
        }

        /* Biarkan inherit dari parent */

        /* Flexbox untuk baris item */
        .receipt-item-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            align-items: flex-start;
            /* Align items to start for multi-line content */
        }

        .receipt-item-line .label {
            font-weight: bold;
            flex-shrink: 0;
            margin-right: 10px;
        }

        .receipt-item-line .value {
            text-align: right;
            flex-grow: 1;
        }

        .receipt-item-line.align-left .value {
            text-align: left;
            /* Override for specific items like notes */
        }

        .receipt-item-line.notes-block {
            flex-direction: column;
            align-items: flex-start;
        }
        .receipt-item-line.notes-block .value {
            text-align: left;
            white-space: pre-wrap;
        }

        /* Header specific styles for inline/left aligned */
        .header-inline-container {
            display: flex;
            align-items: flex-start;
            /* Align to top of brand name, not center */
            margin-bottom: 10px;
        }

        .header-inline-container img {
            height: 25px;
            /* Fixed height for inline logo */
            width: auto;
            margin-right: 5px;
            object-fit: contain;
        }

        .header-text-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* Vertically center text if logo is taller */
        }

        .header-text-content h4,
        .header-text-content small {
            margin: 0;
            /* Reset margins */
        }

        /* QR Code Box Style */
        .qr-code-box {
            width: 100px;
            /* Lebar QR code */
            height: 100px;
            /* Tinggi QR code */
            border: 1px dashed #888;
            /* Border kotak */
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 10px auto;
            /* Pusatkan */
            background-color: #f0f0f0;
            /* Warna latar belakang */
            font-size: 0.7em;
            /* Ukuran font untuk teks placeholder */
            color: #555;
            text-align: center;
        }
    </style>
@endpush

@section('content')
    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">Konfigurasi Struk Pembayaran</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i
                        class="fa fa-expand"></i></a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i
                        class="fa fa-redo"></i></a>
            </div>
        </div>
        <div class="panel-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                {{-- Form Konfigurasi (Kiri) --}}
                <div class="col-md-6">
                    <form action="{{ route('partner.receipt.config.update') }}" method="POST" id="receiptConfigForm">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3 text-primary"><i class="fas fa-file-invoice me-2"></i>Elemen Struk</h5>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="show_brand_logo"
                                        name="show_brand_logo" value="1"
                                        {{ $config['show_brand_logo'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_brand_logo">Tampilkan Logo Brand</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="show_brand_name"
                                        name="show_brand_name" value="1"
                                        {{ $config['show_brand_name'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_brand_name">Tampilkan Nama Brand</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="show_outlet_address"
                                        name="show_outlet_address" value="1"
                                        {{ $config['show_outlet_address'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_outlet_address">Tampilkan Alamat
                                        Outlet</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="show_outlet_phone"
                                        name="show_outlet_phone" value="1"
                                        {{ $config['show_outlet_phone'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_outlet_phone">Tampilkan Telepon Outlet</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="show_nota_id" name="show_nota_id"
                                        value="1" {{ $config['show_nota_id'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_nota_id">Tampilkan Nomor Nota</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="show_customer_info"
                                        name="show_customer_info" value="1"
                                        {{ $config['show_customer_info'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_customer_info">Tampilkan Info
                                        Pelanggan</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    {{-- UBAH NAMA DAN ID --}}
                                    <input class="form-check-input" type="checkbox" id="show_payment_method"
                                        name="show_payment_method" value="1"
                                        {{ $config['show_payment_method'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_payment_method">Tampilkan Metode
                                        Pembayaran</label> {{-- UBAH LABEL --}}
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="show_cashier_name"
                                        name="show_cashier_name" value="1"
                                        {{ $config['show_cashier_name'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_cashier_name">Tampilkan Nama Kasir</label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="show_datetime" name="show_datetime"
                                        value="1" {{ $config['show_datetime'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_datetime">Tampilkan Tanggal & Waktu</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="show_notes" name="show_notes"
                                        value="1" {{ $config['show_notes'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_notes">Tampilkan Catatan</label>
                                </div>
                                {{-- NEW: Checkbox untuk QR Code --}}
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="show_qr_code"
                                        name="show_qr_code" value="1"
                                        {{ $config['show_qr_code'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_qr_code">Tampilkan QR Code</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3 text-primary"><i class="fas fa-image me-2"></i>Pengaturan Header & Font</h5>

                        <div class="mb-3">
                            <label class="form-label d-block">Gaya Header:</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="header_style"
                                    id="header_style_centered" value="centered"
                                    {{ $config['header_style'] == 'centered' ? 'checked' : '' }}>
                                <label class="form-check-label" for="header_style_centered">Di Atas (Centered)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="header_style"
                                    id="header_style_left" value="left_aligned"
                                    {{ $config['header_style'] == 'left_aligned' ? 'checked' : '' }}>
                                <label class="form-check-label" for="header_style_left">Di Samping (Left-aligned)</label>
                            </div>
                        </div>

                        {{-- NEW: Logo Size Control --}}
                        <div class="mb-3">
                            <label for="logo_size" class="form-label">Ukuran Logo:</label>
                            <select class="form-select" id="logo_size" name="logo_size">
                                <option value="small" {{ ($config['logo_size'] ?? 'normal') == 'small' ? 'selected' : '' }}>Kecil (25px)</option>
                                <option value="normal" {{ ($config['logo_size'] ?? 'normal') == 'normal' ? 'selected' : '' }}>Normal (50px)</option>
                                <option value="large" {{ ($config['logo_size'] ?? 'normal') == 'large' ? 'selected' : '' }}>Besar (75px)</option>
                            </select>
                        </div>
                        {{-- END NEW: Logo Size Control --}}

                        <div class="mb-3">
                            <label for="font_size" class="form-label">Ukuran Tulisan:</label>
                            <select class="form-select" id="font_size" name="font_size">
                                <option value="20px" {{ $config['font_size'] == '20px' ? 'selected' : '' }}>20px (Kecil)
                                </option>
                                <option value="21px" {{ $config['font_size'] == '21px' ? 'selected' : '' }}>21px
                                </option>
                                <option value="22px" {{ $config['font_size'] == '22px' ? 'selected' : '' }}>22px
                                    (Normal)</option>
                                <option value="23px" {{ $config['font_size'] == '23px' ? 'selected' : '' }}>23px
                                </option>
                                <option value="24px" {{ $config['font_size'] == '24px' ? 'selected' : '' }}>24px (Besar)
                                </option>
                            </select>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3 text-primary"><i class="fas fa-comment-alt me-2"></i>Pesan Kustom</h5>

                        <div class="mb-3">
                            <label for="thank_you_message" class="form-label">Pesan Terima Kasih</label>
                            <textarea class="form-control" id="thank_you_message" name="thank_you_message" rows="2">{{ old('thank_you_message', $config['thank_you_message']) }}</textarea>
                            @error('thank_you_message')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="instruction_message" class="form-label">Pesan Instruksi</label>
                            <textarea class="form-control" id="instruction_message" name="instruction_message" rows="2">{{ old('instruction_message', $config['instruction_message']) }}</textarea>
                            @error('instruction_message')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2">
                                <i class="fas fa-save me-2"></i> Simpan Konfigurasi
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Preview Struk (Kanan) --}}
                <div class="col-md-6">
                    <h5 class="mb-3 text-primary">Preview Struk</h5>
                    <div class="receipt-preview">
                        <div id="live-invoice-content">
                            {{-- Header Section --}}
                            <div id="preview_header_container" style="margin-bottom: 10px;">
                                {{-- Content dynamically generated by JS --}}
                            </div>
                            <div id="preview_header_line" class="dashed-line"></div>

                            {{-- NEW: Wrapper for Body Content --}}
                            <div id="receipt_body_wrapper">
                                {{-- Transaction Details --}}
                                <div class="receipt-item-line" id="preview_nota_id_line">
                                    <span class="label">Nota:</span>
                                    <span class="value" id="preview_nota_id"></span>
                                </div>

                                {{-- Customer Info --}}
                                <div id="preview_customer_info_section">
                                    <div class="receipt-item-line">
                                        <span class="label">Customer:</span>
                                        <span class="value" id="preview_customer_name"></span>
                                    </div>
                                    <div class="receipt-item-line">
                                        <span class="label">Email:</span>
                                        <span class="value" id="preview_customer_email"></span>
                                    </div>
                                </div>

                                {{-- Payment Info --}}
                                <div class="receipt-item-line" id="preview_payment_method_line">
                                    <span class="label">Metode Pembayaran:</span>
                                    <span class="value" id="preview_payment_method"></span>
                                </div>
                                <div class="receipt-item-line" id="preview_cashier_name_line">
                                    <span class="label">Kasir:</span>
                                    <span class="value" id="preview_cashier_name"></span>
                                </div>

                                <div id="preview_payment_line" class="dashed-line"></div>

                                {{-- Service & Amount --}}
                                <div class="receipt-item-line" id="preview_service_type_line">
                                    <span class="label">Layanan:</span>
                                    <span class="value" id="preview_service_type"></span>
                                </div>
                                <div class="receipt-item-line" id="preview_service_price_line">
                                    <span class="label">Harga Layanan:</span>
                                    <span class="value" id="preview_service_price"></span>
                                </div>

                                <div id="preview_addons_section">
                                    <div class="receipt-item-line" id="addons_label_line_static">
                                        <span class="label">Tambahan:</span>
                                    </div>
                                    <ul id="preview_addons_list" style="margin: 0; padding: 0; list-style-type: none;"></ul>
                                </div>

                                <div class="receipt-item-line notes-block" id="preview_notes_line">
                                    <span class="label">Catatan:</span>
                                    <span class="value" id="preview_notes"></span>
                                </div>

                                <div id="preview_total_summary_line" class="dashed-line"></div>

                                <div class="receipt-item-line" style="margin-top: 10px;" id="preview_total_amount_line">
                                    <span class="label">TOTAL:</span>
                                    <span class="value" id="preview_total_amount" style="font-weight: bold;"></span>
                                </div>
                                <div class="receipt-item-line" id="preview_amount_paid_line">
                                    <span class="label">JUMLAH DIBAYAR:</span>
                                    <span class="value" id="preview_amount_paid" style="font-weight: bold;"></span>
                                </div>

                                <div id="preview_detail_line" class="dashed-line"></div>

                                {{-- Date & Time --}}
                                <div class="receipt-item-line" id="preview_datetime_line_content">
                                    <span class="label">Tanggal:</span>
                                    <span class="value" id="preview_datetime"></span>
                                </div>

                                <div id="preview_datetime_line" class="dashed-line"></div>

                                {{-- NEW: QR Code Section --}}
                                <div id="preview_qr_code_section" class="text-center" style="display: none;">
                                    <div class="qr-code-box">QR Code Here</div>
                                    <p style="font-size: 0.8em; margin-top: 5px; color: #555;">Scan for details</p>
                                </div>
                            </div>
                            {{-- END NEW: Wrapper for Body Content --}}


                            {{-- Footer Messages --}}
                            <div class="text-center" id="preview_footer_messages_container" style="font-size: inherit;">
                                <p id="preview_thank_you_message" style="margin: 5px 0;"></p>
                                <p id="preview_instruction_message" style="margin: 5px 0;"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/plugins/gritter/js/jquery.gritter.js') }}"></script>
    <script>
        @if (session('success'))
            $.gritter.add({
                title: 'Success!',
                text: '{{ session('success') }}',
                sticky: false,
                time: 3000,
                class_name: 'gritter-light'
            });
        @endif
        @if (session('error'))
            $.gritter.add({
                title: 'Error!',
                text: '{{ session('error') }}',
                sticky: false,
                time: 3000,
                class_name: 'gritter-light'
            });
        @endif

        document.addEventListener('DOMContentLoaded', function() {
            const configForm = document.getElementById('receiptConfigForm');
            const previewContent = document.getElementById('live-invoice-content');
            const receiptBodyWrapper = document.getElementById('receipt_body_wrapper'); // NEW: Get body wrapper
            const previewFooterMessagesContainer = document.getElementById('preview_footer_messages_container'); // NEW: Get footer container

            // References to the static elements in the preview HTML
            const previewHeaderContainer = document.getElementById('preview_header_container');
            const previewNotaIdLine = document.getElementById('preview_nota_id_line');
            const previewNotaId = document.getElementById('preview_nota_id');
            const previewCustomerInfoSection = document.getElementById('preview_customer_info_section');
            const previewPaymentMethodLine = document.getElementById('preview_payment_method_line');
            const previewPaymentMethod = document.getElementById('preview_payment_method');
            const previewCashierNameLine = document.getElementById('preview_cashier_name_line');
            const previewCashierName = document.getElementById('preview_cashier_name');
            const previewServiceTypeLine = document.getElementById('preview_service_type_line');
            const previewServiceType = document.getElementById('preview_service_type');
            const previewServicePriceLine = document.getElementById('preview_service_price_line');
            const previewServicePrice = document.getElementById('preview_service_price');
            const previewAddonsSection = document.getElementById('preview_addons_section');
            const previewAddonsList = document.getElementById('preview_addons_list');
            const addonsLabelLineStatic = document.getElementById('addons_label_line_static');
            const previewNotesLine = document.getElementById('preview_notes_line');
            const previewNotes = document.getElementById('preview_notes');
            const previewTotalAmountLine = document.getElementById('preview_total_amount_line');
            const previewTotalAmount = document.getElementById('preview_total_amount');
            const previewAmountPaid = document.getElementById('preview_amount_paid');
            const previewDatetimeLineContent = document.getElementById('preview_datetime_line_content');
            const previewDatetime = document.getElementById('preview_datetime');
            const previewThankYouMessage = document.getElementById('preview_thank_you_message');
            const previewInstructionMessage = document.getElementById('instruction_message'); // Fixed ID
            const previewQrCodeSection = document.getElementById('preview_qr_code_section');

            const previewHeaderLine = document.getElementById('preview_header_line');
            const previewPaymentLine = document.getElementById('preview_payment_line');
            const previewDetailLine = document.getElementById('preview_detail_line');
            const previewDatetimeLine = document.getElementById('preview_datetime_line');
            const previewTotalSummaryLine = document.getElementById('preview_total_summary_line');


            // Dummy Data for Preview (ALL DUMMY)
            const currentOwnerBrandName = "{{ Auth::user()->owner->brand_name ?? 'CORRUPT LAUNDRY' }}";
            // Menggunakan placeholder image jika tidak ada logo dari DB
            const currentOwnerBrandLogo =
                "{{ Auth::user()->owner->brand_logo ? asset('storage/' . Auth::user()->owner->brand_logo) : 'https://placehold.co/50x50/cccccc/000000?text=LOGO' }}";
            const currentOutletAddress =
                "{{ Auth::user()->owner->outlets->first()->address ?? 'Jl. Contoh No. 123, Kota Dummy, 12345' }}";
            const currentOutletPhone =
                "{{ Auth::user()->owner->outlets->first()->phone_number ?? '0811-2233-4455' }}";

            const dummyTransaction = {
                owner: {
                    brand_name: currentOwnerBrandName,
                    brand_logo: currentOwnerBrandLogo
                },
                outlet: {
                    address: currentOutletAddress,
                    phone_number: currentOutletPhone,
                },
                order_id: 'TRX-987654321',
                service_price: 50000,
                created_at: '2025-07-04 14:30:00',
                timezone: 'WIB',
                type_for_preview: 'manual', // Default, digunakan untuk simulasi tipe transaksi
                manual_transaction_details: {
                    cashier_name: 'Kasir Preview',
                    notes: 'Mohon cuci terpisah untuk baju putih dan mudah luntur. Terima kasih atas pengertiannya.',
                    payment_method: 'cash', // Dummy payment method
                    addons: [{
                            "id": 1,
                            "name": "Pewangi Extra",
                            "price": "2000.00"
                        },
                        {
                            "id": 2,
                            "name": "Setrika Premium",
                            "price": "5000.00"
                        }
                    ]
                },
                member_transaction: {
                    member: {
                        user: {
                            name: 'Pelanggan Member Preview',
                            email: 'member.preview@example.com'
                        }
                    }
                },
                service_type: 'Cuci Kering (Kg)',
            };
            // End Dummy Data

            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(number);
            }

            // Helper to check if an element is visible (considering display:none)
            function isElementVisible(el) {
                if (!el) return false;
                const computedStyle = window.getComputedStyle(el);
                return computedStyle.display !== 'none' && computedStyle.visibility !== 'hidden' && el
                    .offsetParent !== null;
            }

            function updatePreview() {
                const currentConfig = {};
                configForm.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    currentConfig[checkbox.name] = checkbox.checked;
                });
                configForm.querySelectorAll('input[name="header_style"]').forEach(radio => {
                    if (radio.checked) {
                        currentConfig.header_style = radio.value;
                    }
                });
                currentConfig.font_size = document.getElementById('font_size').value;
                currentConfig.logo_size = document.getElementById('logo_size').value; // Ambil nilai logo_size
                currentConfig.thank_you_message = document.getElementById('thank_you_message').value;
                currentConfig.instruction_message = document.getElementById('instruction_message').value;

                // --- PERBAIKAN: Terapkan font size hanya ke body wrapper ---
                receiptBodyWrapper.style.fontSize = currentConfig.font_size;
                // --- AKHIR PERBAIKAN ---


                // --- Hitung Total Amount dari service_price dan addons ---
                let totalAddonsPrice = 0;
                if (dummyTransaction.manual_transaction_details?.addons && Array.isArray(dummyTransaction
                        .manual_transaction_details.addons)) {
                    dummyTransaction.manual_transaction_details.addons.forEach(addon => {
                        totalAddonsPrice += parseFloat(addon.price || 0);
                    });
                }
                const totalAmount = dummyTransaction.service_price + totalAddonsPrice;
                const amountPaidValue = totalAmount; // For preview, assume amount paid equals total


                // --- Update Header Section (Logo, Brand Name, Outlet Info) ---
                previewHeaderContainer.innerHTML = '';

                let headerLogoImg;
                let logoPxSize; // Variabel untuk menyimpan ukuran piksel logo

                // Tentukan ukuran piksel berdasarkan pilihan logo_size
                switch (currentConfig.logo_size) {
                    case 'small':
                        logoPxSize = '25px';
                        break;
                    case 'normal':
                        logoPxSize = '50px';
                        break;
                    case 'large':
                        logoPxSize = '75px';
                        break;
                    default:
                        logoPxSize = '50px'; // Default jika tidak ada atau tidak cocok
                }


                if (currentConfig.show_brand_logo && dummyTransaction.owner.brand_logo && dummyTransaction.owner.brand_logo !== '') {
                    headerLogoImg = document.createElement('img');
                    headerLogoImg.alt = 'Brand Logo';
                    headerLogoImg.src = dummyTransaction.owner.brand_logo;
                    headerLogoImg.style.objectFit = 'contain';
                }

                const brandNameH4 = document.createElement('h4');
                brandNameH4.style.margin = '0';
                brandNameH4.style.fontSize = '1.6em';
                brandNameH4.style.fontWeight = '700';
                brandNameH4.innerText = (dummyTransaction.owner.brand_name || 'N/A').toUpperCase();

                const outletAddressSmall = document.createElement('small');
                outletAddressSmall.style.fontSize = '1.1em';
                outletAddressSmall.innerText = dummyTransaction.outlet.address || 'N/A';

                const outletPhoneSmall = document.createElement('small');
                outletPhoneSmall.style.fontSize = '1.1em';
                outletPhoneSmall.innerText = dummyTransaction.outlet.phone_number || '-';

                if (currentConfig.header_style === 'centered') {
                    previewHeaderContainer.style.textAlign = 'center';
                    previewHeaderContainer.style.marginLeft = '0'; // Reset margin untuk centered
                    previewHeaderContainer.style.paddingLeft = '10px'; // Reset padding untuk centered
                    if (headerLogoImg) {
                        headerLogoImg.style.width = logoPxSize; // Terapkan ukuran logo
                        headerLogoImg.style.height = logoPxSize; // Terapkan ukuran logo
                        headerLogoImg.style.display = 'block';
                        headerLogoImg.style.margin = '0 auto 5px auto';
                        previewHeaderContainer.appendChild(headerLogoImg);
                    }
                    if (currentConfig.show_brand_name) {
                        previewHeaderContainer.appendChild(brandNameH4);
                    }
                    if (currentConfig.show_outlet_address) {
                        outletAddressSmall.style.display = 'block';
                        previewHeaderContainer.appendChild(outletAddressSmall);
                    }
                    if (currentConfig.show_outlet_phone) {
                        outletPhoneSmall.style.display = 'block';
                        previewHeaderContainer.appendChild(outletPhoneSmall);
                    }

                } else { // left_aligned
                    previewHeaderContainer.style.textAlign = 'left';
                    // --- PERBAIKAN UNTUK "NEMPEL KEKIRI" ---
                    previewHeaderContainer.style.marginLeft = '-10px'; // Menggeser kontainer ke kiri
                    previewHeaderContainer.style.paddingLeft = '10px'; // Mengembalikan padding internal untuk konten di dalamnya
                    // --- AKHIR PERBAIKAN ---

                    const headerContentDiv = document.createElement('div');
                    headerContentDiv.classList.add('header-inline-container');
                    // justify-content: flex-start sudah ada di CSS, tidak perlu diatur ulang di sini
                    // headerContentDiv.style.justifyContent = 'flex-start';

                    const textContentDiv = document.createElement('div');
                    textContentDiv.classList.add('header-text-content');

                    if (headerLogoImg) {
                        headerLogoImg.style.height = logoPxSize; // Terapkan ukuran logo
                        headerLogoImg.style.width = 'auto'; // Biarkan width auto untuk menjaga rasio
                        headerLogoImg.style.display = 'block';
                        headerLogoImg.style.margin = '0 5px 0 0';
                        headerContentDiv.appendChild(headerLogoImg);
                    }
                    if (currentConfig.show_brand_name) {
                        textContentDiv.appendChild(brandNameH4);
                    }
                    if (currentConfig.show_outlet_address) {
                        outletAddressSmall.style.display = 'block';
                        textContentDiv.appendChild(outletAddressSmall);
                    }
                    if (currentConfig.show_outlet_phone) {
                        outletPhoneSmall.style.display = 'block';
                        textContentDiv.appendChild(outletPhoneSmall);
                    }
                    headerContentDiv.appendChild(textContentDiv);
                    previewHeaderContainer.appendChild(headerContentDiv);
                }
                if (!currentConfig.show_brand_logo && !currentConfig.show_brand_name) {
                    previewHeaderContainer.innerHTML = '';
                }


                // --- Update Transaction Details ---
                previewNotaIdLine.style.display = currentConfig.show_nota_id ? 'flex' : 'none';
                previewNotaId.innerText = dummyTransaction.order_id;

                // Customer Info
                if (currentConfig.show_customer_info) {
                    previewCustomerInfoSection.style.display = 'block';
                    previewCustomerInfoSection.querySelector('#preview_customer_name').innerText = dummyTransaction
                        .member_transaction.member.user.name;
                    previewCustomerInfoSection.querySelector('#preview_customer_email').innerText = dummyTransaction
                        .member_transaction.member.user.email;
                } else {
                    previewCustomerInfoSection.style.display = 'none';
                }

                // Metode Pembayaran
                previewPaymentMethodLine.style.display = currentConfig.show_payment_method ? 'flex' : 'none';
                let paymentMethodDisplay = '';
                if (dummyTransaction.type_for_preview === 'manual' && dummyTransaction.manual_transaction_details
                    ?.payment_method) {
                    paymentMethodDisplay = dummyTransaction.manual_transaction_details.payment_method === 'cash' ?
                        'Tunai' : 'Non-Tunai';
                } else if (dummyTransaction.type_for_preview === 'member') {
                    paymentMethodDisplay = 'Saldo Member';
                } else {
                    paymentMethodDisplay = 'QRIS';
                }
                previewPaymentMethod.innerText = paymentMethodDisplay;

                // Nama Kasir (Perbaikan agar hanya bergantung pada checkbox show_cashier_name)
                previewCashierNameLine.style.display = currentConfig.show_cashier_name ? 'flex' : 'none';
                previewCashierName.innerText = dummyTransaction.manual_transaction_details?.cashier_name || '-';


                // Service Type (Always Shown)
                previewServiceTypeLine.style.display = 'flex';
                previewServiceType.innerText = dummyTransaction.service_type || 'Layanan Contoh';

                // Service Price (Always Shown)
                previewServicePriceLine.style.display = 'flex';
                previewServicePrice.innerText = formatRupiah(dummyTransaction.service_price);


                // Addons
                previewAddonsList.innerHTML = '';
                const hasDummyAddons = dummyTransaction.manual_transaction_details?.addons && Array.isArray(dummyTransaction.manual_transaction_details.addons) && dummyTransaction.manual_transaction_details.addons.length > 0;

                if (hasDummyAddons) {
                    previewAddonsSection.style.display = 'block';
                    addonsLabelLineStatic.style.display = 'flex';
                    dummyTransaction.manual_transaction_details.addons.forEach(addon => {
                        const li = document.createElement('li');
                        li.classList.add('receipt-item-line');
                        li.innerHTML =
                            `<span>- ${addon.name}</span><span class="value">${formatRupiah(parseFloat(addon.price || 0))}</span>`;
                        previewAddonsList.appendChild(li);
                    });
                } else {
                    previewAddonsSection.style.display = 'none';
                    addonsLabelLineStatic.style.display = 'none';
                }


                // Notes
                if (currentConfig.show_notes && dummyTransaction.manual_transaction_details?.notes) {
                    previewNotesLine.style.display = 'flex';
                    previewNotes.innerText = dummyTransaction.manual_transaction_details.notes;
                } else {
                    previewNotesLine.style.display = 'none';
                }

                // Total Amount (Always Shown)
                previewTotalAmountLine.style.display = 'flex';
                previewTotalAmount.innerText = formatRupiah(totalAmount);

                // Amount Paid (optional, configured by checkbox)
              
                // --- Update Date & Time ---
                previewDatetimeLineContent.style.display = currentConfig.show_datetime ? 'flex' : 'none';
                const formattedDate = dummyTransaction.created_at ? new Date(dummyTransaction.created_at)
                    .toLocaleString('id-ID', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    }) + ' ' + (dummyTransaction.timezone.toUpperCase() || '') : '';
                previewDatetime.innerText = formattedDate;

                // --- Update QR Code Section ---
                previewQrCodeSection.style.display = currentConfig.show_qr_code ? 'block' : 'none';


                // --- Update Custom Messages ---
                // Pastikan font size tidak mempengaruhi ini
                previewThankYouMessage.style.fontSize = 'inherit';
                previewInstructionMessage.style.fontSize = 'inherit';

                previewThankYouMessage.innerText = currentConfig.thank_you_message;
                previewInstructionMessage.innerText = currentConfig.instruction_message;

                // --- Update Dashed Lines Visibility ---
                const headerContentRendered = previewHeaderContainer.children.length > 0;
                const transactionInfoRendered = isElementVisible(previewNotaIdLine) || isElementVisible(
                    previewCustomerInfoSection);
                const paymentInfoRendered = isElementVisible(previewPaymentMethodLine) || isElementVisible(
                    previewCashierNameLine);
                const serviceDetailsActive = isElementVisible(previewServiceTypeLine) || isElementVisible(
                    previewServicePriceLine) || isElementVisible(previewAddonsSection) || isElementVisible(
                    previewNotesLine);
              
                const datetimeRendered = isElementVisible(previewDatetimeLineContent);
                const qrCodeRendered = isElementVisible(previewQrCodeSection);
                const footerMessageRendered = currentConfig.thank_you_message || currentConfig.instruction_message;

                previewHeaderLine.style.display = headerContentRendered && (transactionInfoRendered ||
                    paymentInfoRendered || serviceDetailsActive  || datetimeRendered ||
                    qrCodeRendered || footerMessageRendered) ? 'block' : 'none';
                previewPaymentLine.style.display = (transactionInfoRendered || paymentInfoRendered) && (
                    serviceDetailsActive  || datetimeRendered || qrCodeRendered ||
                    footerMessageRendered) ? 'block' : 'none';
                previewTotalSummaryLine.style.display = serviceDetailsActive ? 'block' : 'none';
                previewDetailLine.style.display = (serviceDetailsActive) && (datetimeRendered ||
                    qrCodeRendered || footerMessageRendered) ? 'block' : 'none';
                previewDatetimeLine.style.display = datetimeRendered && (qrCodeRendered || footerMessageRendered) ?
                    'block' : 'none';
            }


            // Initial call to update preview
            updatePreview();

            // Add event listeners for form changes
            configForm.addEventListener('change', updatePreview);
            configForm.addEventListener('keyup', updatePreview);
        });
    </script>
@endpush
