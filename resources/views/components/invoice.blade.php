{{-- resources/views/components/invoice.blade.php --}}

@props(['transaction_id'])

@php
    // --- START DUMMY DATA FOR INVOICE ---
    // HANYA UNTUK TUJUAN PENGUJIAN SESUAI PERMINTAAN.
    // DI LINGKUNGAN PRODUKSI, DATA INI HARUS DIAMBIL DARI DATABASE.

    $dummyOwnerBrandName = 'CORRUPT LAUNDRY';
    $dummyOwnerBrandLogo = 'https://via.placeholder.com/50x50?text=LOGO'; // Placeholder logo
    $dummyOutletAddress = 'Jl. Raya Dummy No. 456, Kota Testing, 98765';
    $dummyOutletPhone = '0877-6655-4433';

    $dummyTransaction = (object) [
        'id' => $transaction_id,
        'order_id' => 'INV-DUMMY-' . uniqid(),
        'service_price' => 75000,
        'created_at' => \Carbon\Carbon::now()->subMinutes(10)->format('Y-m-d H:i:s'),
        'timezone' => 'WIB',
        'type' => 'manual', // or 'member'
        'amount_paid' => 75000,
        'service_type' => 'Cuci + Setrika (Kg)',
        'manualTransactionDetails' => (object) [
            'cashier_name' => 'Kasir Dummy',
            'notes' =>
                'Terima kasih telah menggunakan jasa kami. Mohon periksa kembali semua detail sebelum meninggalkan outlet.',
            'payment_method' => 'cash', // DUMMY: 'cash' atau 'non_cash'
            'addons' => [
                (object) ['id' => 1, 'name' => 'Pelicin Baju', 'price' => '3000.00'],
                (object) ['id' => 2, 'name' => 'Tas Laundry Besar', 'price' => '15000.00'],
            ],
        ],
        'memberTransaction' => (object) [
            'member' => (object) [
                'user' => (object) [
                    'name' => 'John Doe Dummy',
                    'email' => 'john.doe.dummy@example.com',
                ],
            ],
        ],
        'owner' => (object) [
            'brand_name' => $dummyOwnerBrandName,
            'brand_logo' => $dummyOwnerBrandLogo,
            'receipt_config' => [
                // Dummy receipt_config
                'show_brand_name' => true,
                'show_outlet_address' => true,
                'show_outlet_phone' => true,
                'show_nota_id' => true,
                'show_customer_info' => false,
                'show_payment_method' => true, // << UBAH INI
                'show_cashier_name' => true,
                'show_datetime' => true,
                'show_notes' => true,
                'show_qr_code' => true, // << NEW: Tampilkan QR Code secara default di dummy ini
                'header_style' => 'centered',
                'font_size' => '12px',
                'thank_you_message' => '-- TERIMA KASIH --',
                'instruction_message' => 'Nota ini wajib dibawa sebagai bukti transaksi.',
            ],
        ],
        'outlet' => (object) [
            'address' => $dummyOutletAddress,
            'phone_number' => $dummyOutletPhone,
        ],
    ];

    // Ini adalah data yang akan digunakan untuk rendering
    $transaction = $dummyTransaction;
    $config = $transaction->owner->receipt_config;

    // Pastikan font_size valid
    if (!in_array($config['font_size'], ['10px', '11px', '12px', '13px', '14px'])) {
        $config['font_size'] = '12px';
    }
    // Pastikan header_style valid
    if (!in_array($config['header_style'], ['centered', 'left_aligned'])) {
        $config['header_style'] = 'centered';
    }

    // Hitung total harga layanan + addons untuk "TOTAL"
    $totalAddonsPrice = 0;
    if (
        $transaction->type == 'manual' &&
        $transaction->manualTransactionDetails &&
        is_array($transaction->manualTransactionDetails->addons)
    ) {
        foreach ($transaction->manualTransactionDetails->addons as $addon) {
            $totalAddonsPrice += (float) ($addon->price ?? 0);
        }
    }
    $totalAmountBeforePaid = $transaction->service_price + $totalAddonsPrice;
    $amountPaid = $transaction->amount_paid;

    // Helper untuk memeriksa apakah ada konten header yang aktif
    $hasHeaderContent =
        ($config['show_brand_name'] && ($transaction->owner->brand_name || $transaction->owner->brand_logo)) ||
        $config['show_outlet_address'] ||
        $config['show_outlet_phone'];

    // Helper untuk memeriksa apakah ada konten transaksi yang aktif
    $hasTransactionInfo =
        $config['show_nota_id'] ||
        ($config['show_customer_info'] &&
            $transaction->type == 'member' &&
            ($transaction->memberTransaction->member->user->name ?? false));

    // Helper untuk memeriksa apakah ada konten pembayaran/kasir yang aktif
    $hasPaymentInfo =
        ($config['show_payment_method'] &&
            $transaction->type == 'manual' &&
            ($transaction->manualTransactionDetails->payment_method ?? false)) || // << UBAH INI
        ($config['show_cashier_name'] && $transaction->type == 'manual');

    // Helper untuk memeriksa apakah ada konten layanan/harga yang aktif
    $hasServiceDetails = true; // Service Type and Service Price are always shown
    if (
        $transaction->type == 'manual' &&
        $transaction->manualTransactionDetails &&
        is_array($transaction->manualTransactionDetails->addons) &&
        count($transaction->manualTransactionDetails->addons) > 0
    ) {
        $hasServiceDetails = true;
    }
    if (
        $config['show_notes'] &&
        $transaction->type == 'manual' &&
        ($transaction->manualTransactionDetails->notes ?? false)
    ) {
        $hasServiceDetails = true;
    }

    // Helper untuk memeriksa apakah ada konten tanggal/waktu yang aktif
    $hasDateTime = $config['show_datetime'];

    // Helper untuk memeriksa apakah ada QR code
    $hasQrCode = $config['show_qr_code'];

    // Helper untuk memeriksa apakah ada pesan footer yang aktif
    $hasFooterMessage = !empty($config['thank_you_message']) || !empty($config['instruction_message']);
