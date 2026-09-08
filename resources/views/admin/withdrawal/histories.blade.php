@props([
    'items' => ['Admin', 'Withdrawal Management', 'Withdrawal Histories'],
    'title' => 'Riwayat Penarikan Dana',
    'subtitle' => 'Lihat riwayat seluruh penarikan dana oleh owner.',
])

@extends('layouts.dashboard.app')

@push('styles')
    <link href="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />
    <style>
        :root {
            --primary-blue: #0066ff;
            --soft-blue: #f0f7ff;
        }

        body {
            background-color: #f1f5f9;
        }

        .summary-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 1rem;
            transition: transform 0.2s ease;
        }

        .summary-card:hover {
            transform: translateY(-2px);
        }

        .summary-card.large-card {
            flex-direction: column;
            align-items: center;
            text-align: center;
            background: linear-gradient(135deg, #0066ff 0%, #0052cc 100%);
            border: none;
            color: white;
            height: 100%;
            justify-content: center;
        }

        .summary-card.large-card .card-title {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 600;
        }

        .summary-card.large-card .card-text.h3 {
            color: white;
            font-size: 2.25rem;
            font-weight: 800;
            margin-top: 0.5rem;
        }

        .summary-card.large-card .icon-circle {
            background: rgba(255, 255, 255, 0.2);
            width: 70px;
            height: 70px;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .summary-card .icon-circle {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            color: #ffffff;
            flex-shrink: 0;
        }

        .bg-info-dark {
            background-color: #0d6efd !important;
        }

        .bg-blue {
            background-color: var(--primary-blue) !important;
        }

        .bg-success {
            background-color: #10b981 !important;
        }

        .bg-danger {
            background-color: #ef4444 !important;
        }

        .bg-warning-dark {
            background-color: #f59e0b !important;
        }

        .summary-card .card-title {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 700;
            text-uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .summary-card .card-text.h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: #1e293b;
        }

        .summary-card .card-text.small-desc {
            font-size: 0.8rem;
            color: #94a3b8;
        }

        /* Modal Redesign */
        .modal-content-premium {
            border-radius: 1.5rem;
            border: none;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }

        .modal-header-premium {
            padding: 2rem;
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
        }

        .modal-header-premium .modal-title-wrap {
            display: flex;
            align-items: center;
        }

        .modal-header-premium .icon-box {
            width: 50px;
            height: 50px;
            background: var(--primary-blue);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1.25rem;
        }

        .modal-body-premium {
            padding: 2rem;
            background: #f8fafc;
        }

        .detail-grid-card {
            background: white;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .detail-item:last-child {
            margin-bottom: 0;
        }

        .detail-item .icon-wrap {
            width: 36px;
            height: 36px;
            background: var(--soft-blue);
            color: var(--primary-blue);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .detail-item .content-wrap {
            flex-grow: 1;
        }

        .detail-item .label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .detail-item .value {
            display: block;
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
        }

        .section-card-premium {
            background: white;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            border-left: 4px solid var(--primary-blue);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .section-card-premium h6 {
            font-size: 0.85rem;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
        }

        .section-card-premium h6 i {
            margin-right: 0.75rem;
            color: var(--primary-blue);
        }

        .data-table-simple {
            width: 100%;
        }

        .data-table-simple tr td {
            padding: 0.5rem 0;
            font-size: 0.9rem;
        }

        .data-table-simple tr td:first-child {
            color: #64748b;
            font-weight: 500;
            width: 45%;
        }

        .data-table-simple tr td:last-child {
            color: #1e293b;
            font-weight: 700;
            text-align: right;
        }

        .modal-footer-premium {
            background: white;
            border-top: 1px solid #f1f5f9;
            padding: 1.25rem 2rem;
            display: flex;
            justify-content: flex-end;
        }

        /* Badge Styles */
        .badge-premium {
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.75rem;
        }

        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-approved {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Original Layout Support */
        .card-grid-container {
            display: grid;
            grid-template-columns: 1.2fr 2fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .small-cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .filter-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }

        .filter-left,
        .filter-right {
            display: flex;
            gap: 10px;
        }

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }

        .pagination-container nav {
            margin-bottom: 0;
        }

        @media (max-width: 992px) {
            .card-grid-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 575.98px) {
            .small-cards-grid {
                grid-template-columns: 1fr;
            }

            .filter-actions,
            .filter-left {
                display: grid;
                width: 100%;
                gap: .5rem;
            }

            .filter-actions .btn {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="card-grid-container">
        <div>
            <div class="summary-card large-card shadow-sm">
                <div class="icon-circle">
                    <i class="fa fa-money-bill-wave"></i>
                </div>
                <div>
                    <h5 class="card-title">Total Dana Belum Ditarik</h5>
                    <p class="card-text h3">Rp {{ number_format($totalUnwithdrawnFunds, 0, ',', '.') }}</p>
                    <p class="card-text small-desc">Akumulasi saldo semua owner.</p>
                </div>
            </div>
        </div>

        <div class="small-cards-grid">
            <div class="summary-card">
                <div class="icon-circle bg-blue">
                    <i class="fa fa-clipboard-list"></i>
                </div>
                <div>
                    <h5 class="card-title">Total Permintaan</h5>
                    <p class="card-text h3">{{ $totalGlobalWithdrawalsCount }}</p>
                    <p class="card-text small-desc">Seluruh permintaan penarikan.</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="icon-circle bg-success">
                    <i class="fa fa-check-circle"></i>
                </div>
                <div>
                    <h5 class="card-title">Disetujui</h5>
                    <p class="card-text h3">Rp {{ number_format($approvedGlobalWithdrawalsAmount, 0, ',', '.') }}</p>
                    <p class="card-text small-desc">{{ $approvedGlobalWithdrawalsCount }} permintaan.</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="icon-circle bg-danger">
                    <i class="fa fa-times-circle"></i>
                </div>
                <div>
                    <h5 class="card-title">Ditolak</h5>
                    <p class="card-text h3">Rp {{ number_format($rejectedGlobalWithdrawalsAmount, 0, ',', '.') }}</p>
                    <p class="card-text small-desc">{{ $rejectedGlobalWithdrawalsCount }} permintaan.</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="icon-circle bg-warning-dark">
                    <i class="fa fa-hourglass-half"></i>
                </div>
                <div>
                    <h5 class="card-title">Pending</h5>
                    <p class="card-text h3">Rp {{ number_format($pendingGlobalWithdrawalsAmount, 0, ',', '.') }}</p>
                    <p class="card-text small-desc">{{ $pendingGlobalWithdrawalsCount }} permintaan pending.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3 mb-4">
        <form method="GET" action="{{ route('admin.withdrawal.histories') }}">
            <div class="d-flex flex-wrap align-items-end gap-3">
                <div style="min-width: 180px;">
                    <label for="filter-status" class="form-label mb-1 fw-bold">Status</label>
                    <select name="status" id="filter-status" class="form-select form-select-sm">
                        <option value="">-- Semua Status --</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <div style="min-width: 240px; flex: 1;">
                    <label for="filter-daterange" class="form-label mb-1 fw-bold">Rentang Waktu</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                        <input type="text" name="daterange" class="form-control" id="filter-daterange"
                            autocomplete="off" value="{{ request('daterange') }}" placeholder="Pilih Rentang Waktu">
                    </div>
                </div>

                <div style="min-width: 240px; flex: 1;">
                    <label for="filter-search" class="form-label mb-1 fw-bold">Pencarian</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                        <input type="text" name="search" id="filter-search" class="form-control"
                            value="{{ request('search') }}" placeholder="Nama Owner atau Brand">
                    </div>
                </div>
            </div>
            <hr>
            <div class="filter-actions">
                <div class="filter-left">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-filter me-1"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('admin.withdrawal.histories') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-redo me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title fw-bold">{{ $title ?? '' }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i class="fa fa-expand"></i></a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i class="fa fa-redo"></i></a>
            </div>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="1%">#</th>
                            <th>Owner (Brand)</th>
                            <th>Jumlah Diminta</th>
                            <th>Biaya</th>
                            <th>Jumlah Diterima</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($withdrawalHistories as $withdrawal)
                            @php
                                $timezoneMap = ['wib' => 'Asia/Jakarta', 'wita' => 'Asia/Makassar', 'wit' => 'Asia/Jayapura'];
                                $tzKey = strtolower($withdrawal->timezone ?? 'wib');
                                $tz = $timezoneMap[$tzKey] ?? 'Asia/Jakarta';

                                $statusClass = match($withdrawal->status) {
                                    'pending' => 'badge-pending',
                                    'approved' => 'badge-approved',
                                    'rejected' => 'badge-rejected',
                                    default => 'bg-secondary text-white'
                                };
                            @endphp
                            <tr class="withdrawal-row"
                                data-bs-toggle="modal"
                                data-bs-target="#adminWithdrawalDetailModal"
                                data-id="{{ $withdrawal->id }}"
                                data-owner-name="{{ $withdrawal->owner->user->name ?? '-' }}"
                                data-brand-name="{{ $withdrawal->owner->brand_name ?? '-' }}"
                                data-requested-amount="{{ $withdrawal->requested_amount }}"
                                data-withdrawal-fee="{{ $withdrawal->withdrawal_fee }}"
                                data-net-amount-transferred="{{ $withdrawal->net_amount_transferred }}"
                                data-amount-before-fee="{{ $withdrawal->amount_before_fee ?? 'N/A' }}"
                                data-amount-after-fee="{{ $withdrawal->amount_after_fee ?? 'N/A' }}"
                                data-status="{{ ucfirst($withdrawal->status) }}"
                                data-status-class="{{ $statusClass }}"
                                data-notes="{{ $withdrawal->notes ?? '-' }}"
                                data-created-at="{{ $withdrawal->created_at->setTimezone($tz)->format('d M Y H:i') }} {{ strtoupper($withdrawal->timezone ?? 'WIB') }}"
                                data-approved-at="{{ $withdrawal->approved_at ? \Carbon\Carbon::parse($withdrawal->approved_at)->setTimezone($tz)->format('d M Y H:i') . ' ' . strtoupper($withdrawal->timezone ?? 'WIB') : '-' }}"
                                data-rejected-at="{{ $withdrawal->rejected_at ? \Carbon\Carbon::parse($withdrawal->rejected_at)->setTimezone($tz)->format('d M Y H:i') . ' ' . strtoupper($withdrawal->timezone ?? 'WIB') : '-' }}"
                                data-bank-name="{{ $withdrawal->bank_name ?? 'Belum diatur' }}"
                                data-bank-account-number="{{ $withdrawal->bank_account_number ?? 'Belum diatur' }}"
                                data-bank-account-holder-name="{{ $withdrawal->bank_account_holder_name ?? 'Belum diatur' }}"
                            >
                                <td>{{ $loop->iteration + ($withdrawalHistories->currentPage() - 1) * $withdrawalHistories->perPage() }}</td>
                                <td>
                                    <strong>{{ $withdrawal->owner->brand_name ?? '-' }}</strong><br>
                                    <small class="text-muted">{{ $withdrawal->owner->user->name ?? '-' }}</small>
                                </td>
                                <td class="fw-bold">Rp {{ number_format($withdrawal->requested_amount, 0, ',', '.') }}</td>
                                <td class="text-danger">Rp {{ number_format($withdrawal->withdrawal_fee, 0, ',', '.') }}</td>
                                <td class="text-success fw-bold">Rp {{ number_format($withdrawal->net_amount_transferred, 0, ',', '.') }}</td>
                                <td><span class="badge-premium {{ $statusClass }}">{{ ucfirst($withdrawal->status) }}</span></td>
                                <td>
                                    <small class="fw-bold">{{ $withdrawal->created_at->setTimezone($tz)->format('d/m/Y') }}</small><br>
                                    <small class="text-muted">{{ $withdrawal->created_at->setTimezone($tz)->format('H:i') }} {{ strtoupper($withdrawal->timezone ?? 'WIB') }}</small>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-xs rounded-2">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                    <p>Tidak ada riwayat penarikan dana.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-container">
                <div class="small text-muted">
                    Menampilkan <strong>{{ $withdrawalHistories->firstItem() }}</strong> - <strong>{{ $withdrawalHistories->lastItem() }}</strong> dari <strong>{{ $withdrawalHistories->total() }}</strong> data
                </div>
                <div>{{ $withdrawalHistories->appends(request()->query())->links() }}</div>
            </div>
        </div>
    </div>

    <!-- Redesigned Modal -->
    <div class="modal fade" id="adminWithdrawalDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-content-premium">
                <div class="modal-header modal-header-premium">
                    <div class="modal-title-wrap">
                        <div class="icon-box">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-dark">Detail Penarikan #<span id="detail-id"></span></h4>
                            <p class="text-muted small mb-0">Informasi lengkap rincian penarikan dana owner</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-premium">
                    <div class="row">
                        <div class="col-lg-12">
                            {{-- Info Grid Card --}}
                            <div class="detail-grid-card shadow-sm">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <div class="icon-wrap"><i class="fas fa-store"></i></div>
                                            <div class="content-wrap">
                                                <span class="label">Owner (Brand)</span>
                                                <span class="value" id="detail-owner-brand"></span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="icon-wrap"><i class="fas fa-user"></i></div>
                                            <div class="content-wrap">
                                                <span class="label">Nama Owner</span>
                                                <span class="value" id="detail-owner-name"></span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="icon-wrap"><i class="fas fa-wallet"></i></div>
                                            <div class="content-wrap">
                                                <span class="label">Jumlah Diminta</span>
                                                <span class="value text-primary fw-bold" id="detail-requested-amount"></span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="icon-wrap"><i class="fas fa-percent"></i></div>
                                            <div class="content-wrap">
                                                <span class="label">Biaya Penarikan</span>
                                                <span class="value text-danger" id="detail-withdrawal-fee"></span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="icon-wrap"><i class="fas fa-hand-holding-usd"></i></div>
                                            <div class="content-wrap">
                                                <span class="label">Jumlah Diterima (Net)</span>
                                                <span class="value text-success fw-bold" id="detail-net-amount-transferred"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <div class="icon-wrap"><i class="fas fa-info-circle"></i></div>
                                            <div class="content-wrap">
                                                <span class="label">Status</span>
                                                <span class="badge-premium" id="detail-status-badge"></span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="icon-wrap"><i class="fas fa-sticky-note"></i></div>
                                            <div class="content-wrap">
                                                <span class="label">Catatan Admin</span>
                                                <span class="value" id="detail-notes"></span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="icon-wrap"><i class="fas fa-calendar-alt"></i></div>
                                            <div class="content-wrap">
                                                <span class="label">Tanggal Diajukan</span>
                                                <span class="value" id="detail-created-at"></span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="icon-wrap"><i class="fas fa-check-double"></i></div>
                                            <div class="content-wrap">
                                                <span class="label">Tanggal Disetujui</span>
                                                <span class="value" id="detail-approved-at"></span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="icon-wrap"><i class="fas fa-times-circle"></i></div>
                                            <div class="content-wrap">
                                                <span class="label">Tanggal Ditolak</span>
                                                <span class="value" id="detail-rejected-at"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Bank Card --}}
                            <div class="section-card-premium shadow-sm">
                                <h6><i class="fas fa-university"></i> Informasi Rekening Bank Tujuan</h6>
                                <table class="data-table-simple">
                                    <tr>
                                        <td>Nama Bank</td>
                                        <td id="detail-bank-name"></td>
                                    </tr>
                                    <tr>
                                        <td>Nomor Rekening</td>
                                        <td id="detail-bank-account-number"></td>
                                    </tr>
                                    <tr>
                                        <td>Nama Pemilik Rekening</td>
                                        <td id="detail-bank-account-holder-name"></td>
                                    </tr>
                                </table>
                            </div>

                            {{-- Balance Card --}}
                            <div class="section-card-premium shadow-sm mb-0">
                                <h6><i class="fas fa-coins"></i> Informasi Saldo Owner</h6>
                                <table class="data-table-simple">
                                    <tr>
                                        <td>Saldo Sebelum Penarikan</td>
                                        <td id="detail-amount-before-fee"></td>
                                    </tr>
                                    <tr>
                                        <td>Saldo Setelah Penarikan</td>
                                        <td id="detail-amount-after-fee" style="border-top: 1px solid #f1f5f9; padding-top: 10px; margin-top: 5px;"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-premium">
                    <button type="button" class="btn btn-primary px-4 fw-bold rounded-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/plugins/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script>
        $(function() {
            $('#filter-daterange').daterangepicker({
                timePicker: false,
                showDropdowns: true,
                autoUpdateInput: false,
                autoApply: false,
                locale: {
                    format: 'YYYY-MM-DD',
                    separator: ' - ',
                    cancelLabel: 'Clear',
                    applyLabel: 'Terapkan',
                    customRangeLabel: 'Custom',
                    daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                    firstDay: 1
                },
                ranges: {
                    'Hari Ini': [moment(), moment()],
                    'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                    '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                    'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });

            if ("{{ request('daterange') }}") {
                $('#filter-daterange').val("{{ request('daterange') }}");
            }

            $('#filter-daterange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            });

            $('#filter-daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            function formatRupiah(number) {
                if (typeof number === 'string' && number.toLowerCase() === 'n/a') return number;
                if (isNaN(parseFloat(number))) return 'Rp 0';
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(parseFloat(number));
            }

            var adminWithdrawalDetailModal = document.getElementById('adminWithdrawalDetailModal');
            adminWithdrawalDetailModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var row = $(button).closest('tr');

                var id = row.data('id');
                var ownerName = row.data('owner-name');
                var brandName = row.data('brand-name');
                var requestedAmount = row.data('requested-amount');
                var withdrawalFee = row.data('withdrawal-fee');
                var netAmountTransferred = row.data('net-amount-transferred');
                var amountBeforeFee = row.data('amount-before-fee');
                var amountAfterFee = row.data('amount-after-fee');
                var status = row.data('status');
                var statusClass = row.data('status-class');
                var notes = row.data('notes');
                var createdAt = row.data('created-at');
                var approvedAt = row.data('approved-at');
                var rejectedAt = row.data('rejected-at');
                var bankName = row.data('bank-name');
                var bankAccountNumber = row.data('bank-account-number');
                var bankAccountHolderName = row.data('bank-account-holder-name');

                $('#detail-id').text(id);
                $('#detail-owner-brand').text(brandName);
                $('#detail-owner-name').text(ownerName);
                $('#detail-requested-amount').text(formatRupiah(requestedAmount));
                $('#detail-withdrawal-fee').text(formatRupiah(withdrawalFee));
                $('#detail-net-amount-transferred').text(formatRupiah(netAmountTransferred));
                $('#detail-status-badge').text(status).removeClass().addClass('badge-premium ' + statusClass);
                $('#detail-notes').text(notes);
                $('#detail-created-at').text(createdAt);
                $('#detail-approved-at').text(approvedAt);
                $('#detail-rejected-at').text(rejectedAt);
                $('#detail-bank-name').text(bankName);
                $('#detail-bank-account-number').text(bankAccountNumber);
                $('#detail-bank-account-holder-name').text(bankAccountHolderName);
                $('#detail-amount-before-fee').text(formatRupiah(amountBeforeFee));
                $('#detail-amount-after-fee').text(formatRupiah(amountAfterFee));
            });
        });
    </script>
@endpush
