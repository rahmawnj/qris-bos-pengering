@props([
    'items' => ['Partner', 'Keuangan', 'Riwayat Penarikan'],
    'title' => 'Riwayat Penarikan Dana',
    'subtitle' => 'Lihat histori penarikan dana Anda di sini',
])
@php
    $feature = getData(); // Dapatkan instance DataFetcher sekali
@endphp

@push('styles')
    <link href="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />
    <style>
        /* Base Card Styles */
        .summary-card {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: .5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            height: 100%;
        }

        .summary-card.large-card {
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 1rem;
            background-color: #d1ecf1;
            border-color: #bee5eb;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            justify-content: center;
        }

        .summary-card.large-card .icon-circle {
            width: 70px;
            height: 70px;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .summary-card .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            color: #ffffff;
            flex-shrink: 0;
        }

        .icon-circle.bg-info-dark {
            background-color: #0d6efd !important;
        }

        .icon-circle.bg-blue {
            background-color: #007bff !important;
        }

        .icon-circle.bg-success {
            background-color: #28a745 !important;
        }

        .icon-circle.bg-danger {
            background-color: #dc3545 !important;
        }

        .icon-circle.bg-warning-dark {
            background-color: #ffc107 !important;
            color: #343a40 !important;
        }

        .summary-card .card-title {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .summary-card .card-text.h3 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            color: #343a40;
        }

        .summary-card .card-text.small-desc {
            font-size: 0.875rem;
            color: #999;
            margin-bottom: 0;
        }

        /* Filter Panel Specific Styles (Matching Screenshot) */
        .filter-panel-custom {
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,.08);
            margin-bottom: 20px;
            padding: 20px;
        }

        .filter-panel-custom label {
            font-weight: 700;
            font-size: 12px;
            margin-bottom: 8px;
            display: block;
        }

        .filter-actions-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .btn-terapkan {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
            font-weight: 700;
            padding: 6px 12px;
        }

        .btn-reset {
            background-color: #e9ecef;
            border-color: #e9ecef;
            color: #333;
            font-weight: 700;
            padding: 6px 12px;
        }

        /* Grid for Cards */
        .card-grid-container {
            display: grid;
            grid-template-columns: 1.2fr 2fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: stretch;
        }

        .small-cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 992px) {
            .card-grid-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="card-grid-container">
        <div>
            <div class="summary-card large-card">
                <div class="icon-circle bg-info-dark">
                    <i class="fa fa-wallet"></i>
                </div>
                <div>
                    <h5 class="card-title">Saldo Tersedia untuk Penarikan</h5>
                    <p class="card-text h3">Rp {{ number_format($availableBalance, 0, ',', '.') }}</p>
                    <p class="card-text small-desc">Dana bersih yang siap Anda tarik ke rekening bank Anda.</p>
                </div>
            </div>
        </div>

        <div class="small-cards-grid">
            <div class="summary-card">
                <div class="icon-circle bg-primary">
                    <i class="fa fa-clipboard-list"></i>
                </div>
                <div>
                    <h5 class="card-title">Total Permintaan Penarikan</h5>
                    <p class="card-text h3">{{ $totalWithdrawalsCount }}</p>
                    <p class="card-text small-desc">Jumlah seluruh permintaan penarikan yang diajukan.</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="icon-circle bg-success">
                    <i class="fa fa-check-circle"></i>
                </div>
                <div>
                    <h5 class="card-title">Jumlah Disetujui</h5>
                    <p class="card-text h3">Rp {{ number_format($approvedWithdrawalsAmount, 0, ',', '.') }}</p>
                    <p class="card-text small-desc">{{ $approvedWithdrawalsCount }} permintaan telah disetujui.</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="icon-circle bg-danger">
                    <i class="fa fa-times-circle"></i>
                </div>
                <div>
                    <h5 class="card-title">Jumlah Ditolak</h5>
                    <p class="card-text h3">Rp {{ number_format($rejectedWithdrawalsAmount, 0, ',', '.') }}</p>
                    <p class="card-text small-desc">{{ $rejectedWithdrawalsCount }} permintaan telah ditolak.</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="icon-circle bg-warning-dark">
                    <i class="fa fa-hourglass-half"></i>
                </div>
                <div>
                    <h5 class="card-title">Permintaan Pending</h5>
                    <p class="card-text h3">{{ $pendingWithdrawalsCount }}</p>
                    <p class="card-text small-desc">Total permintaan yang sedang dalam proses.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Panel (Styled to match screenshot) --}}
    <div class="filter-panel-custom">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('partner.withdrawal.request') }}"
                class="btn btn-success px-4 @disabled(!$feature->can('withdrawal.request'))">
                <i class="fa fa-hand-holding-usd me-2"></i> AJUKAN PENARIKAN BARU
            </a>
            @if ($pendingWithdrawalsCount > 0)
                <div class="text-end">
                    <h5 class="mb-0 text-warning small fw-bold">
                        <i class="fa fa-exclamation-circle me-1"></i> {{ $pendingWithdrawalsCount }} Permintaan Pending
                        (Rp {{ number_format($totalPendingWithdrawalsAmount, 0, ',', '.') }})
                    </h5>
                </div>
            @endif
        </div>

        <form method="GET" action="{{ route('partner.withdrawal.histories') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Rentang Waktu</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                        <input type="text" name="daterange" class="form-control" id="filter-daterange"
                            autocomplete="off" value="{{ request('daterange') }}" placeholder="Pilih Rentang Waktu">
                    </div>
                </div>
                <div class="col-md-4">
                    <label>Filter Status</label>
                    <select name="status" id="filter-status" class="form-control form-control-sm">
                        <option value="">-- Semua Status --</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
            </div>

            <div class="filter-actions-row">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-terapkan btn-sm">
                        <i class="fa fa-filter me-1"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('partner.withdrawal.histories') }}" class="btn btn-reset btn-sm">
                        <i class="fa fa-redo me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">Daftar Riwayat Penarikan</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i class="fa fa-expand"></i></a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i class="fa fa-redo"></i></a>
            </div>
        </div>
        <div class="panel-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>Jumlah Penarikan</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Tanggal Diajukan</th>
                            <th>Tanggal Disetujui</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($withdrawalHistories as $withdrawal)
                            @php
                                $timezoneMap = ['wib' => 'Asia/Jakarta', 'wita' => 'Asia/Makassar', 'wit' => 'Asia/Jayapura'];
                                $tzKey = strtolower($withdrawal->timezone ?? 'wib');
                                $tz = $timezoneMap[$tzKey] ?? 'Asia/Jakarta';

                                $statusBadgeClass = match($withdrawal->status) {
                                    'pending' => 'bg-warning text-dark',
                                    'approved' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $statusBadgeClass }}">
                                        {{ ucfirst($withdrawal->status) }}
                                    </span>
                                </td>
                                <td>{{ $withdrawal->notes ?? '-' }}</td>
                                <td>
                                    {{ $withdrawal->created_at->setTimezone($tz)->format('d-m-Y H:i') }}
                                    <small class="text-muted">{{ strtoupper($withdrawal->timezone ?? 'WIB') }}</small>
                                </td>
                                <td>
                                    @if ($withdrawal->approved_at)
                                        {{ \Carbon\Carbon::parse($withdrawal->approved_at)->setTimezone($tz)->format('d-m-Y H:i') }}
                                        <small class="text-muted">{{ strtoupper($withdrawal->timezone ?? 'WIB') }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada riwayat penarikan dana.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="1">Total:</th>
                            <th>Rp {{ number_format($totalWithdrawalsAmountInTable, 0, ',', '.') }}</th>
                            <th colspan="2"></th>
                            <th>Total Data:</th>
                            <th>{{ $withdrawalHistories->total() }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center p-3">
                <div class="small text-muted">
                    Menampilkan {{ $withdrawalHistories->firstItem() }} - {{ $withdrawalHistories->lastItem() }} dari {{ $withdrawalHistories->total() }} riwayat
                </div>
                <div>{{ $withdrawalHistories->appends(request()->query())->links() }}</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
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
                    customRangeLabel: 'Custom'
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
        });
    </script>
@endpush