@endphp

<div id="invoice-content-{{ $transaction->id }}"
    style="width: 300px; font-family: 'Courier New', Courier, monospace; font-size: {{ $config['font_size'] }}; padding: 10px; margin: auto; display: none;">

    {{-- Header Section --}}
    <div
        style="margin-bottom: 10px; {{ $config['header_style'] == 'centered' ? 'text-align: center;' : 'text-align: left;' }}">
        @if ($config['show_brand_name'])
            @if ($config['header_style'] == 'centered')
                @if ($transaction->owner->brand_logo)
                    <img src="{{ $transaction->owner->brand_logo }}" alt="Brand Logo"
                        style="width: 50px; height: 50px; object-fit: contain; margin: 0 auto 5px auto; display: block;">
                @endif
                <h4 style="margin: 0;">{{ strtoupper($transaction->owner->brand_name ?? 'N/A') }}</h4>
            @else
                {{-- left_aligned --}}
                <div style="display: flex; align-items: flex-start; margin-bottom: 5px; justify-content: flex-start;">
                    @if ($transaction->owner->brand_logo)
                        <img src="{{ $transaction->owner->brand_logo }}" alt="Brand Logo"
                            style="height: 25px; width: auto; object-fit: contain; margin-right: 5px;">
                    @endif
                    <div style="display: flex; flex-direction: column; justify-content: center;">
                        <h4 style="margin: 0;">{{ strtoupper($transaction->owner->brand_name ?? 'N/A') }}</h4>
                        @if ($config['show_outlet_address'])
                            <small
                                style="font-size: inherit; display: block; text-align: left;">{{ $transaction->outlet->address ?? 'N/A' }}</small>
                        @endif
                        @if ($config['show_outlet_phone'])
                            <small
                                style="font-size: inherit; display: block; text-align: left;">{{ $transaction->outlet->phone_number ?? '-' }}</small>
                        @endif
                    </div>
                </div>
            @endif
        @endif
        @if ($config['show_brand_name'] && $config['header_style'] == 'centered')
            @if ($config['show_outlet_address'])
                <small style="font-size: inherit; display: block;">{{ $transaction->outlet->address ?? 'N/A' }}</small>
            @endif
            @if ($config['show_outlet_phone'])
                <small
                    style="font-size: inherit; display: block;">{{ $transaction->outlet->phone_number ?? '-' }}</small>
            @endif
        @endif
    </div>

    {{-- Line separator for header --}}
    @if (
        $hasHeaderContent &&
            ($hasTransactionInfo ||
                $hasPaymentInfo ||
                $hasServiceDetails ||
                $hasDateTime ||
                $hasQrCode ||
                $hasFooterMessage))
        <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
    @endif

    @if ($config['show_nota_id'])
        <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
            <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Nota:</span>
            <span style="text-align: right; flex-grow: 1;">{{ $transaction->order_id }}</span>
        </div>
    @endif

    @if (
        $config['show_customer_info'] &&
            $transaction->type == 'member' &&
            ($transaction->memberTransaction->member->user->name ?? false))
        <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
            <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Customer:</span>
            <span
                style="text-align: right; flex-grow: 1;">{{ $transaction->memberTransaction->member->user->name ?? '-' }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
            <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Email:</span>
            <span
                style="text-align: right; flex-grow: 1;">{{ $transaction->memberTransaction->member->user->email ?? '-' }}</span>
        </div>
    @endif

    {{-- Line separator for transaction info --}}
    @if (($hasTransactionInfo || $hasPaymentInfo) && ($hasServiceDetails || $hasDateTime || $hasQrCode || $hasFooterMessage))
        <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
    @endif

    @if (
        $config['show_payment_method'] &&
            $transaction->type == 'manual' &&
            ($transaction->manualTransactionDetails->payment_method ?? false))
        <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
            <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Metode Pembayaran:</span>
            <span style="text-align: right; flex-grow: 1;">
                @if ($transaction->manualTransactionDetails->payment_method == 'cash')
                    Tunai
                @elseif ($transaction->manualTransactionDetails->payment_method == 'non_cash')
                    Non-Tunai
                @else
                    -
                @endif
            </span>
        </div>
    @endif

    @if ($config['show_cashier_name'] && $transaction->type == 'manual' && $transaction->manualTransactionDetails)
        <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
            <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Kasir:</span>
            <span
                style="text-align: right; flex-grow: 1;">{{ $transaction->manualTransactionDetails->cashier_name ?? '-' }}</span>
        </div>
    @endif

    {{-- Line separator for payment info --}}
    @if (($hasTransactionInfo || $hasPaymentInfo) && $hasServiceDetails && ($hasDateTime || $hasQrCode || $hasFooterMessage))
        <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
    @endif

    {{-- Tulisan "Nama Produk/Layanan" --}}
    <div style="display: flex; justify-content: flex-start; margin-bottom: 5px;">
        <span style="font-weight: bold;">Nama Produk/Layanan:</span>
    </div>

    {{-- Service Type (ALWAYS SHOWN) --}}
    <div style="display: flex; justify-content: space-between; margin-bottom: 3px; margin-left: 10px;">
        <span>- {{ ucfirst(str_replace('_', ' ', $transaction->service_type)) }}</span>
        <span
            style="text-align: right; flex-grow: 1;">Rp{{ number_format($transaction->service_price, 0, ',', '.') }}</span>
    </div>

    {{-- Addons (ALWAYS SHOWN if present) --}}
    @if (
        $transaction->type == 'manual' &&
            $transaction->manualTransactionDetails &&
            is_array($transaction->manualTransactionDetails->addons) &&
            count($transaction->manualTransactionDetails->addons) > 0)
        <div style="display: flex; justify-content: flex-start; margin-bottom: 3px; margin-top: 5px;">
            <span style="font-weight: bold;">Tambahan:</span>
        </div>
        <ul style="margin: 0; padding: 0; list-style-type: none;">
            @foreach ($transaction->manualTransactionDetails->addons as $addon)
                <li style="display: flex; justify-content: space-between; margin-bottom: 2px; margin-left: 10px;">
                    <span>- {{ $addon->name ?? 'N/A' }}</span>
                    <span>Rp{{ number_format($addon->price ?? 0, 0, ',', '.') }}</span>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($config['show_notes'] && $transaction->type == 'manual' && ($transaction->manualTransactionDetails->notes ?? false))
        <div style="display: flex; flex-direction: column; margin-top: 10px;">
            <span style="font-weight: bold; margin-bottom: 3px;">Catatan:</span>
            <span style="text-align: justify;">{{ $transaction->manualTransactionDetails->notes }}</span>
        </div>
    @endif

    {{-- Line separator before Total --}}
    <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>

    {{-- Total --}}
    <div style="display: flex; justify-content: space-between; margin-bottom: 3px; font-weight: bold;">
        <span>TOTAL:</span>
        <span>Rp{{ number_format($totalAmountBeforePaid, 0, ',', '.') }}</span>
    </div>



    {{-- Line separator before datetime, if both service/amount and datetime are present --}}
    @if (($hasServiceDetails || ( $hasServiceDetails)) && $hasDateTime)
        <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
    @endif

    @if ($config['show_datetime'])
        <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
            <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Tanggal:</span>
            <span
                style="text-align: right; flex-grow: 1;">{{ \Carbon\Carbon::parse($transaction->created_at)->format('d-m-Y H:i') }}
                {{ strtoupper($transaction->timezone) }}</span>
        </div>
    @endif

    {{-- Line separator before QR Code, if datetime/service/amount and QR are present --}}
    @if (($hasDateTime || $hasServiceDetails || ($hasServiceDetails)) && $hasQrCode)
        <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
    @endif


    {{-- Line separator before footer messages, if QR/datetime/service/amount and footer messages are present --}}
    @if (
        ($hasQrCode || $hasDateTime || $hasServiceDetails || ($hasServiceDetails)) &&
            $hasFooterMessage)
        <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
    @endif

    <div style="text-align: center; font-size: inherit;">
        @if (!empty($config['thank_you_message']))
            <p style="margin: 5px 0;">{{ $config['thank_you_message'] }}</p>
        @endif
        @if (!empty($config['instruction_message']))
            <p style="margin: 5px 0;">{{ $config['instruction_message'] }}</p>
        @endif
    </div>

    {{-- NEW: QR Code Section --}}
    @if ($config['show_qr_code'])
        <div style="text-align: center; margin: 10px 0;">
            {{-- Ini adalah placeholder untuk QR Code. Dalam aplikasi nyata, Anda akan generate QR code di sini. --}}
            {{-- Contoh menggunakan gambar placeholder: --}}
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode('Invoice ID: ' . $transaction->order_id . ' | Total: Rp' . number_format($totalAmountBeforePaid, 0, ',', '.')) }}"
                alt="QR Code" style="width: 100px; height: 100px; display: block; margin: 0 auto;">
            {{-- Atau cukup kotak dummy seperti di preview --}}
            {{-- <div style="width: 100px; height: 100px; border: 1px dashed #888; margin: 0 auto; display: flex; justify-content: center; align-items: center; font-size: 0.7em; color: #555; text-align: center;">QR Code</div> --}}
            <p style="font-size: 0.8em; margin-top: 5px; color: #555;">Scan untuk detail transaksi</p>
        </div>
    @endif

