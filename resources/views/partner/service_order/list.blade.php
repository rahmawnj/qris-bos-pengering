@extends('layouts.dashboard.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h2 class="mb-0">Daftar Service Order Belum Selesai</h2>
            <div class="text-muted small">
                Menampilkan {{ $transactions->count() }} order per halaman
            </div>
            {{-- <a href="{{ route('partner.outlets.create') }}" class="btn btn-primary btn-add-outlet">
                <i class="fas fa-plus-circle me-2"></i> Tambah Outlet Baru
            </a> --}}
        </div>

        <div class="row g-4" id="serviceOrderList">
            @forelse($transactions as $transaction)
                {{-- Pastikan transaksi memiliki manualTransaction --}}
                @if ($transaction->manualTransaction)
                    <div class="col-12 col-lg-6 d-flex align-items-stretch">
                        <div class="card service-order-card w-100">
                            <div class="card-body">
                                @php
                                    $progress = $transaction->manualTransaction->progress ?? 'received';
                                    $progressLabel = \App\Constants\OrderStatus::label($progress);
                                    $progressClass = $progress === 'ready_for_pickup' ? 'status-pickup' : 'status-received';
                                @endphp

                                <div class="order-card-header">
                                    <span class="order-status-badge {{ $progressClass }}">
                                        <i class="fas {{ $progress === 'ready_for_pickup' ? 'fa-box' : 'fa-check-circle' }}"></i>
                                        {{ strtoupper(str_replace('_', ' ', $progress)) }}
                                    </span>
                                    <strong class="order-id">#{{ $transaction->order_id }}</strong>
                                </div>

                                <div class="order-detail-grid">
                                    <div class="order-info-item">
                                        <i class="fas fa-user"></i>
                                        <div>
                                            <span>Customer</span>
                                            <strong>{{ $transaction->manualTransaction->customer_name ?? 'Pelanggan Walk-in' }}</strong>
                                        </div>
                                    </div>
                                    <div class="order-info-item">
                                        <i class="fas fa-cogs"></i>
                                        <div>
                                            <span>Layanan</span>
                                            <strong>{{ ucfirst($transaction->manualTransaction->service->name ?? 'N/A') }}</strong>
                                        </div>
                                    </div>
                                    <div class="order-info-item">
                                        <i class="fas fa-phone-alt"></i>
                                        <div>
                                            <span>Telepon</span>
                                            <strong>{{ $transaction->manualTransaction->customer_phone_number ?? '-' }}</strong>
                                        </div>
                                    </div>
                                    <div class="order-info-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <div>
                                            <span>Dibuat Pada</span>
                                            <strong>{{ $transaction->created_at->format('d M Y, H:i') }} WIB</strong>
                                        </div>
                                    </div>
                                    <div class="order-info-item">
                                        <i class="fas fa-user-tie"></i>
                                        <div>
                                            <span>Kasir</span>
                                            <strong>{{ $transaction->manualTransaction->cashier_name ?? '-' }}</strong>
                                        </div>
                                    </div>
                                    <div class="order-info-item">
                                        <i class="far fa-clock"></i>
                                        <div>
                                            <span>Estimasi Selesai</span>
                                            @if ($transaction->manualTransaction->estimated_completion_at)
                                                <strong class="text-danger">
                                                    {{ \Carbon\Carbon::parse($transaction->manualTransaction->estimated_completion_at)->format('d M Y, H:i') }} WIB
                                                </strong>
                                            @else
                                                <strong class="text-muted">-</strong>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="order-card-footer">
                                    <div class="order-total">
                                        <span>Total</span>
                                        <strong>Rp{{ number_format($transaction->amount, 0, ',', '.') }}</strong>
                                    </div>
                                    <button type="button" class="btn order-detail-btn" data-bs-toggle="offcanvas"
                                        data-bs-target="#deviceServiceModal-{{ $transaction->id }}">
                                        Lihat Detail <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Drawer for Device Services --}}
                    <div class="offcanvas offcanvas-end service-order-drawer" id="deviceServiceModal-{{ $transaction->id }}" tabindex="-1"
                        aria-labelledby="deviceServiceModalLabel-{{ $transaction->id }}" aria-hidden="true">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title mb-1" id="deviceServiceModalLabel-{{ $transaction->id }}">Detail Order</h5>
                                    <div class="modal-order-id">#{{ $transaction->order_id }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                                    aria-label="Close"></button>
                            </div>

	                                <div class="modal-body service-order-modal-body">
                                        @php
                                            $currentProgress = $transaction->manualTransaction->progress ?? 'received';
                                            $progressKeys = array_keys(\App\Constants\OrderStatus::STATUSES);
                                            $currentProgressIndex = array_search($currentProgress, $progressKeys, true);
                                            $currentProgressIndex = $currentProgressIndex === false ? 0 : $currentProgressIndex;
                                        @endphp

	                                    <section class="modal-section">
                                            <h6 class="modal-section-title">Informasi Order</h6>
                                            <div class="modal-info-grid">
                                                <div class="modal-info-item">
                                                    <i class="fas fa-user"></i>
                                                    <div>
                                                        <span>Customer</span>
                                                        <strong>{{ $transaction->manualTransaction->customer_name ?? 'Pelanggan Walk-in' }}</strong>
                                                    </div>
                                                </div>
                                                <div class="modal-info-item">
                                                    <i class="fas fa-cogs"></i>
                                                    <div>
                                                        <span>Metode Bayar</span>
                                                        <strong class="payment-pill">{{ ucfirst(str_replace('_', ' ', $transaction->manualTransaction->payment_method ?? 'N/A')) }}</strong>
                                                    </div>
                                                </div>
                                                <div class="modal-info-item">
                                                    <i class="fas fa-phone-alt"></i>
                                                    <div>
                                                        <span>Telepon</span>
                                                        <strong>{{ $transaction->manualTransaction->customer_phone_number ?? '-' }}</strong>
                                                    </div>
                                                </div>
                                                <div class="modal-info-item">
                                                    <i class="fas fa-filter"></i>
                                                    <div>
                                                        <span>Layanan Utama</span>
                                                        <strong>{{ ucfirst($transaction->manualTransaction->service->name ?? 'N/A') }}</strong>
                                                    </div>
                                                </div>
                                                <div class="modal-info-item">
                                                    <i class="fas fa-user-tie"></i>
                                                    <div>
                                                        <span>Kasir</span>
                                                        <strong>{{ $transaction->manualTransaction->cashier_name ?? '-' }}</strong>
                                                    </div>
                                                </div>
                                                <div class="modal-info-item">
                                                    <i class="far fa-clock"></i>
                                                    <div>
                                                        <span>Estimasi Selesai</span>
                                                        @if ($transaction->manualTransaction->estimated_completion_at)
                                                            <strong class="text-danger">
                                                                {{ \Carbon\Carbon::parse($transaction->manualTransaction->estimated_completion_at)->format('d M Y, H:i') }} WIB
                                                            </strong>
                                                        @else
                                                            <strong class="text-muted">-</strong>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </section>

                                        @if (!empty($transaction->manualTransaction->addons))
                                            <section class="modal-section">
                                                <h6 class="modal-section-title">Detail Tambahan</h6>
                                                <ul class="list-unstyled mb-0 modal-addon-list">
                                                    @foreach ($transaction->manualTransaction->addons as $addon)
                                                        @php
                                                            $addonQty = max(1, (int) ($addon['qty'] ?? 1));
                                                            $addonPrice = (float) ($addon['price'] ?? 0);
                                                            $addonTotal = $addonPrice * $addonQty;
                                                        @endphp
                                                        <li>
                                                            <span>
                                                                {{ $addon['name'] ?? 'N/A' }}
                                                                @if ($addonQty > 1)
                                                                    (x{{ $addonQty }})
                                                                @endif
                                                                <small class="text-muted d-block">
                                                                    Harga satuan: Rp{{ number_format($addonPrice, 0, ',', '.') }}
                                                                </small>
                                                            </span>
                                                            <strong>Rp{{ number_format($addonTotal, 0, ',', '.') }}</strong>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </section>
                                        @endif

                                        <section class="modal-section modal-note-progress">
                                            <div>
                                                <h6 class="modal-section-title">Catatan</h6>
                                                <p class="modal-note-text">{{ $transaction->manualTransaction->notes ?: '-' }}</p>
                                            </div>
                                            <form
                                                action="{{ route('partner.service-orders.update-progress', $transaction->manualTransaction->id) }}"
                                                method="POST" class="modal-progress-form" id="progressForm-{{ $transaction->manualTransaction->id }}">
                                                @csrf
                                                <select name="progress" class="form-select form-select-sm"
                                                    onchange="confirmAndSubmitProgress(this, '{{ $transaction->manualTransaction->id }}')"
                                                    {{ in_array($transaction->manualTransaction->progress, \App\Constants\OrderStatus::finalStatuses()) ? 'disabled' : '' }}>
                                                    @foreach (\App\Constants\OrderStatus::STATUSES as $key => $statusData)
                                                        <option value="{{ $key }}"
                                                            {{ $transaction->manualTransaction->progress == $key ? 'selected' : '' }}>
                                                            {{ $statusData['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        </section>

                                        <section class="modal-section">
                                            <h6 class="modal-section-title">Status Progres Utama</h6>
                                            <div class="order-progress-track">
                                                @foreach (\App\Constants\OrderStatus::STATUSES as $key => $statusData)
                                                    @php
                                                        $stepIndex = array_search($key, $progressKeys, true);
                                                        $isDone = $stepIndex !== false && $stepIndex <= $currentProgressIndex;
                                                    @endphp
                                                    <div class="progress-step {{ $isDone ? 'is-done' : '' }}">
                                                        <span class="progress-dot">
                                                            <i class="fas {{ $isDone ? 'fa-check' : 'fa-square' }}"></i>
                                                        </span>
                                                        <strong>{{ $statusData['label'] }}</strong>
                                                        @if ($key === 'received')
                                                            <small>{{ $transaction->created_at->format('d M H:i') }}</small>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </section>

	                                    <section class="modal-section">
                                            <h5 class="device-service-heading">Daftar Layanan Perangkat</h5>
	                                    @if ($transaction->deviceTransactions->isEmpty())
	                                        <div class="alert alert-warning text-center">
	                                            Tidak ada layanan perangkat yang terkait dengan transaksi ini.
	                                        </div>
	                                    @else
	                                        <div class="device-service-list">
	                                            @foreach ($transaction->deviceTransactions as $deviceService)
                                                @php
                                                    $activatedAt = $deviceService->activated_at;
                                                    $bypassActivation = $deviceService->bypass_activation;
                                                    $isServiceActivated = !is_null($activatedAt);
                                                    $diffHoursSinceActivated = null;

                                                    $buttonText = 'Mulai Layanan';
                                                    $buttonClass = 'btn-success';
                                                    $buttonDisabled = '';
                                                    $serviceStatusText = 'Belum Dimulai';
                                                    $activatedInfo = '';

                                                    $machineStatus = $deviceService->status
                                                        ? 'Mesin Sedang Berjalan (Konfirmasi IoT)' // Changed text for clarity
                                                        : 'Mesin Telah Berhenti';

                                                    if ($isServiceActivated) {
                                                        $activatedCarbon = Carbon\Carbon::parse($activatedAt);
                                                        $now = Carbon\Carbon::now();
                                                        $diffHoursSinceActivated = $now->diffInHours($activatedCarbon);

                                                        $activatedInfo =
                                                            'Dimulai: ' .
                                                            $activatedCarbon->isoFormat('D MMM [pukul] HH:mm') .
                                                            ' WIB';

                                                        // Asumsi: jika lebih dari 24 jam setelah aktivasi, layanan dianggap selesai secara otomatis
                                                        if ($diffHoursSinceActivated >= 24) {
                                                            $buttonText = 'Layanan Selesai (Otomatis)';
                                                            $buttonClass = 'btn-secondary';
                                                            $serviceStatusText = 'Selesai';
                                                            $buttonDisabled = 'disabled';
                                                            $machineStatus = 'Mesin Telah Berhenti';
                                                        } else {
                                                            $serviceStatusText = 'Sedang Berjalan';

                                                            if ($deviceService->status) {
                                                                // if deviceService->status is true (1) means still running
                                                                $buttonText = 'Layanan Sedang Berjalan'; // Updated text
                                                                $buttonClass = 'btn-warning';
                                                                $buttonDisabled = 'disabled';
                                                                $machineStatus =
                                                                    'Mesin Sedang Berjalan (Konfirmasi IoT)';
                                                            } else {
                                                                $buttonText = 'Layanan Selesai (Trigger Ulang)'; // Updated text for clarity
                                                                $buttonClass = 'btn-info';
                                                                $buttonDisabled = ''; // Can be triggered again
                                                                $machineStatus = 'Mesin Telah Berhenti'; // Machine stopped, but service duration not over
                                                            }
                                                        }
                                                    } else {
                                                        $buttonText = 'Mulai Layanan';
                                                        $buttonClass = 'btn-success';
                                                        $serviceStatusText = 'Belum Dimulai';
                                                        $buttonDisabled = '';
                                                        $machineStatus = 'Mesin Belum Dijalankan';
                                                    }

                                                    $bypassInfo = '';
                                                    if ($bypassActivation) {
                                                        $bypassCarbon = Carbon\Carbon::parse($bypassActivation);
                                                        $bypassInfo =
                                                            'Bypass Aktif Sejak: ' .
                                                            $bypassCarbon->isoFormat('D MMM [pukul] HH:mm') .
                                                            ' WIB';
                                                    }
                                                @endphp
	                                                <div class="device-service-card">
                                                        <div class="device-service-icon {{ $isServiceActivated ? 'is-active' : '' }}">
                                                            <i class="fas fa-soap"></i>
                                                        </div>
	                                                    <div class="device-service-content">
	                                                        <h6>Kode Perangkat: <strong>{{ $deviceService->device_code }}</strong></h6>
	                                                        <p>Tipe Layanan: <span>{{ ucfirst($deviceService->service_type ?? 'N/A') }}</span></p>
	                                                        <p>Status Layanan: <strong class="{{ $isServiceActivated ? 'text-success' : 'text-warning' }}">{{ $serviceStatusText }}</strong></p>
	                                                        <p>Status Mesin: <strong>{{ $machineStatus }}</strong></p>
	                                                        @if ($isServiceActivated)
	                                                            <p class="text-info">{{ $activatedInfo }}</p>
	                                                        @endif
	                                                        @if ($bypassInfo)
	                                                            <p class="text-warning">{{ $bypassInfo }}</p>
	                                                        @endif
	                                                    </div>
	                                                    <form
	                                                        action="{{ route('partner.service-orders.activate-device', $deviceService->id) }}"
	                                                        method="POST" class="device-service-action">
	                                                        @csrf
	                                                        <button type="submit"
	                                                            class="btn {{ $buttonClass }} btn-sm {{ $buttonDisabled }}" {{ $buttonDisabled }}>
	                                                            {{ $buttonText }}
	                                                        </button>
	                                                    </form>
	                                                </div>
	                                            @endforeach
	                                        </div>
	                                    @endif
                                        </section>
	                                </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="offcanvas">Tutup</button>
                            </div>
                        </div>
	                    </div>
                @endif {{-- End of @if ($transaction->manualTransaction) --}}
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center" role="alert">
                        Belum ada service order yang belum selesai.
                    </div>
                </div>
            @endforelse
        </div>

        @if ($transactions->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $transactions->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --primary-blue: #007bff;
            --light-gray: #f0f2f5;
            --border-gray: #e9ecef;
            --text-dark: #343a40;
            --text-muted: #6c757d;
            --success-green: #28a745;
            --card-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            /* Slightly less pronounced shadow */
            --card-hover-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            /* Slightly less pronounced hover shadow */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .service-order-card {
            min-height: 300px;
            border-radius: 12px;
            border: none;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            background: #fff;
        }

        .service-order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.12);
        }

        .service-order-card .card-body {
            min-height: 300px;
            padding: 22px;
            display: flex;
            flex-direction: column;
        }

        .order-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 22px;
        }

        .order-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            max-width: 52%;
            padding: 7px 11px;
            border-radius: 8px;
            font-size: .68rem;
            font-weight: 700;
            line-height: 1;
            color: #0f5132;
            background: #d9f5e4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .order-status-badge.status-pickup {
            color: #0d6efd;
            background: #e7f1ff;
        }

        .order-id {
            min-width: 0;
            color: #111827;
            font-size: .76rem;
            font-weight: 700;
            text-align: right;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .order-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            column-gap: 28px;
            row-gap: 18px;
            margin-bottom: 26px;
        }

        .order-info-item {
            display: grid;
            grid-template-columns: 18px minmax(0, 1fr);
            gap: 10px;
            align-items: start;
            min-width: 0;
        }

        .order-info-item i {
            margin-top: 3px;
            color: #0d6efd;
            font-size: .86rem;
            text-align: center;
        }

        .order-info-item span {
            display: block;
            margin-bottom: 6px;
            color: #475569;
            font-size: .72rem;
            font-weight: 600;
        }

        .order-info-item strong {
            display: block;
            color: #111827;
            font-size: .84rem;
            font-weight: 500;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .order-card-footer {
            margin-top: auto;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
        }

        .order-total span {
            display: block;
            color: #475569;
            font-size: .78rem;
            line-height: 1.1;
        }

        .order-total strong {
            display: block;
            margin-top: 4px;
            color: #16a34a;
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .order-detail-btn {
            min-width: 132px;
            padding: 12px 18px;
            border: 0;
            border-radius: 8px;
            color: #0d6efd;
            background: #e7f1ff;
            font-size: .82rem;
            font-weight: 700;
        }

        .order-detail-btn:hover,
        .order-detail-btn:focus {
            color: #fff;
            background: #0d6efd;
        }

	        .modal-content {
	            height: 100%;
	            border: 0;
	            border-radius: 0;
	            overflow: hidden;
	            box-shadow: 0 18px 48px rgba(15, 23, 42, .18);
	        }

	        .service-order-drawer {
	            width: min(680px, 100vw);
	            border-left: 0;
	            box-shadow: -18px 0 40px rgba(15, 23, 42, .18);
	        }

	        .modal-header {
	            flex: 0 0 auto;
	            padding: 20px 24px 16px;
	            border-bottom-color: #e9eef5;
	        }

	        .modal-title {
	            color: #1f2937;
	            font-size: 1.05rem;
	            font-weight: 700;
	        }

	        .modal-order-id {
	            color: #64748b;
	            font-size: .78rem;
	            font-weight: 600;
	        }

	        .service-order-modal-body {
	            flex: 1 1 auto;
	            overflow-y: auto;
	            padding: 0;
	            background: #fff;
	        }

	        .modal-section {
	            padding: 18px 24px;
	            border-bottom: 1px solid #e9eef5;
	        }

	        .modal-section-title {
	            margin-bottom: 14px;
	            color: #1f2937;
	            font-size: .82rem;
	            font-weight: 700;
	        }

	        .modal-info-grid {
	            display: grid;
	            grid-template-columns: repeat(2, minmax(0, 1fr));
	            column-gap: 34px;
	            row-gap: 18px;
	            position: relative;
	        }

	        .modal-info-grid::before {
	            content: "";
	            position: absolute;
	            top: 0;
	            bottom: 0;
	            left: 50%;
	            width: 1px;
	            background: #e5eaf0;
	        }

	        .modal-info-item {
	            display: grid;
	            grid-template-columns: 20px minmax(0, 1fr);
	            gap: 10px;
	            align-items: start;
	        }

	        .modal-info-item i {
	            margin-top: 4px;
	            color: #0d6efd;
	            text-align: center;
	        }

	        .modal-info-item span {
	            display: block;
	            margin-bottom: 5px;
	            color: #64748b;
	            font-size: .72rem;
	            font-weight: 700;
	        }

	        .modal-info-item strong {
	            display: block;
	            color: #111827;
	            font-size: .84rem;
	            font-weight: 500;
	            line-height: 1.35;
	        }

	        .payment-pill {
	            width: fit-content;
	            padding: 4px 10px;
	            border-radius: 14px;
	            color: #15803d !important;
	            background: #dcfce7;
	            font-size: .72rem !important;
	            font-weight: 700 !important;
	        }

	        .modal-addon-list li {
	            display: flex;
	            justify-content: space-between;
	            gap: 16px;
	            padding: 7px 0;
	            border-bottom: 1px solid #eef2f6;
	        }

	        .modal-addon-list li:last-child {
	            border-bottom: 0;
	        }

	        .modal-note-progress {
	            display: grid;
	            grid-template-columns: minmax(0, 1fr) 150px;
	            align-items: center;
	            gap: 20px;
	        }

	        .modal-note-text {
	            margin-bottom: 0;
	            color: #64748b;
	            font-style: italic;
	        }

	        .modal-progress-form .form-select {
	            height: 42px;
	            border-color: #d8e0ea;
	            border-radius: 8px;
	            font-size: .84rem;
	            font-weight: 600;
	        }

	        .order-progress-track {
	            display: grid;
	            grid-template-columns: repeat(4, minmax(0, 1fr));
	            position: relative;
	            gap: 10px;
	        }

	        .order-progress-track::before {
	            content: "";
	            position: absolute;
	            top: 15px;
	            left: 8%;
	            right: 8%;
	            height: 6px;
	            border-radius: 999px;
	            background: #eef2f6;
	        }

	        .progress-step {
	            position: relative;
	            z-index: 1;
	            text-align: center;
	        }

	        .progress-dot {
	            width: 30px;
	            height: 30px;
	            margin: 0 auto 8px;
	            display: inline-flex;
	            align-items: center;
	            justify-content: center;
	            border-radius: 50%;
	            color: #94a3b8;
	            background: #f1f5f9;
	            border: 3px solid #e2e8f0;
	            font-size: .62rem;
	        }

	        .progress-step.is-done .progress-dot {
	            color: #16a34a;
	            background: #dcfce7;
	            border-color: #dcfce7;
	        }

	        .progress-step strong {
	            display: block;
	            color: #334155;
	            font-size: .66rem;
	            font-weight: 700;
	        }

	        .progress-step small {
	            display: block;
	            margin-top: 4px;
	            color: #64748b;
	            font-size: .62rem;
	        }

	        .device-service-heading {
	            margin-bottom: 14px;
	            color: #1f2937;
	            font-size: .96rem;
	            font-weight: 700;
	        }

	        .device-service-list {
	            display: grid;
	            gap: 12px;
	        }

	        .device-service-card {
	            display: grid;
	            grid-template-columns: 58px minmax(0, 1fr) auto;
	            gap: 14px;
	            align-items: center;
	            padding: 16px;
	            border: 1px solid #e5eaf0;
	            border-radius: 10px;
	            background: #fff;
	            box-shadow: 0 2px 7px rgba(15, 23, 42, .04);
	        }

	        .device-service-icon {
	            width: 48px;
	            height: 48px;
	            display: inline-flex;
	            align-items: center;
	            justify-content: center;
	            border-radius: 50%;
	            color: #7c3aed;
	            background: #ede9fe;
	            font-size: 1.25rem;
	        }

	        .device-service-icon.is-active {
	            color: #0d6efd;
	            background: #dbeafe;
	        }

	        .device-service-content h6 {
	            margin-bottom: 7px;
	            color: #1f2937;
	            font-size: .84rem;
	            font-weight: 600;
	        }

	        .device-service-content p {
	            margin-bottom: 4px;
	            color: #475569;
	            font-size: .78rem;
	            line-height: 1.25;
	        }

	        .device-service-content p:last-child {
	            margin-bottom: 0;
	        }

	        .device-service-action .btn {
	            white-space: nowrap;
	            font-size: .74rem;
	            font-weight: 700;
	            border-radius: 7px;
	        }

	        .modal-footer {
	            flex: 0 0 auto;
	            padding: 14px 24px;
	            background: #f8fafc;
	            border-top-color: #e9eef5;
	        }

	        .modal-footer .btn {
	            min-width: 86px;
	            border-radius: 7px;
	        }


        /* Responsive adjustments */
	        @media (max-width: 767.98px) {
	            .order-detail-grid {
	                grid-template-columns: 1fr;
	                row-gap: 14px;
	            }

            .order-card-footer {
                align-items: stretch;
                flex-direction: column;
            }

	            .order-detail-btn {
	                width: 100%;
	            }

                .modal-info-grid,
                .modal-note-progress,
                .device-service-card {
                    grid-template-columns: 1fr;
                }

                .modal-info-grid::before {
                    display: none;
                }

                .order-progress-track {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    row-gap: 18px;
                }

                .order-progress-track::before {
                    display: none;
                }

                .device-service-action .btn {
                    width: 100%;
                }
	        }
    </style>
    <link href="{{ asset('assets/plugins/gritter/css/jquery.gritter.css') }}" rel="stylesheet" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/gritter/js/jquery.gritter.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>

    <script>
        // Make the PHP final statuses available in JavaScript
        window.finalOrderStatuses = @json(\App\Constants\OrderStatus::finalStatuses());

        function confirmAndSubmitProgress(selectElement, transactionId) {
            const selectedProgress = selectElement.value;
            const currentProgress = selectElement.getAttribute('data-current-progress');
            const form = document.getElementById('progressForm-' + transactionId);

            // Check if the selected progress is one of the final statuses
            if (window.finalOrderStatuses.includes(selectedProgress)) {
                swal({
                    title: 'Konfirmasi Perubahan Status',
                    text: 'Apakah Anda yakin ingin mengubah status progress menjadi "' + selectElement.options[selectElement.selectedIndex].text + '"? Status ini bersifat final.',
                    icon: 'warning',
                    buttons: {
                        cancel: {
                            text: 'Batal',
                            value: null,
                            visible: true,
                            className: 'btn btn-danger',
                            closeModal: true
                        },
                        confirm: {
                            text: 'Ya, Ubah Status!',
                            value: true,
                            visible: true,
                            className: 'btn btn-primary',
                            closeModal: true
                        }
                    }
                }).then((isConfirmed) => {
                    if (isConfirmed) {
                        form.submit();
                    } else {
                        // If user cancels, revert the select box to its original value
                        selectElement.value = currentProgress;
                    }
                });
            } else {
                // If it's not a final status, submit directly without confirmation
                form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize data-current-progress for all select elements on page load
            document.querySelectorAll('select[name="progress"]').forEach(function(select) {
                select.setAttribute('data-current-progress', select.value);
            });

            @if (session('success'))
                $.gritter.add({
                    title: 'Sukses',
                    text: '{{ session('success') }}',
                    class_name: 'gritter-success gritter-light',
                    sticky: false,
                    time: 3000
                });
            @endif

            @if (session('error'))
                $.gritter.add({
                    title: 'Error',
                    text: '{{ session('error') }}',
                    class_name: 'gritter-error gritter-light',
                    sticky: false,
                    time: 3000
                });
            @endif
        });
    </script>
@endpush
