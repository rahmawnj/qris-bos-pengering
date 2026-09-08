@php
    $isManual = $transaction->type === 'manual';
    $isQris = $transaction->type === 'qris';
    $title = $isManual ? 'Detail Transaksi Manual' : 'Detail Transaksi QRIS';
    $subtitle = $isManual ? 'Rincian transaksi manual' : 'Rincian transaksi QRIS';
    $items = [($isAdminContext ?? true) ? 'Admin' : 'Partner', 'Transaksi', $title];
@endphp

@extends('layouts.dashboard.app')

@push('styles')
    <style>
	        :root {
	            --primary-blue: #0d6efd;
	            --soft-blue: #eef5ff;
	            --text-muted: #64748b;
	            --text-dark: #15213b;
	            --card-shadow: 0 8px 26px rgba(15, 23, 42, 0.08);
	            --border-radius: 8px;
	        }

        .trx-detail-container {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: transparent;
        }

        /* Header Overview Section */
	        .overview-wrapper {
	            display: grid;
	            grid-template-columns: minmax(280px, 1.65fr) repeat(3, minmax(180px, 1fr));
	            gap: 0;
	            margin-bottom: 24px;
	            background: #fff;
	            border: 1px solid #eef2f7;
	            border-radius: var(--border-radius);
	            box-shadow: var(--card-shadow);
	            overflow: hidden;
	        }

        @media (max-width: 1200px) {
            .overview-wrapper {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .overview-wrapper {
                grid-template-columns: 1fr;
            }
        }

	        .overview-card {
	            background: #fff;
	            padding: 18px 24px;
	            display: flex;
	            align-items: center;
	            min-height: 110px;
	            border-right: 1px solid #eef2f7;
	        }

	        .overview-card:last-child {
	            border-right: 0;
	        }

        .header-main-card {
            display: flex;
            align-items: center;
            gap: 15px;
        }

	        .icon-box-primary {
	            width: 70px;
	            height: 70px;
	            background: var(--soft-blue);
	            border-radius: 50%;
	            display: flex;
	            align-items: center;
	            justify-content: center;
	            color: var(--primary-blue);
	            font-size: 28px;
	            flex-shrink: 0;
	        }

	        .header-content h6 {
	            font-size: 12px;
	            color: var(--text-muted);
	            margin-bottom: 2px;
	            font-weight: 500;
	        }

	        .header-content h4 {
	            font-size: 18px;
	            font-weight: 700;
	            color: var(--text-dark);
	            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            word-break: break-all;
        }

	        .copy-btn {
            background: none;
            border: none;
            color: var(--primary-blue);
            cursor: pointer;
            padding: 4px;
            font-size: 14px;
            transition: opacity 0.2s;
        }

        .copy-btn:hover {
            opacity: 0.7;
        }

	        .info-stat-card {
	            padding: 16px 24px;
	            display: flex;
	            align-items: center;
	            gap: 15px;
	        }

	        .stat-icon {
	            width: 48px;
	            height: 48px;
	            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

	        .stat-icon.blue { background: #eef5ff; color: #3b82f6; }
	        .stat-icon.orange { background: #fff7ed; color: #f97316; }
	        .stat-icon.green { background: #ecfdf5; color: #16a34a; }

	        .stat-content h6 {
	            font-size: 11px;
	            text-transform: none;
	            letter-spacing: 0;
            color: var(--text-muted);
            margin: 0 0 2px 0;
            font-weight: 600;
        }

	        .stat-content .value {
	            font-size: 15px;
	            font-weight: 700;
	            color: var(--text-dark);
	        }

        /* Content Sections */
        .content-card {
            background: #fff;
	            border-radius: var(--border-radius);
	            box-shadow: var(--card-shadow);
	            border: 1px solid #eef2f7;
            height: 100%;
            overflow: hidden;
        }

        .card-header-premium {
	            padding: 20px 24px;
	            border-bottom: 1px solid #f1f4f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header-premium h5 {
	            font-size: 16px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header-premium h5 i {
            color: var(--primary-blue);
            font-size: 18px;
        }

        .card-body-premium {
	            padding: 16px 24px;
        }

        /* Data List Styling */
        .premium-data-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

	        .data-item {
	            display: grid;
	            grid-template-columns: 190px 1fr;
	            padding: 10px 0;
	            border-bottom: 1px dashed #e8edf5;
	        }

        .data-item:last-child { border-bottom: none; }

        .data-label {
	            font-size: 13px;
	            color: var(--text-muted);
	            display: flex;
	            align-items: center;
	            gap: 12px;
	        }

	        .data-label i {
	            width: 24px;
	            height: 24px;
	            border-radius: 5px;
	            background: #eef5ff;
	            display: inline-flex;
	            align-items: center;
	            justify-content: center;
	            text-align: center;
	            color: var(--primary-blue);
	            font-size: 12px;
	        }

	        .data-value {
	            font-size: 13px;
	            font-weight: 600;
	            color: var(--text-dark);
	            flex-grow: 1;
	            padding-left: 16px;
	            border-left: 1px dashed #e8edf5;
	        }

        /* Table Styling */
        .premium-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .premium-table th {
	            background: #f8fafc;
	            padding: 14px 20px;
	            font-size: 11px;
	            text-transform: none;
            font-weight: 700;
            color: #64748b;
            border-bottom: 1px solid #edf2f7;
        }

        .premium-table td {
	            padding: 16px 20px;
	            font-size: 13px;
            border-bottom: 1px solid #f8fafc;
            color: var(--text-dark);
            vertical-align: middle;
        }

        .empty-state {
	            padding: 58px 20px;
	            text-align: center;
	        }

	        .empty-icon {
	            width: 74px;
	            height: 74px;
	            background: transparent;
	            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
	            font-size: 44px;
	            color: #8ab6ff;
	            margin: 0 auto 12px;
	        }

        .empty-state h6 {
	            font-size: 14px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 13px;
            color: #94a3b8;
            margin: 0;
        }

	        .total-footer {
	            background: #f8fafc;
	            padding: 18px 24px;
	            display: flex;
	            justify-content: space-between;
	            align-items: center;
	            gap: 16px;
	        }

        .total-label {
            font-size: 13px;
            color: #64748b;
        }

	        .total-value {
	            font-size: 24px;
	            font-weight: 800;
	            color: var(--primary-blue);
	        }

	        .header-print-action {
	            display: inline-flex;
	            margin-left: 8px;
	        }

	        .header-print-action > .btn {
	            width: 44px;
	            height: 44px;
	            display: inline-flex;
	            align-items: center;
	            justify-content: center;
	            border-radius: 7px;
	            padding: 0;
	        }

        /* Badges */
        .badge-premium {
            padding: 6px 12px;
	            border-radius: 7px;
	            font-size: 11px;
	            font-weight: 700;
	            text-transform: none;
	        }

	        .badge-warning-soft { background: #fff7ed; color: #c2410c; }
	        .badge-success-soft { background: #f0fdf4; color: #15803d; }
	        .badge-danger-soft { background: #fef2f2; color: #b91c1c; }

	        .transaction-note {
	            margin: 0 24px 16px;
	            padding: 12px 16px;
	            border: 1px solid #cfe1ff;
	            background: #f6faff;
	            border-radius: 6px;
	            color: #334155;
	            font-size: 12px;
	        }

	        .header-actions .btn {
	            border-radius: 6px !important;
	            font-weight: 600;
	        }

	        @media (max-width: 1200px) {
	            .overview-wrapper {
	                grid-template-columns: 1fr 1fr;
	            }

	            .overview-card:nth-child(2) {
	                border-right: 0;
	            }

	            .overview-card:nth-child(-n+2) {
	                border-bottom: 1px solid #eef2f7;
	            }
	        }

	        @media (max-width: 768px) {
	            .overview-wrapper {
	                grid-template-columns: 1fr;
	            }

	            .overview-card {
	                border-right: 0;
	                border-bottom: 1px solid #eef2f7;
	            }

	            .overview-card:last-child {
	                border-bottom: 0;
	            }

	            .data-item {
	                grid-template-columns: 1fr;
	                gap: 6px;
	            }

	            .data-value {
	                border-left: 0;
	                padding-left: 36px;
	            }

	            .total-footer {
	                align-items: flex-start;
	                flex-direction: column;
	            }

	        }

	    </style>
@endpush

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    @php
        $timezoneMap = [
            'wib' => 'Asia/Jakarta',
            'wita' => 'Asia/Makassar',
            'wit' => 'Asia/Jayapura',
        ];
        $tzKey = strtolower($transaction->timezone);
        $tz = $timezoneMap[$tzKey] ?? 'Asia/Jakarta';

        $firstDeviceTransaction = $transaction->deviceTransactions->first();
        $manualDetail = $transaction->manualTransaction;
        $displayDeviceCode = $firstDeviceTransaction?->device_code
            ?? $transaction->qrisTransaction?->device_code
            ?? $transaction->device_code
            ?? 'Belum Lengkap';

	        $statusLabel = ucfirst($transaction->status);
	        $statusClass = 'badge-warning-soft';
	        if($transaction->status === 'success') $statusClass = 'badge-success-soft';
	        if($transaction->status === 'failed' || $transaction->status === 'expired') $statusClass = 'badge-danger-soft';
	        $isPaid = $transaction->status === 'success';
	    @endphp

    <div class="trx-detail-container">
        <!-- Header Overview Cards -->
        <div class="overview-wrapper">
            <div class="overview-card header-main-card">
                <div class="icon-box-primary">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div class="header-content">
                    <h6>Order ID</h6>
                    <h4>
                        {{ $transaction->order_id }}
                        <button class="copy-btn" onclick="copyToClipboard('{{ $transaction->order_id }}')" title="Salin Order ID">
                            <i class="far fa-copy"></i>
                        </button>
                    </h4>

                </div>
            </div>

            <div class="overview-card info-stat-card">
                <div class="stat-icon blue">
                    <i class="far fa-calendar-alt"></i>
                </div>
                <div class="stat-content">
                    <h6>Tanggal Transaksi</h6>
                    <div class="value">
	                        {{ $transaction->created_at->copy()->setTimezone($tz)->locale('id')->translatedFormat('d F Y') }}
                        <div class="small text-muted fw-normal" style="font-size: 11px;">{{ $transaction->created_at->setTimezone($tz)->format('H:i') }} {{ strtoupper($transaction->timezone) }}</div>
                    </div>
                </div>
            </div>

            <div class="overview-card info-stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-content">
                    <h6>Total Pembayaran</h6>
                    <div class="value">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</div>
                </div>
            </div>

	            <div class="overview-card info-stat-card">
	                <div class="stat-icon {{ $isPaid ? 'green' : 'orange' }}">
	                    <i class="fas {{ $isPaid ? 'fa-check-circle' : 'fa-info-circle' }}"></i>
	                </div>
	                <div class="stat-content">
	                    <h6>Status Pembayaran</h6>
	                    <div class="value">{{ $isPaid ? 'Sudah Dibayar' : 'Belum Dibayar' }}</div>
	                </div>
	            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-5 col-lg-6">
	                <div class="content-card">
	                    <div class="card-header-premium">
	                        <h5><i class="fas fa-file-invoice"></i> Data Transaksi Utama</h5>
	                        <div class="header-actions">
	                            @if ($isAdminContext && $isQris && $transaction->status === 'pending' && $transaction->qrisTransaction?->payment_url)
	                                <button class="btn btn-xs btn-primary btn-check-payment-gateway rounded-pill px-3"
	                                    data-provider="{{ $transaction->qris_provider_label }}"
	                                    data-check-url="{{ route('admin.transactions.check_payment_gateway', $transaction->id) }}">
	                                    <i class="fa fa-sync-alt me-1"></i> Cek Status Gateway
	                                </button>
	                            @endif
	                            <span class="header-print-action">
	                                <x-print-invoice :transaction="$transaction" />
	                            </span>
	                        </div>
	                    </div>
                    <div class="card-body-premium">
                        <ul class="premium-data-list">
                            <li class="data-item">
                                <div class="data-label"><i class="fas fa-hashtag"></i> Order ID</div>
                                <div class="data-value">{{ $transaction->order_id }}</div>
                            </li>
                            <li class="data-item">
                                <div class="data-label"><i class="fas fa-coins"></i> {{ $isManual ? 'Jumlah Tagihan' : 'Jumlah' }}</div>
                                <div class="data-value text-primary">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</div>
                            </li>
                            <li class="data-item">
                                <div class="data-label"><i class="fas fa-user-tie"></i> Owner</div>
                                <div class="data-value">{{ $transaction->owner->brand_name ?? 'Belum Lengkap' }}</div>
                            </li>
                            <li class="data-item">
                                <div class="data-label"><i class="fas fa-store"></i> Outlet</div>
                                <div class="data-value">
                                    {{ $transaction->outlet->outlet_name ?? 'Belum Lengkap' }}
                                    @if ($transaction->outlet?->address)
                                        <div class="small text-muted fw-normal mt-1" style="font-size: 12px; line-height: 1.4;">{{ $transaction->outlet->address }}</div>
                                    @endif
                                </div>
                            </li>
                            <li class="data-item">
                                <div class="data-label"><i class="fas fa-mobile-alt"></i> Device Code</div>
                                <div class="data-value"><span class="badge bg-light text-dark px-2">{{ $displayDeviceCode }}</span></div>
                            </li>

                            @if ($isQris)
                                <li class="data-item">
                                    <div class="data-label"><i class="fas fa-network-wired"></i> Provider</div>
                                    <div class="data-value">{{ $transaction->qris_provider_label }}</div>
                                </li>
                                <li class="data-item">
                                    <div class="data-label"><i class="fas fa-link"></i> Gateway Ref</div>
                                    <div class="data-value text-break" style="font-size: 12px;">
                                        {{ $transaction->qrisTransaction?->gateway_reference ?? $transaction->qrisTransaction?->payment_url ?? 'Belum Lengkap' }}
                                    </div>
                                </li>
                            @endif

                            @if ($isManual && $manualDetail)
                                <li class="data-item">
                                    <div class="data-label"><i class="fas fa-concierge-bell"></i> Layanan</div>
                                    <div class="data-value">{{ $manualDetail->service->name ?? 'N/A' }}</div>
                                </li>
                                <li class="data-item">
                                    <div class="data-label"><i class="fas fa-boxes"></i> Jumlah Unit</div>
                                    <div class="data-value">{{ $manualDetail->quantity ?? 1 }} {{ $manualDetail->unit ?? 'Unit' }}</div>
                                </li>
                                <li class="data-item">
                                    <div class="data-label"><i class="fas fa-credit-card"></i> Metode Bayar</div>
                                    <div class="data-value">{{ ucfirst(str_replace('_', ' ', $manualDetail->payment_method ?? 'N/A')) }}</div>
                                </li>
                                <li class="data-item">
                                    <div class="data-label"><i class="fas fa-user-clock"></i> Kasir</div>
                                    <div class="data-value">{{ $manualDetail->cashier_name ?? 'N/A' }}</div>
                                </li>
                            @endif

                            <li class="data-item">
                                <div class="data-label"><i class="fas fa-check-circle"></i> Status Transaksi</div>
                                <div class="data-value">
                                    <span class="badge badge-premium {{ $statusClass }}">{{ $statusLabel }}</span>
                                </div>
                            </li>
                            <li class="data-item">
                                <div class="data-label"><i class="far fa-calendar-check"></i> Tanggal Transaksi</div>
                                <div class="data-value">
                                    {{ $transaction->created_at->setTimezone($tz)->format('d-m-Y H:i') }}
                                    <span class="text-muted fw-normal">{{ strtoupper($transaction->timezone) }}</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right Column: Detail Transaksi Perangkat -->
            <div class="col-xl-7 col-lg-6">
	                <div class="content-card">
	                    <div class="card-header-premium">
	                        <h5><i class="fas fa-desktop"></i> Detail Transaksi Perangkat</h5>
	                        <div class="header-actions">
	                             <button class="btn btn-light btn-sm px-3" disabled>
	                                 Export <i class="fas fa-download ms-1" style="font-size: 10px;"></i>
	                             </button>
	                        </div>
	                    </div>
	                    <div class="card-body-premium p-0">
	                        <div class="transaction-note">
	                            <i class="fas fa-info-circle text-primary me-2"></i>
	                            Daftar perangkat yang termasuk dalam transaksi ini.
	                        </div>
	                        @if ($transaction->deviceTransactions->isNotEmpty())
	                            <div class="table-responsive">
	                                <table class="premium-table">
	                                    <thead>
	                                        <tr>
	                                            <th>Perangkat</th>
	                                            <th>Tipe</th>
	                                            <th class="text-end">Harga Satuan</th>
	                                            <th class="text-end">Jumlah</th>
	                                            <th class="text-end">Subtotal</th>
	                                        </tr>
	                                    </thead>
	                                    <tbody>
	                                        @foreach ($transaction->deviceTransactions as $deviceTrans)
	                                            @php
	                                                $itemCount = max($transaction->deviceTransactions->count(), 1);
	                                                $unitPrice = (int) round($transaction->amount / $itemCount);
	                                            @endphp
	                                            <tr>
	                                                <td>
	                                                    <div class="fw-bold">{{ $deviceTrans->device->code ?? $deviceTrans->device_code }}</div>
	                                                    <div class="small text-muted">
	                                                        @if ($deviceTrans->activated_at)
	                                                            Dijalankan {{ \Carbon\Carbon::parse($deviceTrans->activated_at)->setTimezone($tz)->format('d-m-Y H:i') }}
	                                                        @else
	                                                            Menunggu aktivasi
	                                                        @endif
	                                                    </div>
	                                                </td>
	                                                <td>{{ ucfirst($deviceTrans->service_type) }}</td>
	                                                <td class="text-end">Rp {{ number_format($unitPrice, 0, ',', '.') }}</td>
	                                                <td class="text-end">1</td>
	                                                <td class="text-end fw-bold">Rp {{ number_format($unitPrice, 0, ',', '.') }}</td>
	                                            </tr>
	                                        @endforeach
	                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-inbox"></i>
                                </div>
                                <h6>Belum ada detail perangkat</h6>
                                <p>Transaksi ini belum memiliki detail perangkat.</p>
                            </div>
                        @endif
                    </div>
	                    <div class="total-footer">
	                        <div class="item-count">
	                            <div class="total-label">Total Item</div>
	                            <div class="fw-bold fs-5">{{ $transaction->deviceTransactions->count() }}</div>
	                        </div>
	                        <div class="text-end">
	                            <div class="total-label">Total Pembayaran</div>
	                            <div class="total-value">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</div>
	                        </div>
	                    </div>
	                </div>
	            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/plugins/gritter/js/jquery.gritter.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                $.gritter.add({
                    title: 'Berhasil!',
                    text: 'Order ID telah disalin ke clipboard.',
                    sticky: false,
                    time: 3000,
                    class_name: 'gritter-light'
                });
            }).catch(err => {
                console.error('Gagal menyalin: ', err);
            });
        }

        function confirmBypass(selectElement) {
            if (selectElement.value === 'active') {
                swal({
                    title: "Konfirmasi Aktifasi Bypass",
                    text: "Apakah Anda yakin ingin mengaktifkan bypass? Mesin akan langsung menyala dan status tidak bisa dibatalkan.",
                    icon: "info",
                    buttons: {
                        cancel: {
                            text: "Batal",
                            value: null,
                            visible: true,
                            className: "btn btn-secondary",
                            closeModal: true
                        },
                        confirm: {
                            text: "Ya, Aktifkan!",
                            value: true,
                            visible: true,
                            className: "btn btn-primary"
                        }
                    },
                    dangerMode: false,
                }).then((willActivate) => {
                    if (willActivate) {
                        selectElement.form.submit();
                    } else {
                        selectElement.value = 'inactive';
                    }
                });
            }
        }

        $(document).ready(function() {
            @if (session('success'))
                $.gritter.add({
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    sticky: false,
                    time: 5000,
                    class_name: 'gritter-light'
                });
            @endif

            @if (session('error'))
                $.gritter.add({
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    sticky: false,
                    time: 5000,
                    class_name: 'gritter-light'
                });
            @endif

            $('.btn-check-payment-gateway').on('click', function() {
                const btn = $(this);
                const provider = String(btn.data('provider') || 'gateway').toUpperCase();
                const checkUrl = btn.data('check-url');
                const originalHtml = btn.html();

                btn.html('<i class="fa fa-spinner fa-spin me-1"></i> Mengecek...').prop('disabled', true);

                $.ajax({
                    url: checkUrl,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        btn.html(originalHtml).prop('disabled', false);

                        if (res.status === 'success') {
                            swal("Pembayaran Ditemukan di " + provider, res.message, "success")
                                .then(() => window.location.reload());
                            return;
                        }

                        if (res.status === 'not_found') {
                            swal("Belum Ada Pembayaran", res.message, "warning");
                            return;
                        }

                        swal("Info " + provider, res.message || 'Status belum berubah.', "info");
                    },
                    error: function() {
                        btn.html(originalHtml).prop('disabled', false);
                        swal("Gagal", "Tidak bisa mengecek status pembayaran saat ini.", "error");
                    }
                });
            });
        });
    </script>
@endpush