</div>

<button class="btn btn-sm btn-secondary" onclick="printInvoice('{{ $transaction->id }}')" title="Cetak Nota">
    <i class="fa fa-print"></i>
</button>

@push('scripts')
    <script>
        function printInvoice(transactionId) {
            const printContents = document.getElementById('invoice-content-' + transactionId).innerHTML;
            const printWindow = window.open('', '_blank');

            printWindow.document.open();
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Nota Transaksi</title>
                    <style>
                        body { margin: 0; padding: 0; }
                        div#invoice-content-${transactionId} {
                            width: 300px;
                            font-family: 'Courier New', Courier, monospace;
                            font-size: {{ $config['font_size'] }};
                            padding: 10px;
                            margin: auto;
                            color: #000;
                        }
                        h4 { margin: 0; }
                        p { margin: 5px 0; }
                        ul { margin: 0; padding: 0; list-style-type: none; }
                        ul li { margin-bottom: 2px; }
                        .text-center { text-align: center; }
                        .text-left { text-align: left; }
                        .text-justify { text-align: justify; }
                        .dashed-line { border-top: 1px dashed #000; margin: 10px 0; }
                        small { font-size: inherit; display: block; }
                        .receipt-item-line {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 3px;
                            align-items: flex-start;
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
                        .receipt-item-line.justify-text .value {
                            text-align: justify;
                        }
                        /* Header specific styles for inline/left aligned */
                        .header-inline-container {
                            display: flex;
                            align-items: flex-start;
                            margin-bottom: 10px;
                        }
                        .header-inline-container img {
                            height: 25px;
                            width: auto;
                            margin-right: 5px;
                            object-fit: contain;
                        }
                        .header-text-content {
                            display: flex;
                            flex-direction: column;
                            justify-content: center;
                        }
                        .header-text-content h4,
                        .header-text-content small {
                            margin: 0;
                        }
                        .qr-code-box {
                            width: 100px;
                            height: 100px;
                            border: 1px dashed #888;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            margin: 10px auto;
                            background-color: #f0f0f0;
                            font-size: 0.7em;
                            color: #555;
                            text-align: center;
                        }
                        @media print {
                            body {
                                -webkit-print-color-adjust: exact;
                                print-color-adjust: exact;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div id="invoice-content-${transactionId}">
                        ${printContents}
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }
    </script>
@endpush
