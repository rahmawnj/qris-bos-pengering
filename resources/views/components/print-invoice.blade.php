{{-- resources/views/components/invoice.blade.php --}}

@props(['transaction'])

@php
    // Ambil konfigurasi struk dari owner terkait transaksi
    // Pastikan $transaction->owner dan $transaction->owner->receipt_config ada
    $config = $transaction->owner->receipt_config ?? [];

    // Default konfigurasi jika tidak ada di database atau field yang hilang
    $defaultConfig = [
        'show_brand_name' => true,
        'show_brand_logo' => true,
        'show_outlet_address' => true,
        'show_outlet_phone' => true,
        'show_nota_id' => true,
        'show_customer_info' => true,
        'show_payment_method' => true,
        'show_cashier_name' => true,
        'show_amount_paid' => true,
        'show_datetime' => true,
        'show_notes' => true,
        'show_qr_code' => false,
        'show_addons' => true, // NEW default
        'show_service_type' => true, // NEW default
        'header_style' => 'centered',
        'font_size' => '12px',
        'logo_size' => 'normal', // NEW default
        'thank_you_message' => '-- Terima Kasih --',
        'instruction_message' => 'Nota ini wajib dibawa sebagai bukti transaksi.',
    ];

    // Gabungkan konfigurasi dari DB dengan default, untuk memastikan semua key ada
    $config = array_merge($defaultConfig, $config);

    // Pastikan nilai boolean sesuai
    foreach (
        [
            'show_brand_name',
            'show_brand_logo',
            'show_outlet_address',
            'show_outlet_phone',
            'show_nota_id',
            'show_customer_info',
            'show_payment_method',
            'show_cashier_name',
            'show_amount_paid',
            'show_datetime',
            'show_notes',
            'show_qr_code',
            'show_addons', // NEW
            'show_service_type', // NEW
        ]
        as $key
    ) {
        $config[$key] = (bool) ($config[$key] ?? false);
    }

    // Pastikan font_size valid
    if (!in_array($config['font_size'], ['10px', '11px', '12px', '13px', '14px'])) {
        $config['font_size'] = '25px';
    }
    // Pastikan header_style valid
    if (!in_array($config['header_style'], ['centered', 'left_aligned'])) {
        $config['header_style'] = 'centered';
    }
    // Pastikan logo_size valid
    if (!in_array($config['logo_size'], ['small', 'normal', 'large'])) {
        $config['logo_size'] = 'large';
    }

    // Hitung total harga layanan + addons untuk "TOTAL"
    $totalAddonsPrice = 0;
    $addonsData = [];
    if ($transaction->type == 'manual' && $transaction->manualTransaction && $transaction->manualTransaction->addons) {
        $addons = $transaction->manualTransaction->addons;
        if (is_string($addons)) {
            $addons = json_decode($addons, true);
        }
        if (is_array($addons)) {
            foreach ($addons as $addon) {
                $addonQty = (int) ($addon['qty'] ?? 1);
                $addonQty = max(1, $addonQty);
                $totalAddonsPrice += (float) ($addon['price'] ?? 0) * $addonQty;
                $addonsData[] = $addon; // Simpan data addons yang sudah di-parse
            }
        }
    }
    $totalAmountBeforePaid = ($transaction->service_price ?? 0) + $totalAddonsPrice;
    $amountPaid = $transaction->amount_paid ?? $totalAmountBeforePaid; // Jika amount_paid tidak ada, anggap sama dengan total

    // --- Helper untuk memeriksa apakah garis putus-putus perlu ditampilkan ---
    // (Logika disesuaikan agar mirip dengan JS preview)

    // Header Content Rendered
    $headerContentRendered =
        ($config['show_brand_logo'] && ($transaction->owner->brand_logo ?? false)) ||
        ($config['show_brand_name'] && ($transaction->owner->brand_name ?? false));

    // Transaction Info Rendered
    $transactionInfoRendered =
        ($config['show_nota_id'] && ($transaction->order_id ?? false)) ||
        ($config['show_customer_info'] &&
            (($transaction->type == 'member' && ($transaction->memberTransaction->member->user->name ?? false)) ||
                ($transaction->type == 'manual' && ($transaction->manualTransaction->customer_name ?? false)))); // Check for manual customer name
    // Payment Info Rendered
    $paymentInfoRendered =
        ($config['show_payment_method'] &&
            (($transaction->type == 'manual' && ($transaction->manualTransaction->payment_method ?? false)) ||
                $transaction->type == 'member')) || // Member juga punya metode pembayaran (saldo)
        ($config['show_cashier_name'] &&
            $transaction->type == 'manual' &&
            ($transaction->manualTransaction->cashier_name ?? false));

    // Service Details Active (selalu true jika service_type dan price selalu ada, tapi kita cek addons/notes)
    $hasAddonsContent = $config['show_addons'] && !empty($addonsData);
    $hasNotesContent =
        $config['show_notes'] && $transaction->type == 'manual' && ($transaction->manualTransaction->notes ?? false);
    $serviceDetailsActive = $config['show_service_type'] || $hasAddonsContent || $hasNotesContent;

    // Totals Active (Total Amount selalu ada, Amount Paid opsional)
    $totalsActive = true; // Total Amount selalu ditampilkan
    if (!$config['show_amount_paid']) {
        // Jika amount_paid tidak ditampilkan, barisnya tidak dihitung untuk penentuan garis
    }

    $dateTimeRendered = $config['show_datetime'];
    $qrCodeRendered = $config['show_qr_code'];
    $footerMessageRendered = !empty($config['thank_you_message']) || !empty($config['instruction_message']);

    // Tentukan visibilitas garis putus-putus
    $showHeaderLine =
        $headerContentRendered &&
        ($transactionInfoRendered ||
            $paymentInfoRendered ||
            $serviceDetailsActive ||
            $totalsActive ||
            $dateTimeRendered ||
            $qrCodeRendered ||
            $footerMessageRendered);
    $showPaymentLine =
        ($transactionInfoRendered || $paymentInfoRendered) &&
        ($serviceDetailsActive || $totalsActive || $dateTimeRendered || $qrCodeRendered || $footerMessageRendered);
    $showTotalSummaryLine = $serviceDetailsActive && $totalsActive;
    $showDetailLine =
        ($serviceDetailsActive || $totalsActive) && ($dateTimeRendered || $qrCodeRendered || $footerMessageRendered);
    $showDatetimeLine = $dateTimeRendered && ($qrCodeRendered || $footerMessageRendered);

    // Tentukan ukuran piksel logo berdasarkan pilihan logo_size
    $logoPxSize = '140px'; // Default
    switch ($config['logo_size']) {
        case 'small':
            $logoPxSize = '100px';
            break;
        case 'normal':
            $logoPxSize = '120px';
            break;
        case 'large':
            $logoPxSize = '140px';
            break;
    }

    // Placeholder logo jika tidak ada brand_logo
    $brandLogoSrc = $transaction->owner->brand_logo
        ? asset('storage/' . $transaction->owner->brand_logo)
        : 'https://placehold.co/50x50/cccccc/000000?text=LOGO';
