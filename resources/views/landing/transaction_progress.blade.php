<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Progres Layanan - {{ $transaction->order_id ?? 'Laundry' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #0066ff;
            --secondary-blue: #0052cc;
            --bg-light: #f4f7fa;
            --text-dark: #2d3748;
            --text-muted: #718096;
            --border-color: #e2e8f0;
            --success-green: #10b981;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Outfit', sans-serif;
            color: var(--text-dark);
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        .main-container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            min-height: 100vh;
            box-shadow: 0 0 40px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
        }

        /* Header Premium */
        .page-header {
            background: linear-gradient(135deg, #0061ff 0%, #0045cc 100%);
            padding: 30px 25px;
            color: white;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            border-bottom-left-radius: 5px;
            border-bottom-right-radius: 5px;
        }

        .header-icon-wrap {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .header-icon-wrap i {
            font-size: 30px;
            background: linear-gradient(135deg, #ff9900, #ff6600);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Alternatif icon: washer/logo laundry */
        .header-logo-svg {
            width: 40px;
            height: 40px;
        }

        .header-content h1 {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .header-content p {
            font-size: 13px;
            margin: 5px 0 0;
            opacity: 0.9;
            font-weight: 400;
            line-height: 1.4;
        }

        /* Abstract patterns in header */
        .page-header::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }

        /* Outlet Section */
        .outlet-info-card {
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid var(--border-color);
        }

        .outlet-details h2 {
            font-size: 18px;
            font-weight: 800;
            margin: 0 0 8px 0;
            color: #1a202c;
            text-transform: uppercase;
        }

        .outlet-address {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.6;
            max-width: 280px;
            margin: 0;
            display: flex;
            align-items: flex-start;
        }

        .outlet-address i {
            margin-top: 3px;
            margin-right: 8px;
            color: var(--primary-blue);
        }

        .btn-call {
            background: #f0f5ff;
            color: #0066ff;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }

        .btn-call:hover {
            background: #e1ebff;
            transform: translateY(-1px);
        }

        .btn-call i {
            margin-right: 8px;
        }

        /* Content Sections */
        .content-body {
            padding: 20px;
            background-color: #fafbfc;
            flex-grow: 1;
        }

        .section-card {
            background: #fff;
            border: 1px solid #edf2f7;
            border-radius: 12px;
            margin-bottom: 18px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: white;
            font-size: 16px;
        }

        .bg-blue { background-color: #0066ff; }
        .bg-green { background-color: #10b981; }
        .bg-purple { background-color: #8b5cf6; }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
            color: #1a202c;
        }

        /* Data List */
        .data-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f7fafc;
            font-size: 14px;
        }

        .data-row:last-child {
            border-bottom: none;
        }

        .data-label {
            color: var(--text-muted);
            font-weight: 500;
        }

        .data-value {
            color: var(--text-dark);
            font-weight: 600;
            text-align: right;
        }

        .text-red { color: #ef4444; }
        .text-green { color: #10b981; }

        /* Addons List */
        .addons-list {
            background: #f8fafc;
            border-radius: 8px;
            padding: 12px;
            margin: 10px 0;
        }

        .addon-item {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            padding: 4px 0;
            color: #4a5568;
        }

        /* Progress Steps */
        .progress-wrapper {
            position: relative;
            padding: 30px 10px 10px;
        }

        .progress-line {
            position: absolute;
            top: 50px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: #e2e8f0;
            z-index: 1;
        }

        .progress-line-active {
            position: absolute;
            top: 50px;
            left: 10%;
            height: 2px;
            background: var(--primary-blue);
            z-index: 2;
            transition: width 0.5s ease;
        }

        .steps-container {
            display: flex;
            justify-content: space-between;
            position: relative;
            z-index: 3;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 25%;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            transition: all 0.3s;
            color: #cbd5e0;
            font-size: 16px;
        }

        .step-item.active .step-circle {
            background: var(--primary-blue);
            border-color: var(--primary-blue);
            color: #fff;
            box-shadow: 0 0 0 5px rgba(0, 102, 255, 0.15);
            transform: scale(1.15);
        }

        .step-item.completed .step-circle {
            background: #fff;
            border-color: var(--primary-blue);
            color: var(--primary-blue);
        }

        .step-label {
            font-size: 11px;
            font-weight: 600;
            color: #a0aec0;
            text-align: center;
            white-space: nowrap;
        }

        .step-item.active .step-label,
        .step-item.completed .step-label {
            color: var(--text-dark);
        }

        /* Footer */
        .page-footer {
            padding: 25px;
            text-align: center;
            border-top: 1px solid var(--border-color);
            font-size: 12px;
            color: var(--text-muted);
            background: #fff;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 10px;
        }

        .footer-brand i {
            color: var(--primary-blue);
            margin-right: 6px;
            font-size: 14px;
        }

        .thank-you-box {
            background: #f0f7ff;
            border: 1px solid #d1e9ff;
            border-radius: 10px;
            padding: 12px;
            text-align: center;
            font-size: 13px;
            color: #0056b3;
            margin-top: 20px;
        }
        
        .thank-you-box i {
            margin-right: 6px;
            color: #007bff;
        }

        @media (max-width: 480px) {
            .main-container {
                box-shadow: none;
            }
            .header-icon-wrap {
                width: 60px;
                height: 60px;
            }
            .header-content h1 {
                font-size: 20px;
            }
            .step-label {
                font-size: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        {{-- Header Section --}}
        <header class="page-header">
            <div class="header-icon-wrap">
                <svg class="header-logo-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.477 2 2 6.477 2 12C2 17.523 6.477 22 12 22C17.523 22 22 17.523 22 12C22 6.477 17.523 2 12 2ZM12 20C7.589 20 4 16.411 4 12C4 7.589 7.589 4 12 4C16.411 4 20 7.589 20 12C20 16.411 16.411 20 12 20Z" fill="#ff9900"/>
                    <path d="M12 6C8.686 6 6 8.686 6 12C6 15.314 8.686 18 12 18C15.314 18 18 15.314 18 12C18 8.686 15.314 6 12 6ZM12 16C9.791 16 8 14.209 8 12C8 9.791 9.791 8 12 8C14.209 8 16 9.791 16 12C16 14.209 14.209 16 12 16Z" fill="#ff6600"/>
                </svg>
            </div>
            <div class="header-content">
                <h1>Progres Layanan</h1>
                <p>Terima kasih telah mempercayakan cucian Anda kepada kami.</p>
            </div>
        </header>

        {{-- Outlet Info Section --}}
        <section class="outlet-info-card">
            <div class="outlet-details">
                <h2>{{ $transaction->owner->brand_name ?? 'LAUNDRY HUB' }}</h2>
                <p class="outlet-address">
                    <i class="fas fa-location-dot"></i>
                    <span>{{ $transaction->outlet->address ?? 'Alamat Outlet' }}</span>
                </p>
            </div>
            @if($transaction->outlet->phone_number)
                <a href="tel:{{ $transaction->outlet->phone_number }}" class="btn-call">
                    <i class="fas fa-phone-alt"></i>
                    {{ $transaction->outlet->phone_number }}
                </a>
            @endif
        </section>

        {{-- Main Content --}}
        <main class="content-body">
            
            {{-- Detail Transaksi --}}
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon bg-blue">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h3 class="section-title">Detail Transaksi</h3>
                </div>
                
                <div class="data-row">
                    <span class="data-label">Order ID</span>
                    <span class="data-value">{{ $transaction->order_id }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Tanggal Transaksi</span>
                    <span class="data-value">{{ \Carbon\Carbon::parse($transaction->created_at)->format('d M Y H:i') }} WIB</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Jenis Transaksi</span>
                    <span class="data-value">{{ ucfirst($transaction->type) }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Status Pembayaran</span>
                    <span class="data-value {{ $transaction->status === 'success' ? 'text-green' : 'text-red' }}">
                        {{ ucfirst($transaction->status) }}
                    </span>
                </div>
                @if($transaction->type !== 'qris')
                <div class="data-row">
                    <span class="data-label">Nama Pelanggan</span>
                    <span class="data-value">{{ $transaction->customer_display_name }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Telepon Pelanggan</span>
                    <span class="data-value">{{ $transaction->customer_display_phone }}</span>
                </div>
                @endif
                @if($transaction->type !== 'qris' && $transaction->estimated_completion_display_at)
                <div class="data-row">
                    <span class="data-label">Estimasi Selesai</span>
                    <span class="data-value text-red">{{ \Carbon\Carbon::parse($transaction->estimated_completion_display_at)->format('d M Y H:i') }} WIB</span>
                </div>
                @endif
            </div>

            {{-- Informasi Kontak Outlet --}}
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon bg-green">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <h3 class="section-title">Informasi Kontak Outlet</h3>
                </div>
                <div class="data-row">
                    <span class="data-label">Alamat Outlet</span>
                    <span class="data-value">{{ $transaction->outlet->address ?? '-' }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Telepon Outlet</span>
                    <span class="data-value">{{ $transaction->outlet->phone_number ?? '-' }}</span>
                </div>
            </div>

            {{-- Detail Layanan --}}
            @if($transaction->type !== 'qris')
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon bg-purple">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                    <h3 class="section-title">Detail Layanan</h3>
                </div>
                
                @if($transaction->type == 'manual' && $transaction->manualTransaction)
                    @php
                        $mt = $transaction->manualTransaction;
                        $serviceName = $mt->service->name ?? 'Layanan';
                        $qty = $mt->quantity ?? 1;
                        $unit = $mt->unit ?? 'unit';
                        $price = $mt->service_price ?? 0;
                    @endphp
                    <div class="data-row">
                        <span class="data-label">Layanan</span>
                        <span class="data-value">{{ $serviceName }} ({{ $qty }} {{ $unit }})</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Harga Layanan</span>
                        <span class="data-value">Rp {{ number_format($price * $qty, 0, ',', '.') }}</span>
                    </div>

                    @if(!empty($transaction->addons_data))
                    <div class="addons-list">
                        <div style="font-size: 12px; font-weight: 700; color: #4a5568; margin-bottom: 5px;">Tambahan:</div>
                        @foreach($transaction->addons_data as $addon)
                            @php
                                $aq = max(1, (int)($addon['qty'] ?? 1));
                                $ap = (float)($addon['price'] ?? 0);
                            @endphp
                            <div class="addon-item">
                                <span>• {{ $addon['name'] }} ({{ $aq }}x)</span>
                                <span>Rp {{ number_format($ap * $aq, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                    @endif

                    <div class="data-row mt-2">
                        <span class="data-label" style="font-weight: 700; color: #2d3748;">Total Pembayaran</span>
                        <span class="data-value text-green" style="font-weight: 800; font-size: 16px;">Rp {{ number_format($transaction->total_amount_before_paid, 0, ',', '.') }}</span>
                    </div>
                @elseif($transaction->type == 'member' && $transaction->memberTransaction)
                    <div class="data-row">
                        <span class="data-label">Metode</span>
                        <span class="data-value">Saldo Member</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Jumlah</span>
                        <span class="data-value text-green">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
            @endif

            {{-- Status Progres --}}
            @if($transaction->type === 'manual')
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon" style="background-color: #3182ce;">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3 class="section-title">Status Progres Layanan</h3>
                </div>

                <div class="progress-wrapper">
                    @php
                        $allStatuses = ['received', 'in_progress', 'ready_for_pickup', 'completed'];
                        $currentProgress = $transaction->manualTransaction->progress ?? 'received';
                        
                        // Status mapping
                        $labels = [
                            'received' => 'Pesanan Diterima',
                            'in_progress' => 'Sedang Diproses',
                            'ready_for_pickup' => 'Siap Diambil',
                            'completed' => 'Selesai'
                        ];
                        
                        $icons = [
                            'received' => 'fas fa-clipboard-list',
                            'in_progress' => 'fas fa-spinner',
                            'ready_for_pickup' => 'fas fa-box',
                            'completed' => 'fas fa-check'
                        ];

                        $currentIndex = array_search($currentProgress, $allStatuses);
                        if($currentIndex === false) $currentIndex = 0;
                        
                        $progressPercent = ($currentIndex / (count($allStatuses) - 1)) * 80;
                    @endphp

                    <div class="progress-line"></div>
                    <div class="progress-line-active" style="width: {{ 10 + $progressPercent }}%"></div>

                    <div class="steps-container">
                        @foreach($allStatuses as $index => $statusKey)
                            @php
                                $statusClass = '';
                                if($index < $currentIndex) $statusClass = 'completed';
                                elseif($index == $currentIndex) $statusClass = 'active';
                            @endphp
                            <div class="step-item {{ $statusClass }}">
                                <div class="step-circle">
                                    <i class="{{ $icons[$statusKey] }} {{ $index == $currentIndex && $statusKey == 'in_progress' ? 'fa-spin' : '' }}"></i>
                                </div>
                                <div class="step-label">{{ $labels[$statusKey] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="thank-you-box">
                    <i class="fas fa-heart"></i>
                    Terima kasih atas kepercayaan Anda.
                </div>
            </div>
            @endif
        </main>

        {{-- Footer --}}
        <footer class="page-footer">
            <div class="footer-brand">
                <i class="fas fa-shield-check"></i>
                &copy; {{ date('Y') }} {{ $transaction->owner->brand_name ?? 'Laundry' }}. Semua Hak Cipta Dilindungi.
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
