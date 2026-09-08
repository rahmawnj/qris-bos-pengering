@extends('layouts.dashboard.app')
@php
    $feature = getData();
@endphp

@section('content')
    <div class="container-fluid py-4 outlet-page">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h2 class="mb-0 outlet-page-title">Daftar Outlet</h2>
            @if ($feature->can('partner.outlets.store'))
                <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
                    data-bs-target="#addOutletModal">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Outlet Baru
                </button>
            @endif
        </div>

        <div class="row row-cols-1 row-cols-lg-2 g-4" id="outletList">
            @forelse($outlets as $outlet)
                <div class="col d-flex">
                    <div class="card w-100 border-0 overflow-hidden outlet-card">
                        @if ($outlet->has_overdue_billing)
                            <div class="status-ribbon bg-danger">Terblokir</div>
                        @elseif ($outlet->status)
                            <div class="status-ribbon status-open">Buka</div>
                        @else
                            <div class="status-ribbon status-closed blink">Tutup</div>
                            <span class="closed-dot" title="Outlet Tutup"></span>
                        @endif
                        <div class="card-body p-0 outlet-card-body">
                            <div class="outlet-right">
                                <div class="outlet-badge">
                                    <i class="fas fa-store-alt"></i>
                                    <span>Outlet</span>
                                </div>
                                <h4 class="outlet-name">{{ $outlet->outlet_name }}</h4>
                                <div class="outlet-code">Kode Outlet: <span>#{{ $outlet->code }}</span></div>

                                <div class="outlet-stats">
                                    <div class="outlet-stat-box">
                                        <i class="fas fa-soap"></i>
                                        <strong>{{ $outlet->devices_count ?? $outlet->devices->count() ?? 0 }}</strong>
                                        <span>Mesin</span>
                                    </div>
                                    <div class="outlet-stat-box">
                                        <i class="fas fa-hand-holding-usd"></i>
                                        <strong>{{ $outlet->services_count ?? $outlet->services->count() ?? 0 }}</strong>
                                        <span>Layanan</span>
                                    </div>
                                    <div class="outlet-stat-box">
                                        <i class="fas fa-cash-register"></i>
                                        <strong>{{ $outlet->cashiers_count ?? $outlet->cashiers->count() ?? 0 }}</strong>
                                        <span>Kasir</span>
                                    </div>
                                </div>

                                <div class="outlet-address">
                                    <i class="fas fa-map-marker-alt me-2"></i>{{ $outlet->address }}
                                </div>

	                                @if ($outlet->qris_billing_enabled)
	                                    @php
	                                        $unpaidSummary = $outlet->qrisBillingUnpaidSummary();
	                                        $totalUnpaidAmount = $unpaidSummary['amount'];
	                                        $hasUnpaidBilling = $unpaidSummary['count'] > 0;
	                                    @endphp
	                                    <div class="outlet-qris-billing {{ $outlet->has_overdue_billing ? 'is-due' : '' }}">
	                                        <div class="outlet-qris-billing-info">
	                                            <i class="fas fa-calendar-day"></i>
	                                            <span>
                                                @if($totalUnpaidAmount > 0)
                                                    Perpanjangan: Rp {{ number_format($totalUnpaidAmount, 0, ',', '.') }}
	                                                @else
	                                                    Perpanjangan QRIS: Rp {{ number_format($outlet->qris_billing_amount, 0, ',', '.') }}
	                                                @endif
	                                                @if (!$hasUnpaidBilling)
	                                                    <br>
	                                                    Aktif sampai: {{ $outlet->qris_billing_due_date ? $outlet->qris_billing_due_date->format('d/m/Y') : '-' }}
	                                                @endif
	                                            </span>
                                        </div>
                                        <div class="outlet-qris-billing-status">
                                            @if ($outlet->has_overdue_billing)
                                                <strong class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Terblokir</strong>
                                            @elseif ($outlet->qris_billing_paid_current_period)
                                                <strong class="text-success">Sudah Dibayar</strong>
                                            @else
                                                <strong>Aktif</strong>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <a href="{{ route('partner.outlets.detail', $outlet->id) }}"
                                    class="btn outlet-detail-btn">
                                    <i class="fas fa-eye me-1"></i> Lihat Detail
                                </a>
	                                @if ($outlet->qris_billing_enabled)
	                                    <a href="{{ route('partner.qris-billing.show', $outlet) }}"
	                                        class="btn btn-outline-primary btn-sm mt-2">
                                        <i class="fas fa-file-invoice-dollar me-1"></i> Perpanjangan QRIS
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center" role="alert">
                        Belum ada outlet yang terdaftar.
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    @if ($feature->can('partner.outlets.store'))
        <div class="modal fade" id="addOutletModal" tabindex="-1" aria-labelledby="addOutletModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addOutletModalLabel">Tambah Outlet Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="addOutletForm" action="{{ route('partner.outlets.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="outlet_name" class="form-label">Nama Outlet <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('outlet_name') is-invalid @enderror"
                                    id="outlet_name" name="outlet_name" value="{{ old('outlet_name') }}" required>
                                @error('outlet_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Alamat Lengkap <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3"
                                    required>{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Nomor Telepon (Opsional)</label>
                                <input type="text" class="form-control @error('phone_number') is-invalid @enderror"
                                    id="phone_number" name="phone_number" value="{{ old('phone_number') }}">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="timezone" class="form-label">Zona Waktu <span class="text-danger">*</span></label>
                              <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone" required>
        <option value="">Pilih Zona Waktu</option>
        <option value="WIB" {{ old('timezone') == 'WIB' ? 'selected' : '' }}>WIB (Asia/Jakarta)</option>
        <option value="WITA" {{ old('timezone') == 'WITA' ? 'selected' : '' }}>WITA (Asia/Makassar)</option>
        <option value="WIT" {{ old('timezone') == 'WIT' ? 'selected' : '' }}>WIT (Asia/Jayapura)</option>
    </select>
                                @error('timezone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Outlet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .outlet-page {
            background: #e9edf2;
        }

        .outlet-page-title {
            color: #111827;
            font-size: 1.65rem;
            font-weight: 600;
        }

        .outlet-card {
            position: relative;
            min-height: 276px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.10);
        }

        .outlet-card-body {
            display: flex;
            min-height: 276px;
        }

        .outlet-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 16px;
            color: #0d6efd;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .outlet-badge i {
            font-size: 1rem;
        }

        .outlet-name {
            margin: 0 0 8px;
            color: #111827;
            font-size: 1.08rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .outlet-code {
            margin-bottom: 18px;
            color: #64748b;
            font-size: .76rem;
            font-weight: 600;
        }

        .outlet-code span {
            color: #0d6efd;
        }

        .outlet-right {
            flex: 1;
            min-width: 0;
            padding: 30px 28px 24px;
            display: flex;
            flex-direction: column;
        }

        .outlet-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-bottom: 18px;
            border: 1px solid #e5eaf0;
            border-radius: 6px;
            overflow: hidden;
        }

        .outlet-stat-box {
            min-height: 62px;
            padding: 10px 12px;
            display: grid;
            grid-template-columns: 28px auto;
            grid-template-rows: auto auto;
            column-gap: 8px;
            align-content: center;
            border-right: 1px solid #e5eaf0;
        }

        .outlet-stat-box:last-child {
            border-right: 0;
        }

        .outlet-stat-box i {
            grid-row: 1 / span 2;
            align-self: center;
            color: #0d6efd;
            font-size: 1.15rem;
            text-align: center;
        }

        .outlet-stat-box strong {
            color: #111827;
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1;
        }

        .outlet-stat-box span {
            margin-top: 3px;
            color: #111827;
            font-size: .72rem;
            line-height: 1;
        }

        .outlet-address {
            margin-bottom: 20px;
            color: #9aa1aa;
            font-size: .82rem;
        }

        .outlet-qris-billing {
            margin: -6px 0 16px;
            padding: 8px 10px;
            display: flex;
            justify-content: space-between;
            gap: 6px 10px;
            align-items: center;
            border-radius: 6px;
            color: #0f5132;
            background: #e9f7ef;
            font-size: .78rem;
            font-weight: 600;
        }

        .outlet-qris-billing-info {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .outlet-qris-billing-status {
            margin-left: auto;
            text-align: right;
            white-space: nowrap;
        }

        .outlet-qris-billing.is-due {
            color: #664d03;
            background: #fff3cd;
        }

        .outlet-address i {
            color: #9aa1aa;
        }

        .outlet-detail-btn {
            margin-top: auto;
            width: 100%;
            min-height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            color: #0d6efd;
            background: #eaf4ff;
            border: 0;
            font-weight: 600;
            font-size: .82rem;
        }

        .outlet-detail-btn:hover,
        .outlet-detail-btn:focus {
            color: #fff;
            background: #0d6efd;
        }

        .status-ribbon {
            position: absolute;
            top: 16px;
            right: 14px;
            transform: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: .68rem;
            letter-spacing: .02em;
            text-transform: uppercase;
            color: #fff;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.18);
            z-index: 2;
        }

        .status-open {
            background: #1f9d55;
        }

        .status-closed {
            background: #e3342f;
        }

        .blink {
            animation: blink-ribbon 1.2s ease-in-out infinite;
        }

        @keyframes blink-ribbon {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.35; }
        }

        .closed-dot {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #e3342f;
            box-shadow: 0 0 0 0 rgba(227, 52, 47, 0.7);
            animation: pulse-dot 1.4s ease-out infinite;
            z-index: 3;
        }

        @keyframes pulse-dot {
            0% {
                transform: scale(0.9);
                box-shadow: 0 0 0 0 rgba(227, 52, 47, 0.7);
            }
            70% {
                transform: scale(1.1);
                box-shadow: 0 0 0 8px rgba(227, 52, 47, 0);
            }
            100% {
                transform: scale(0.9);
                box-shadow: 0 0 0 0 rgba(227, 52, 47, 0);
            }
        }

        @media (max-width: 767.98px) {
            .outlet-card-body {
                flex-direction: column;
            }

            .outlet-right {
                padding: 22px;
            }
        }
    </style>
@endpush