@endphp

<a href="#modal-print{{ $transaction->id }}" class="btn btn-primary" data-bs-toggle="modal">
    <i class="fa fa-print"></i>
</a>
<div class="modal fade" id="modal-print{{ $transaction->id }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Print </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body">
                <div id="invoice-content-{{ $transaction->id }}" class="display: none;">

                    {{-- Header Section --}}
                    <div
                        style="margin: 20px 0;  {{ $config['header_style'] == 'centered' ? 'text-align: center;' : 'text-align: left;' }}">
                        @if ($config['header_style'] == 'centered')
                            @if ($config['show_brand_logo'] && ($transaction->owner->brand_logo ?? false))
                                <img src="{{ $brandLogoSrc }}" alt="Brand Logo"
                                    style="width: {{ $logoPxSize }}; height: {{ $logoPxSize }}; object-fit: contain; margin: 0 auto 5px auto; display: block;">
                            @endif
                            @if ($config['show_brand_name'])
                                <h1 style="margin: 0; font-size: 1.6em; font-weight: 700;">
                                    {{ strtoupper($transaction->owner->brand_name ?? 'N/A') }}
                                </h1>
                            @endif
                        @else
                            {{-- left_aligned --}}
                            <div
                                style="display: flex; align-items: flex-start; margin-bottom: 5px; justify-content: flex-start;">
                                @if ($config['show_brand_logo'] && ($transaction->owner->brand_logo ?? false))
                                    <img src="{{ $brandLogoSrc }}" alt="Brand Logo"
                                        style="height: {{ $logoPxSize }}; width: auto; object-fit: contain; margin-right: 5px;">
                                @endif
                                <div style="display: flex; flex-direction: column; justify-content: center; font-size:25px;">
                                    @if ($config['show_brand_name'])
                                        <h1 style="margin: 0; font-size: 1.6em; font-weight: 700;">
                                            {{ strtoupper($transaction->owner->brand_name ?? 'N/A') }}
                                        </h1>
                                    @endif
                                    {{-- Address and Phone for left-aligned header should be inside this div --}}
                                    @if ($config['show_outlet_address'] && ($transaction->outlet->address ?? false))
                                        <h1
                                            style="font-size: 1.1em; display: block; text-align: left;">{{ $transaction->outlet->address }}</h1>
                                    @endif
                                    @if ($config['show_outlet_phone'] && ($transaction->outlet->phone_number ?? false))
                                        <h1
                                            style="font-size: 1.1em; display: block; text-align: left;">{{ $transaction->outlet->phone_number }}</h1>
                                    @endif
                                </div>
                            </div>
                        @endif
                        {{-- Address and Phone for centered header, outside the flex container --}}
                        @if ($config['show_brand_name'] && $config['header_style'] == 'centered')
                            @if ($config['show_outlet_address'] && ($transaction->outlet->address ?? false))
                                <small
                                    style="font-size: 1.1em; display: block;">{{ $transaction->outlet->address }}</small>
                            @endif
                            @if ($config['show_outlet_phone'] && ($transaction->outlet->phone_number ?? false))
                                <small
                                    style="font-size: 1.1em; display: block;">{{ $transaction->outlet->phone_number }}</small>
                            @endif
                        @endif
                    </div>

                    {{-- Line separator for header --}}
                    @if ($showHeaderLine)
                        <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
                    @endif

                    {{-- NEW: Wrapper for Body Content (Font Size Applied Here) --}}
                    <div style="font-size: {{ $config['font_size'] }};">
                        {{-- Transaction Details --}}
                        @if ($config['show_nota_id'])
                            <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Nota:</span>
                                <span
                                    style="text-align: right; flex-grow: 1;">{{ $transaction->order_id ?? 'N/A' }}</span>
                            </div>
                        @endif

                        {{-- Customer Info (Hanya jika tipe member dan diaktifkan di konfigurasi) --}}
                        @if ($config['show_customer_info'])
                            @php
                                $customerName = null;
                                $customerPhone = null;
                                $customerEmail = null;

                                if (
                                    $transaction->type == 'member' &&
                                    $transaction->memberTransaction &&
                                    $transaction->memberTransaction->member &&
                                    $transaction->memberTransaction->member->user
                                ) {
                                    $customerName = $transaction->memberTransaction->member->user->name;
                                    $customerPhone =
                                        $transaction->memberTransaction->member->user->phone_number ?? null;
                                    $customerEmail = $transaction->memberTransaction->member->user->email;
                                } elseif ($transaction->type == 'manual' && $transaction->manualTransaction) {
                                    // Ambil dari manualTransaction (membutuhkan kolom customer_name & customer_phone di tabel manual_transaction_details)
                                    $customerName = $transaction->manualTransaction->customer_name;
                                    $customerPhone = $transaction->manualTransaction->customer_phone_number;
                                    // Email mungkin tidak tersedia untuk transaksi manual walk-in
                                }
                                // Anda bisa menambahkan logika untuk tipe 'self_service' di sini jika diperlukan,
                                // misalnya jika self_service juga memiliki relasi customer langsung ke tabel customers.
                                // elseif ($transaction->customer) {
                                //     $customerName = $transaction->customer->name;
                                //     $customerPhone = $transaction->customer->phone_number;
                                //     $customerEmail = $transaction->customer->email;
                                // }
                            @endphp

                            @if ($customerName)
                                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                    <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Customer
                                        :</span>
                                    <span style="text-align: right; flex-grow: 1;">{{ $customerName }}</span>
                                </div>
                            @endif
                            @if ($customerPhone)
                                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                    <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Telepon
                                        :</span>
                                    <span style="text-align: right; flex-grow: 1;">{{ $customerPhone }}</span>
                                </div>
                            @endif
                            @if ($customerEmail)
                                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                    <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Email :</span>
                                    <span style="text-align: right; flex-grow: 1;">{{ $customerEmail }}</span>
                                </div>
                            @endif
                        @endif


                        {{-- Payment Method and Cashier Name --}}
                        @if ($config['show_payment_method'])
                            <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Metode
                                    Pembayaran:</span>
                                <span style="text-align: right; flex-grow: 1;">
                                    @if ($transaction->type == 'manual' && ($transaction->manualTransaction->payment_method ?? false))
                                        @if ($transaction->manualTransaction->payment_method == 'cash')
                                            Tunai
                                        @elseif ($transaction->manualTransaction->payment_method == 'non_cash')
                                            Non-Tunai
                                        @else
                                            -
                                        @endif
                                    @elseif ($transaction->type == 'member')
                                        Saldo Member
                                    @else
                                        QRIS
                                    @endif
                                </span>
                            </div>
                        @endif

                        {{-- PERBAIKAN: Pastikan baris kasir muncul jika konfigurasi aktif dan tipe manual, bahkan jika nama kasir kosong --}}
                        @if ($config['show_cashier_name'] && $transaction->type == 'manual')
                            <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Kasir:</span>
                                <span
                                    style="text-align: right; flex-grow: 1;">{{ $transaction->manualTransaction->cashier_name ?? '-' }}</span>
                            </div>
                        @endif

                        {{-- Line separator after payment info --}}
                        @if ($showTotalSummaryLine)
                            <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
                        @endif

                        {{-- Service and Addons Details --}}
                        @if ($config['show_service_type'] && $transaction->type == 'manual')
                            {{-- Service Type & Price (Always Shown) --}}
                            <div style="display: flex; justify-content: flex-start; margin-bottom: 5px;">
                                <span style="font-weight: bold;">Nama Produk/Layanan:</span>
                            </div>

                            {{-- Service Type & Price (Always Shown) --}}
                            <div
                                style="display: flex; justify-content: space-between; margin-bottom: 3px; margin-left: 10px;">
                                <span>-
                                    {{ ucfirst(str_replace('_', ' ', $transaction->manualTransaction->service->name ?? 'N/A')) }}
                                    @if (!empty($transaction->manualTransaction->quantity))
                                        ({{ $transaction->manualTransaction->quantity }}
                                        {{ $transaction->manualTransaction->unit ?? 'Unit' }})
                                    @endif
                                </span>
                                <span
                                    style="text-align: right; flex-grow: 1;">Rp{{ number_format($transaction->manualTransaction->service_price ?? 0, 0, ',', '.') }}</span>
                            </div>
                        @else
                            <div style="display: flex; justify-content: flex-start; margin-bottom: 5px;">
                                <span style="font-weight: bold;">Mesin:</span>
                            </div>

                            {{-- Service Type & Price (Always Shown) --}}
                            <div
                                style="display: flex; justify-content: space-between; margin-bottom: 3px; margin-left: 10px;">
                                <span>{{ $transaction->deviceTransactions->first()->service_type ?? 'N/A' }}</span>
                            </div>
                        @endif

                        {{-- Addons (Only if manual transaction and addons exist and configured to show) --}}
                        @if ($config['show_addons'] && !empty($addonsData))
                            <div
                                style="display: flex; justify-content: flex-start; margin-bottom: 3px; margin-top: 5px;">
                                <span style="font-weight: bold;">Tambahan:</span>
                            </div>
                            <ul style="margin: 0; padding: 0; list-style-type: none;">
                                @foreach ($addonsData as $addon)
                                    @php
                                        $addonQty = (int) ($addon['qty'] ?? 1);
                                        $addonQty = max(1, $addonQty);
                                        $addonTotal = (float) ($addon['price'] ?? 0) * $addonQty;
                                    @endphp
                                    <li
                                        style="display: flex; justify-content: space-between; margin-bottom: 2px; margin-left: 10px;">
                                        <span>- {{ $addon['name'] ?? 'N/A' }} ({{ $addonQty }}x)</span>
                                        <span>Rp{{ number_format($addonTotal, 0, ',', '.') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        {{-- Notes (Only if manual transaction and notes exist and configured to show) --}}
                        @if ($hasNotesContent)
                            <div style="display: flex; flex-direction: column; margin-top: 10px;">
                                <span style="font-weight: bold; margin-bottom: 3px;">Catatan:</span>
                                <span style="text-align: left; white-space: pre-wrap;">{{ $transaction->manualTransaction->notes }}</span>
                            </div>
                        @endif

                        {{-- Estimasi Selesai (khusus manual/drop off) --}}
                        @if ($transaction->type == 'manual' && $transaction->manualTransaction?->estimated_completion_at)
                            <div style="display: flex; justify-content: space-between; margin-top: 6px;">
                                <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Estimasi Selesai:</span>
                                <span style="text-align: right; flex-grow: 1;">
                                    {{ \Carbon\Carbon::parse($transaction->manualTransaction->estimated_completion_at)->format('d-m-Y H:i') }}
                                </span>
                            </div>
                        @endif

                        {{-- Line separator before Total --}}
                        @if ($showDetailLine)
                            <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
                        @endif

                        {{-- Total --}}
                        <div
                            style="display: flex; justify-content: space-between; margin-bottom: 3px; font-weight: bold;">
                            <span>TOTAL:</span>
                            <span>Rp{{ number_format($transaction->amount, 0, ',', '.') }}</span>
                        </div>


                        {{-- Line separator before datetime --}}
                        @if ($showDatetimeLine)
                            <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
                        @endif

                        {{-- Date & Time --}}
                        @if ($config['show_datetime'])
                            <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Tanggal:</span>
                                <span style="text-align: right; flex-grow: 1;">
                                    {{ \Carbon\Carbon::parse($transaction->created_at)->format('d-m-Y H:i') }}
                                    {{ strtoupper($transaction->timezone ?? 'WIB') }}
                                </span>
                            </div>
                        @endif

                        {{-- Line separator before QR Code --}}
                        @if ($qrCodeRendered && ($dateTimeRendered || $serviceDetailsActive || $totalsActive))
                            <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
                        @endif

                        {{-- QR Code Section --}}
                        @if ($config['show_qr_code'])
                            <div style="text-align: center; margin: 25px auto;">
                                {{-- Menggunakan API QR Server. Ganti dengan generator QR lokal jika memungkinkan untuk produksi. --}}
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode(route('home.transaction', ['order_id' => $transaction->order_id])) }}"
                                    alt="QR Code" style="width: 200px; height: 200px; display: block; margin: 0 auto;">
                                <p style="font-size: 0.8em; margin-top: 5px; color: #555;">Scan untuk detail transaksi
                                </p>
                            </div>
                        @endif
                    </div>
                    {{-- END NEW: Wrapper for Body Content --}}

                    {{-- Line separator before footer messages --}}
                    @if ($footerMessageRendered && ($qrCodeRendered || $dateTimeRendered || $serviceDetailsActive || $totalsActive))
                        <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
                    @endif

                    {{-- Footer Messages --}}
                    <div style="text-align: center; font-size: inherit; font-size:20px;">
                        @if (!empty($config['thank_you_message']))
                            <p style="margin: 10px 0;">{{ $config['thank_you_message'] }}</p>
                        @endif
                        @if (!empty($config['instruction_message']))
                            <p style="margin: 1px 0;">{{ $config['instruction_message'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div id="loading-indicator" style="display: none; text-align: center; margin-top: 20px;">
    <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
    </div>
    <p>Sedang membuat PDF, mohon tunggu...</p>
</div>
                <a href="javascript:;" class="btn btn-white" data-bs-dismiss="modal">Close</a>
                <button class="btn btn-sm btn-secondary" onclick="printInvoice('{{ $transaction->id }}')"
                    title="Cetak Nota">
                    Print
                </button>
                <button class="btn btn-sm btn-primary" onclick="downloadInvoicePdf('{{ $transaction->id }}')"
                    title="Download PDF">
                    Cetak PDF
                </button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    {{-- Memuat pustaka html2canvas untuk mengubah HTML menjadi canvas --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" integrity="sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/3.0.1/jspdf.umd.min.js" integrity="sha512-ad3j5/L4h648YM/KObaUfjCsZRBP9sAOmpjaT2BDx6u9aBrKFp7SbeHykruy83rxfmG42+5QqeL/ngcojglbJw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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
                        /* Gaya dasar untuk struk */
                        body {
                            font-family: 'Courier New', Courier, monospace;
                            margin: 0;
                            padding: 0;
                            color: #000;
                        }
                        #invoice-content-print {
                            width: 80mm;
                            padding: 0;
                            box-sizing: border-box;
                            margin: 0;
                        }

                        #invoice-content-print h4,
                        #invoice-content-print p,
                        #invoice-content-print ul {
                            margin: 0;
                            padding: 0;
                        }
                        #invoice-content-print ul li {
                            margin-left: 0;
                            list-style-type: none;
                        }

                        #invoice-content-print .text-center { text-align: center; }
                        #invoice-content-print .text-left { text-align: left; }
                        #invoice-content-print .text-justify { text-align: justify; }
                        #invoice-content-print .dashed-line {
                            border-top: 1px dashed #000;
                            margin: 10px 0;
                        }

                        #invoice-content-print small {
                            font-size: inherit;
                        }
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

                        .header-inline-container {
                            display: flex;
                            align-items: flex-start;
                            margin-bottom: 10px;
                            justify-content: flex-start;
                        }
                        .header-inline-container img {
                            object-fit: contain;
                            margin-right: 5px;
                            display: block;
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
                            font-size: 1.5em;
                            color: #555;
                            text-align: center;
                        }

                        @media print {
                            body {
                                margin: 0;
                                padding: 0;
                            }
                            #invoice-content-print {
                                width: 80mm;
                                padding: 0;
                                margin: 0;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div id="invoice-content-print">
                        ${printContents}
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();

            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            };
            setTimeout(() => {
                if (!printWindow.closed) {
                    printWindow.focus();
                    printWindow.print();
                    printWindow.close();
                }
            }, 1500);
        }

        // Fungsi yang sudah diperbaiki
        async function downloadInvoicePdf(transactionId) {
            // 1. Tampilkan indikator loading
            const loadingIndicator = document.getElementById('loading-indicator');
            loadingIndicator.style.display = 'block';

            const invoiceElement = document.getElementById('invoice-content-' + transactionId);
            const invoiceFilename = 'invoice_' + transactionId + '.pdf';

            try {
                // Menggunakan html2canvas untuk mengubah elemen HTML menjadi canvas
                const canvas = await html2canvas(invoiceElement, {
                    scale: 2,
                    useCORS: true
                });

                const imageData = canvas.toDataURL('image/jpeg', 1.0);

                const receiptWidthMm = 80;
                const paddingMm = 5;

                const pdfWidth = receiptWidthMm - (paddingMm * 2);
                const canvasWidth = canvas.width;
                const canvasHeight = canvas.height;
                const pdfHeight = (pdfWidth / canvasWidth) * canvasHeight;

                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: [receiptWidthMm, pdfHeight + (paddingMm * 2)]
                });

                pdf.addImage(imageData, 'JPEG', paddingMm, paddingMm, pdfWidth, pdfHeight);

                // Mengunduh file PDF
                pdf.save(invoiceFilename);
            } catch (error) {
                console.error('Gagal membuat PDF:', error);
                alert('Terjadi kesalahan saat membuat PDF. Mohon coba lagi.');
            } finally {
                // 2. Sembunyikan indikator loading, baik berhasil atau gagal
                loadingIndicator.style.display = 'none';
            }
        }
    </script>
@endpush

@push('styles')
    {{-- No specific styles pushed here, all moved to the print window --}}
@endpush
