@props([
    'items' => ['Admin', 'Withdrawal Management', 'Konfirmasi Penarikan'],
    'title' => 'Konfirmasi Penarikan Dana',
    'subtitle' => 'Tinjau detail permintaan penarikan dan proses konfirmasi atau penolakan.',
])

@extends('layouts.dashboard.app')

@push('styles')
    <style>
        .detail-card {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: .5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .detail-card h5 {
            color: #343a40;
            font-weight: bold;
            margin-bottom: 1rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .detail-item .icon {
            font-size: 1.2rem;
            width: 30px;
            text-align: center;
            color: #007bff;
            /* Primary color for icons */
            flex-shrink: 0;
        }

        .detail-item strong {
            margin-right: 0.5rem;
            min-width: 150px;
            /* Adjust as needed for alignment */
            color: #555;
        }

        .detail-item span {
            color: #333;
        }

        .amount-highlight {
            font-size: 2.2rem;
            font-weight: bold;
            color: #28a745;
            /* Success color */
            display: block;
            margin-top: 0.5rem;
            margin-bottom: 1rem;
        }

        .balance-card {
            background-color: #d4edda;
            /* Light green background */
            border-color: #c3e6cb;
            color: #155724;
            padding: 1.5rem;
            border-radius: .5rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .balance-card .balance-label {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .balance-card .balance-amount {
            font-size: 2.5rem;
            font-weight: bold;
            line-height: 1;
        }

        .status-badge {
            font-size: 1.1em;
            padding: 0.5em 0.8em;
            border-radius: 0.25rem;
        }

        textarea.form-control {
            min-height: 100px;
        }
    </style>
@endpush

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="row">
        {{-- Card Informasi Umum & Dana --}}
        <div class="col-lg-8">
            <div class="detail-card">
                <h5><i class="fa fa-info-circle me-2"></i> Detail Permintaan Penarikan</h5>

                {{-- Alert untuk pesan sukses/error --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <span class="icon"><i class="fa fa-user"></i></span>
                            <strong>Owner:</strong>
                            <span>{{ $withdrawal->owner->user->name ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="icon"><i class="fa fa-building"></i></span>
                            <strong>Brand:</strong>
                            <span>{{ $withdrawal->owner->brand_name ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="icon"><i class="fa fa-envelope"></i></span>
                            <strong>Email Owner:</strong>
                            <span>{{ $withdrawal->owner->user->email ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="icon"><i class="fa fa-phone"></i></span>
                            <strong>No. Telepon:</strong>
                            <span>{{ $withdrawal->owner->user->phone_number ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="icon"><i class="fa fa-id-card"></i></span>
                            <strong>ID Penarikan:</strong>
                            <span>#{{ $withdrawal->id }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="icon"><i class="fa fa-calendar-alt"></i></span>
                            <strong>Tanggal Pengajuan:</strong>
                            @php
                                $timezoneMap = [
                                    'wib' => 'Asia/Jakarta',
                                    'wita' => 'Asia/Makassar',
                                    'wit' => 'Asia/Jayapura',
                                ];
                                $tzKey = strtolower($withdrawal->timezone ?? 'wib');
                                $tz = $timezoneMap[$tzKey] ?? 'Asia/Jakarta';
                            @endphp
                            <span>{{ $withdrawal->created_at->setTimezone($tz)->format('d-m-Y H:i') }} <small
                                    class="text-muted">({{ strtoupper($withdrawal->timezone ?? 'WIB') }})</small></span>
                        </div>
                        <div class="detail-item">
                            <span class="icon"><i class="fa fa-tag"></i></span>
                            <strong>Catatan Owner:</strong>
                            <span>{{ $withdrawal->notes ?? 'Tidak ada catatan.' }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="detail-item">
                            <span class="icon"><i class="fa fa-university"></i></span>
                            <strong>Nama Bank:</strong>
                            <span>{{ $withdrawal->bank_name ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="icon"><i class="fa fa-credit-card"></i></span>
                            <strong>Nomor Rekening:</strong>
                            <span>{{ $withdrawal->account_number ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="icon"><i class="fa fa-user-circle"></i></span>
                            <strong>Nama Pemilik Rek:</strong>
                            <span>{{ $withdrawal->account_name ?? '-' }}</span>
                        </div>

                        <hr class="my-3">

                        <div class="text-center">
                            <p class="mb-2">Jumlah Penarikan:</p>
                            <span class="amount-highlight">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</span>
                        </div>

                        <div class="detail-item justify-content-center">
                            <strong>Status:</strong>
                            @php
                                $statusBadgeClass = '';
                                switch ($withdrawal->status) {
                                    case 'pending':
                                        $statusBadgeClass = 'bg-warning text-dark';
                                        break;
                                    case 'approved':
                                        $statusBadgeClass = 'bg-success';
                                        break;
                                    case 'rejected':
                                        $statusBadgeClass = 'bg-danger';
                                        break;
                                    default:
                                        $statusBadgeClass = 'bg-secondary';
                                        break;
                                }
                            @endphp
                            <span
                                class="badge status-badge {{ $statusBadgeClass }}">{{ ucfirst($withdrawal->status) }}</span>
                        </div>

                        @if ($withdrawal->status == 'approved' && $withdrawal->approved_at)
                            <div class="detail-item justify-content-center mt-3">
                                <span class="icon"><i class="fa fa-check-circle text-success"></i></span>
                                <strong>Disetujui pada:</strong>
                                <span>{{ \Carbon\Carbon::parse($withdrawal->approved_at)->setTimezone($tz)->format('d-m-Y H:i') }}
                                    <small
                                        class="text-muted">({{ strtoupper($withdrawal->timezone ?? 'WIB') }})</small></span>
                            </div>
                        @elseif ($withdrawal->status == 'rejected' && $withdrawal->rejected_at)
                            <div class="detail-item justify-content-center mt-3">
                                <span class="icon"><i class="fa fa-times-circle text-danger"></i></span>
                                <strong>Ditolak pada:</strong>
                                <span>{{ \Carbon\Carbon::parse($withdrawal->rejected_at)->setTimezone($tz)->format('d-m-Y H:i') }}
                                    <small
                                        class="text-muted">({{ strtoupper($withdrawal->timezone ?? 'WIB') }})</small></span>
                            </div>
                            <div class="detail-item justify-content-center">
                                <span class="icon"><i class="fa fa-comment-alt text-secondary"></i></span>
                                <strong>Alasan Penolakan:</strong>
                                <span><em>"{{ $withdrawal->notes ?? 'Tidak ada alasan spesifik.' }}"</em></span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Sisa Saldo & Aksi --}}
        <div class="col-lg-4">
            <div class="balance-card">
                <p class="balance-label"><i class="fa fa-wallet me-2"></i>Sisa Saldo Owner (Saat Ini)</p>
                <h3 class="balance-amount">Rp {{ number_format($currentOwnerBalance, 0, ',', '.') }}</h3>
                <small class="text-muted">Saldo yang dimiliki owner sebelum permintaan ini diproses.</small>
            </div>

            @if ($withdrawal->status == 'pending')
                <div class="detail-card">
                    <h5><i class="fa fa-gavel me-2"></i> Aksi Konfirmasi</h5>
                    <p class="text-muted">Pilih apakah Anda akan menyetujui atau menolak permintaan penarikan ini.</p>

                    <form id="withdrawal-action-form" method="POST" action="{{ route('admin.withdrawal.store') }}">
                        @csrf
                        <input type="hidden" name="withdrawal_id" value="{{ $withdrawal->id }}">
                        <input type="hidden" name="action" id="action-type">

                        <div class="mb-3" id="rejection-reason-group" style="display: none;">
                            <label for="rejection_reason" class="form-label">Alasan Penolakan (Opsional):</label>
                            <textarea name="rejection_reason" id="rejection_reason" class="form-control" placeholder="Tulis alasan penolakan..."></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success btn-lg" onclick="confirmAction('approve')">
                                <i class="fa fa-check-circle me-2"></i> Konfirmasi Pembayaran
                            </button>
                            <button type="button" class="btn btn-danger btn-lg" onclick="confirmAction('reject')">
                                <i class="fa fa-times-circle me-2"></i> Tolak Permintaan
                            </button>
                            <a href="{{ route('admin.withdrawal.histories') }}" class="btn btn-secondary btn-lg">
                                <i class="fa fa-arrow-left me-2"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            @else
                <div class="alert alert-secondary text-center">
                    <i class="fa fa-info-circle me-1"></i> Permintaan penarikan ini sudah
                    <strong>{{ ucfirst($withdrawal->status) }}</strong>.
                    <p class="mt-2 mb-0"><a href="{{ route('admin.withdrawal.histories') }}"
                            class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-arrow-left me-1"></i> Kembali ke Riwayat
                        </a></p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>
<script>
    function confirmAction(actionType) {
        var isApprove = actionType === 'approve';
        var title = isApprove ? 'Konfirmasi Pembayaran?' : 'Tolak Permintaan?';
        var text = isApprove
            ? 'Anda akan menyetujui penarikan ini. Pastikan pembayaran sudah dilakukan.'
            : 'Anda akan menolak permintaan penarikan ini.';
        var confirmText = isApprove ? 'Ya, Konfirmasi!' : 'Ya, Tolak!';

        swal({
            title: title,
            text: text,
            icon: isApprove ? 'warning' : 'error',
            buttons: {
                cancel: {
                    text: 'Batal',
                    value: null,
                    visible: true,
                    className: 'btn btn-secondary',
                    closeModal: true
                },
                confirm: {
                    text: confirmText,
                    value: true,
                    visible: true,
                    className: isApprove ? 'btn btn-success' : 'btn btn-danger',
                    closeModal: true
                }
            }
        }).then(function(isConfirmed) {
            if (isConfirmed) {
                $('#action-type').val(actionType);

                if (actionType === 'reject') {
                    // Tampilkan prompt alasan penolakan
                    swal({
                        title: 'Masukkan Alasan Penolakan (Opsional)',
                        content: {
                            element: "input",
                            attributes: {
                                placeholder: "Contoh: Saldo tidak cukup, dll...",
                                type: "text",
                            },
                        },
                        buttons: {
                            cancel: {
                                text: 'Batal',
                                visible: true,
                                className: 'btn btn-secondary',
                                closeModal: true
                            },
                            confirm: {
                                text: 'Kirim Penolakan',
                                visible: true,
                                className: 'btn btn-danger',
                                closeModal: true
                            }
                        }
                    }).then(function(reason) {
                        if (reason !== null) {
                            $('#rejection_reason').val(reason);
                            $('#withdrawal-action-form').submit();
                        }
                    });

                } else {
                    // Langsung submit jika approve
                    $('#withdrawal-action-form').submit();
                }
            }
        });
    }
</script>

@endpush
