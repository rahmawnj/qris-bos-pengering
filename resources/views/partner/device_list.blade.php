@extends('layouts.dashboard.app')
@php
    $feature = getData(); // Dapatkan instance DataFetcher sekali
@endphp

@props([
    'title' => 'List Device',
])

@push('styles')
    <link href="{{ asset('assets/plugins/switchery/dist/switchery.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/gritter/css/jquery.gritter.css') }}" rel="stylesheet" />
    <style>
		        .device-card {
			            min-height: 190px;
		            border: 1px solid rgba(89, 122, 153, .24);
		            border-radius: 10px;
		            margin-bottom: 20px;
		            color: #eef6ff;
		            background:
		                linear-gradient(135deg, rgba(21, 37, 52, .96) 0%, rgba(20, 34, 48, .96) 48%, rgba(9, 16, 23, .98) 100%);
		            box-shadow: 0 10px 22px rgba(5, 16, 27, .14);
		            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
		            position: relative;
		            overflow: hidden;
		        }
			        .device-card::before {
			            content: "";
			            position: absolute;
			            inset: 0;
			            background: linear-gradient(135deg, rgba(255, 255, 255, .06), transparent 42%);
			            pointer-events: none;
			        }
		        .device-card:hover {
		            transform: translateY(-2px);
		            border-color: rgba(45, 168, 255, .45);
		            box-shadow: 0 14px 26px rgba(5, 16, 27, .22);
		        }
		        .device-card .card-body {
		            position: relative;
		            z-index: 1;
		            padding: 14px;
		        }
			        .device-card-grid {
			            display: flex;
			            flex-direction: column;
				            min-height: 162px;
			        }
		        .device-info-panel {
		            display: flex;
		            flex-direction: column;
		            min-width: 0;
                    flex-grow: 1;
		        }
			        .device-heading {
			            display: flex;
			            align-items: center;
			            min-width: 0;
				            gap: 8px;
				            margin-bottom: 14px;
				            padding-right: 86px;
			        }
			        .device-head-icon {
			            position: relative;
				            width: 44px;
				            height: 44px;
			            display: inline-flex;
			            align-items: center;
			            justify-content: center;
				            flex: 0 0 44px;
				            border-radius: 10px;
			            color: #28b7ff;
			            background: rgba(40, 183, 255, .12);
			            border: 1px solid rgba(40, 183, 255, .24);
				            font-size: 20px;
			            box-shadow: inset 0 0 18px rgba(40, 183, 255, .08);
			        }
			        .device-status-dot {
			            position: absolute;
				            top: 6px;
				            right: 6px;
				            width: 8px;
				            height: 8px;
			            border-radius: 50%;
			            background: #25e17b;
			            box-shadow: 0 0 12px rgba(37, 225, 123, .78);
		        }
		        .device-status-dot.is-off {
		            background: #8996a3;
		            box-shadow: 0 0 10px rgba(137, 150, 163, .45);
		        }
			        .device-title {
			            min-width: 0;
			        }
			        .device-title h5 {
			            color: #f7fbff;
			            font-size: 1rem;
			            font-weight: 400;
			            line-height: 1.2;
			            margin-bottom: 4px;
			        }
			        .device-heading-code {
			            display: inline-flex;
			            align-items: center;
			            color: #28b7ff;
			            font-size: .78rem;
			            font-weight: 400;
			            line-height: 1.15;
			        }
		        .device-meta {
		            display: grid;
		            gap: 14px;
		            margin-bottom: 14px;
		        }
		        .device-meta-item {
		            color: #dcecff;
		        }
		        .device-meta-label {
		            display: block;
		            color: rgba(220, 236, 255, .55);
		            font-size: .62rem;
		            font-weight: 400;
		            line-height: 1.15;
		            text-transform: uppercase;
		            letter-spacing: .04em;
		        }
		        .device-meta-value {
		            display: block;
		            margin-top: 5px;
		            color: #f5f9ff;
		            font-size: .82rem;
		            font-weight: 400;
		        }
		        .device-code-text {
		            color: #28b7ff;
		            font-size: 1.04rem;
		            font-weight: 400;
		        }
		        .device-control-section {
		            margin-top: auto;
		            padding-top: 12px;
                    padding-bottom: 24px;
		            border-top: 1px solid rgba(220, 236, 255, .18);
                    position: relative;
		        }
		        .device-control-section h6 {
		            color: rgba(240, 247, 255, .78);
		            font-size: .66rem;
		            font-weight: 400;
		            letter-spacing: .03em;
		            text-transform: uppercase;
		        }
			        .device-control-bar {
			            display: flex;
			            align-items: center;
			            justify-content: space-between;
			            flex-wrap: wrap;
				            gap: 8px;
			        }
			        .device-card .device-status-select {
				            width: 108px !important;
				            min-height: 36px;
			            color: #fff;
			            cursor: pointer;
			            appearance: none;
			            background-color: rgba(255, 255, 255, .12);
			            background-image:
			                linear-gradient(45deg, transparent 50%, currentColor 50%),
			                linear-gradient(135deg, currentColor 50%, transparent 50%);
			            background-position:
			                calc(100% - 16px) 15px,
			                calc(100% - 10px) 15px;
			            background-size: 6px 6px, 6px 6px;
			            background-repeat: no-repeat;
			            border-color: rgba(255, 255, 255, .22);
				            border-radius: 7px;
			            box-shadow:
			                inset 0 1px 0 rgba(255, 255, 255, .12),
			                0 8px 18px rgba(0, 0, 0, .18);
				            font-weight: 400;
				            padding-right: 30px;
                            font-size: .78rem;
			        }
			        .device-card .device-status-select:hover {
			            background-color: rgba(255, 255, 255, .18);
			            border-color: rgba(45, 168, 255, .55);
			        }
			        .device-card .device-status-select:focus {
			            color: #fff;
			            background-color: rgba(255, 255, 255, .2);
			            border-color: rgba(45, 168, 255, .7);
			            box-shadow:
			                0 0 0 .2rem rgba(45, 168, 255, .18),
			                0 10px 22px rgba(0, 0, 0, .22);
			        }
			        .device-card .device-status-select:disabled {
			            cursor: not-allowed;
			            opacity: .65;
			        }
			        .device-card .device-status-select option {
			            color: #1f2937;
			            background: #fff;
			        }
			        .device-card-actions {
			            display: flex;
			            justify-content: flex-end;
				            gap: 8px;
			        }
		        .device-card-actions .btn {
		            width: 36px;
		            height: 36px;
		            display: inline-flex;
		            align-items: center;
		            justify-content: center;
		            border-radius: 6px;
		        }
		        .qris-ribbon {
		            position: absolute;
		            top: 12px;
		            right: 12px;
		            width: auto;
		            padding: 6px 10px;
		            border-radius: 7px;
		            font-weight: 400;
		            font-size: .6rem;
		            letter-spacing: .04em;
		            text-transform: uppercase;
		            color: #fff;
		            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.22);
		            z-index: 2;
		            text-align: center;
	        }
        .qris-available {
            background: #1f9d55;
        }
        .qris-unavailable {
            background: #e3342f;
        }
	        .qris-ribbon .qris-hint {
	            position: absolute;
	            top: 34px;
	            right: 0;
	            background: #1f2937;
	            color: #fff;
	            font-size: 0.65rem;
            padding: 4px 8px;
            border-radius: 6px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.15s ease-in-out;
        }
        .qris-unavailable:hover .qris-hint {
            opacity: 1;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            color: #fff;
            display: inline-block;
            min-width: 80px;
            text-align: center;
        }
        .status-off {
            background-color: #dc3545;
        }
        .status-active {
            background-color: #28a745;
        }
        .status-pending {
            background-color: #ffc107;
        }
	        .status-unavailable {
	            background-color: #6c757d;
	        }
	        @media (max-width: 575.98px) {
	            .device-card-actions {
	                justify-content: flex-start;
	            }
	        }
	        .device-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
	        .card.highlight,
	        .device-card.is-focused {
	            animation: highlightAnimation 2s ease-in-out;
	        }
	        .device-card.is-focused {
	            border-color: #0d6efd;
	        }
            .device-blocked-note {
                color: #ff5b57;
                font-size: 0.65rem;
                font-weight: 500;
                text-align: center;
                position: absolute;
                bottom: 4px;
                left: 0;
                right: 0;
                z-index: 10;
            }
	        @keyframes highlightAnimation {
            0% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 0 15px 5px rgba(40, 167, 69, 0.7);
                transform: scale(1.02);
            }
            100% {
                box-shadow: 0 2px 4px rgba(0, 0, 0, .05);
                transform: scale(1);
            }
        }
    </style>
    <link href="{{ asset('assets/plugins/select-picker/dist/picker.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title }}</h4>
            <div class="panel-heading-btn">
                <a href="{{ !$feature->can('partner.device.store') ? '#' : '#create-device-modal' }}" id="addDeviceButton"
                    class="btn btn-xs btn-success {{ !$feature->can('partner.device.store') ? 'disabled' : '' }}"
                    data-bs-toggle="modal" title="Tambah Device Baru"
                    {{ !$feature->can('partner.device.store') ? 'aria-disabled="true" tabindex="-1"' : '' }}>
                    <i class="fa fa-plus"></i> Tambah Device
                </a>
            </div>
        </div>
        <div class="panel-body">
            <div class="row mb-3 align-items-end">
                <div class="col-md-6">
                    <a href="javascript:;" class="btn btn-xs btn-primary" id="refresh-button">
                        <i class="fa fa-sync"></i> Refresh
                    </a>
                </div>
	                <div class="col-md-6">
	                    <label for="device-select" class="form-label">Pilih Device</label>
	                    <select id="device-select" class="form-control" data-live-search="true"
	                        onchange="onDeviceChange(this)">
	                        <option value="">-- Pilih Device --</option>
                        @foreach ($devices as $deviceFilter)
                            <option value="{{ $deviceFilter->id }}"> {{ $deviceFilter->code }} |
                                {{ $deviceFilter->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
	                @forelse ($devices as $device)
		                    <div class="col-sm-6 col-md-4 mb-4" id="device-{{ $device->id }}">
                        <div class="card h-100 device-card">
                            @php
                                $isBillingBlocked = (bool) ($device->outlet?->has_overdue_billing);
                                $qrisAvailable = $device->outlet && $device->outlet->status && !$isBillingBlocked;
                            @endphp
                            @if ($qrisAvailable)
                                <div class="qris-ribbon qris-available">QRIS Ready</div>
	                            @elseif ($isBillingBlocked)
	                                <div class="qris-ribbon qris-unavailable">
	                                    QRIS Terblokir
	                                    <span class="qris-hint">Masa aktif jatuh tempo</span>
                                </div>
                            @else
                                <div class="qris-ribbon qris-unavailable">
                                    QRIS Off
                                    <span class="qris-hint">Buka outlet dulu</span>
                                </div>
                            @endif
	                            <div class="card-body">
	                                <div class="device-card-grid">
		                                    <div class="device-info-panel">
		                                        <div class="device-heading">
		                                            <span class="device-head-icon">
		                                                <i class="fa fa-microchip"></i>
		                                                <span class="device-status-dot {{ $device->device_status === 'off' ? 'is-off' : '' }}"></span>
		                                            </span>
		                                            <div class="device-title">
		                                                <h5 class="mb-0 text-truncate">{{ $device->name }}</h5>
		                                                <span class="device-heading-code">{{ $device->code }}</span>
		                                            </div>
		                                        </div>

		                                        <div class="device-meta">
		                                            <div class="device-meta-item">
		                                                <span class="device-meta-label">Outlet</span>
		                                                <span class="device-meta-value">{{ $device->outlet->outlet_name ?? 'N/A' }}</span>
	                                            </div>
	                                        </div>

	                                        <div class="device-control-section">
	                                            <h6 class="mb-2">Manajemen Status & Harga</h6>
	                                            <div class="device-control-bar">
	                                                <select class="device-status-select form-control"
	                                                    data-device-id="{{ $device->id }}"
	                                                    data-billing-blocked="{{ $isBillingBlocked ? '1' : '0' }}"
	                                                    {{ !$feature->can('partner.device.update_status') || $isBillingBlocked ? 'disabled' : '' }}>
	                                                    <option value="off" {{ $device->device_status === 'off' ? 'selected' : '' }}>
	                                                        Off
	                                                    </option>
	                                                    @foreach (App\Models\ServiceType::all() as $type)
	                                                        <option value="{{ $type->slug }}"
	                                                            {{ $device->device_status === $type->slug ? 'selected' : '' }}>
	                                                            {{ $type->name }}
	                                                        </option>
	                                                    @endforeach
	                                                </select>
	                                                <div class="device-card-actions">
	                                                    <a href="#edit-device-modal-{{ $device->id }}"
	                                                        class="btn btn-sm btn-info {{ !$feature->can('partner.device.update') ? 'disabled' : '' }}"
	                                                        data-bs-toggle="modal" title="Edit Device"
	                                                        {{ !$feature->can('partner.device.update') ? 'aria-disabled="true" tabindex="-1"' : '' }}>
	                                                        <i class="fa fa-edit"></i>
	                                                    </a>
	                                                    <a href="#price-modal-{{ $device->id }}"
	                                                        class="btn btn-sm btn-primary {{ !$feature->can('partner.device.service_types.update') ? 'disabled' : '' }}"
	                                                        data-bs-toggle="modal" title="Set Prices"
	                                                        {{ !$feature->can('partner.device.service_types.update') ? 'aria-disabled="true" tabindex="-1"' : '' }}>
	                                                        <i class="fa fa-dollar-sign"></i>
	                                                    </a>
	                                                    <button type="button" class="btn btn-sm btn-danger delete-device-btn"
	                                                        data-device-id="{{ $device->id }}" data-device-name="{{ $device->name }}"
	                                                        title="Hapus Device"
	                                                        {{ !$feature->can('partner.device.destroy') ? 'disabled' : '' }}>
	                                                        <i class="fa fa-trash-alt"></i>
	                                                    </button>
	                                                </div>
	                                            </div>
	                                                @if ($isBillingBlocked)
	                                                    <div class="device-blocked-note">
	                                                        Bypass dikunci sampai QRIS diperpanjang.
	                                                    </div>
	                                                @endif
	                                        </div>
	                                    </div>
		                                </div>
	                            </div>
	                        </div>
	                    </div>
	                @empty
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            <strong>Belum ada device yang terdaftar.</strong>
                        </div>
                    </div>
                @endforelse
            </div>
	        </div>
	    </div>

    @foreach ($devices as $device)
        <form action="{{ route('partner.device.service_types.update', $device->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal fade" id="price-modal-{{ $device->id }}">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Edit Harga Service Type - {{ $device->name }}</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-hidden="true"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Service Type</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (App\Models\ServiceType::all() as $serviceType)
                                        <tr>
                                            <td>{{ $serviceType->name }}</td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="number"
                                                        name="prices[{{ $serviceType->id }}]"
                                                        class="form-control price-input"
                                                        value="{{ optional($device->serviceTypes->firstWhere('id', $serviceType->id))->pivot->price ?? 0 }}"
                                                        min="0" required>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <a href="javascript:;" class="btn btn-white" data-bs-dismiss="modal">Tutup</a>
                            <button type="submit" class="btn btn-success">Simpan Harga</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <form action="{{ route('partner.device.update', $device->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal fade" id="edit-device-modal-{{ $device->id }}">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Edit Device - {{ $device->name }}</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-hidden="true"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label for="device-name-{{ $device->id }}">Nama Device</label>
                                <input type="text" name="name" id="device-name-{{ $device->id }}" class="form-control" value="{{ $device->name }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="device-outlet-{{ $device->id }}">Outlet</label>
                                <select name="outlet_id" id="device-outlet-{{ $device->id }}" class="form-control" required>
                                    <option value="">Pilih Outlet</option>
                                    @foreach ($outlets as $outlet)
                                        <option value="{{ $outlet->id }}"
                                            {{ $device->outlet_id == $outlet->id ? 'selected' : '' }}>
                                            {{ $outlet->outlet_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="javascript:;" class="btn btn-white" data-bs-dismiss="modal">Tutup</a>
                            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endforeach

	    {{-- Create Device Modal --}}
    <form action="{{ route('partner.device.store') }}" method="POST">
        @csrf
        <div class="modal fade" id="create-device-modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Tambah Device Baru</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="new-device-name">Nama Device</label>
                            <input type="text" name="name" id="new-device-name" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="new-device-outlet">Outlet</label>
                            <select name="outlet_id" id="new-device-outlet" class="form-control" required>
                                <option value="">Pilih Outlet</option>
                                @foreach ($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->outlet_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="javascript:;" class="btn btn-white" data-bs-dismiss="modal">Tutup</a>
                        <button type="submit" class="btn btn-success">Simpan Device</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('assets/plugins/switchery/dist/switchery.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/gritter/js/jquery.gritter.js') }}"></script>
    <script src="{{ asset('assets/plugins/select-picker/dist/picker.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>

	    <script>
	        let lastFocusedDeviceId = null;
	        let lastFocusedAt = 0;

	        function focusDeviceCard(selectedDeviceId) {
	            const now = Date.now();
	            selectedDeviceId = String(selectedDeviceId || '');

	            if (!selectedDeviceId || (selectedDeviceId === lastFocusedDeviceId && now - lastFocusedAt < 700)) {
	                return;
	            }

	            lastFocusedDeviceId = selectedDeviceId;
	            lastFocusedAt = now;

	            $('.card').removeClass('highlight');
	            $('.device-card').removeClass('is-focused');

	            if (selectedDeviceId) {
	                const deviceContainer = $('#device-' + selectedDeviceId);
	                if (deviceContainer.length) {
	                    $('html, body').animate({
	                        scrollTop: deviceContainer.offset().top - 120
	                    }, 500, function() {
	                        const targetCard = deviceContainer.find('.card');
	                        targetCard.addClass('highlight is-focused');
	                        setTimeout(() => targetCard.removeClass('highlight is-focused'), 5000);
	                    });
	                }
	            }
	        }

	        function onDeviceChange(el) {
	            focusDeviceCard($(el).val());
	        }

	    </script>

	    <script>
	        $(document).ready(function() {
	            if ($.fn.picker) {
	                $('#device-select').picker({
	                    search: true,
	                    texts: {
	                        trigger: '-- Pilih Device --',
	                        noResult: 'Device tidak ditemukan',
	                        search: 'Cari kode atau nama device...'
	                    }
	                });
	            }

	            $('#device-select').on('change sp-change', function() {
	                focusDeviceCard($(this).val());
	            });

	            $(document).on('click', '#device-select + .picker .pc-list li:not(.not-found)', function() {
	                focusDeviceCard($(this).data('id'));
	            });

	            $('.price-input').on('focus', function() {
	                if (this.value === '0') {
	                    this.value = '';
	                }
	            });

	            $('.price-input').on('input', function() {
	                this.value = this.value.replace(/^0+(?=\d)/, '');
	            });

	            $('.price-input').on('blur', function() {
	                if (this.value === '') {
	                    this.value = '0';
	                }
	            });

	            $('.device-status-select').each(function() {
	                $(this).data('original', $(this).val());
	            });

            $('.device-status-select').on('change', function() {
                var newStatus = $(this).val();
                var deviceId = $(this).data('device-id');
                var originalStatus = $(this).data('original');

                // Tampilkan SweetAlert dengan input field
                swal({
                    title: 'Berikan Catatan Bypass',
                    text: 'Silakan masukkan catatan mengapa status device ini diubah.',
                    content: {
                        element: "input",
                        attributes: {
                            placeholder: "Catatan Bypass",
                            type: "text",
                        },
                    },
                    buttons: {
                        cancel: {
                            text: "Batal",
                            value: null,
                            visible: true,
                            className: "btn btn-secondary",
                            closeModal: true
                        },
                        confirm: {
                            text: "Ubah Status",
                            value: true,
                            visible: true,
                            className: "btn btn-primary"
                        }
                    },
                    dangerMode: false,
                }).then((value) => {
                    // SweetAlert ini mengembalikan nilai dari input jika dikonfirmasi
                    if (value === null) {
                        // Jika user klik 'Batal' atau di luar modal
                        $(this).val(originalStatus);
                        return;
                    }

                    var bypassNote = value.trim();
                    if (bypassNote === '') {
                        swal('Peringatan', 'Catatan tidak boleh kosong!', 'warning');
                        $(this).val(originalStatus);
                        return;
                    }

                    // Siapkan data untuk dikirim
                    var formData = new FormData();
                    formData.append('device_status', newStatus);
                    formData.append('bypass_note', bypassNote);
                    formData.append('_token', '{{ csrf_token() }}');

                    // Kirim data ke API
                    fetch('/api/devices/' + deviceId + '/update-status', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                $.gritter.add({
                                    title: 'Success',
                                    text: data.message,
                                    sticky: false,
                                    time: 3000,
                                    class_name: 'my-sticky-class gritter-light'
                                });
	                                $(this).data('original', newStatus); // Update status asli
	                                $(this).closest('.device-card').find('.device-status-dot')
	                                    .toggleClass('is-off', newStatus === 'off');
	                            } else {
                                throw new Error(data.message || 'Update failed');
                            }
                        })
                        .catch(error => {
                            $.gritter.add({
                                title: 'Error',
                                text: error.message || 'Terjadi kesalahan saat mengupdate status',
                                sticky: false,
                                time: 3000,
                                class_name: 'my-sticky-class gritter-light'
                            });
                            $(this).val(originalStatus); // Kembalikan ke status awal
                        });
                });
            });

            $('#refresh-button').on('click', function() {
                location.reload();
            });

            $('.delete-device-btn').on('click', function() {
                var deviceId = $(this).data('device-id');
                var deviceName = $(this).data('device-name');

                swal({
                    title: "Apakah Anda yakin?",
                    text: "Setelah dihapus, device '" + deviceName + "' tidak dapat dikembalikan!",
                    icon: "warning",
                    buttons: {
                        cancel: "Batal",
                        confirm: {
                            text: "Ya, Hapus!",
                            value: true,
                            className: "btn-danger"
                        }
                    },
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        fetch('/partner/devices/' +
                            deviceId, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    swal("Berhasil!", data.message, "success");
                                    $('#device-' + deviceId).remove();
                                } else {
                                    swal("Gagal!", data.message, "error");
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                swal("Error!", "Terjadi kesalahan saat menghapus device.",
                                    "error");
                            });
                    } else {
                        swal("Penghapusan dibatalkan!", {
                            icon: "info",
                            button: "OK",
                        });
                    }
                });
            });
        });
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
@endpush
