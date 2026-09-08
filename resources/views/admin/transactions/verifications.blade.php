@extends('layouts.dashboard.app')

@push('styles')
<style>
    .verification-panel {
        background: #fff;
        border: 1px solid rgba(32, 37, 42, .12);
        border-radius: 6px;
        overflow: hidden;
    }

    .verification-tabs {
        background: #20252a;
        border-bottom: 0;
        padding: .5rem .5rem 0;
    }

    .verification-tabs .nav-link {
        border: 0;
        border-radius: 5px 5px 0 0;
        color: rgba(255, 255, 255, .72);
        font-weight: 600;
        padding: .75rem 1.25rem;
    }

    .verification-tabs .nav-link:hover {
        color: #fff;
    }

    .verification-tabs .nav-link.active {
        background: #fff;
        color: #20252a;
    }

    .verification-table {
        margin-bottom: 0;
    }

    .verification-table thead th {
        background: #f8f9fa;
        border-bottom: 1px solid #d7dce0;
        color: #20252a;
        font-weight: 700;
        vertical-align: middle;
    }

    .verification-table tbody td {
        vertical-align: middle;
    }

    .proof-thumb {
        width: 92px;
        height: 68px;
        object-fit: cover;
        border: 1px solid #e1e4e8;
        border-radius: 5px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
    }

    .empty-proof {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        color: #6c757d;
        font-size: .8125rem;
        white-space: nowrap;
    }

    .verification-actions {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
        min-width: 112px;
    }

    .verification-actions .btn {
        white-space: nowrap;
    }

    .bypass-select {
        min-width: 116px;
    }

    .verification-panel .modal,
    .verification-panel .modal-content {
        white-space: normal;
    }

    .verification-panel .modal-body {
        overflow-wrap: anywhere;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-start align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Verifikasi & Pending QRIS</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="verification-panel">
        <ul class="nav nav-tabs verification-tabs">
            <li class="nav-item">
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'with_proof']) }}" class="nav-link {{ $tab === 'with_proof' ? 'active' : '' }}">
                    <span class="d-sm-none">Punya Bukti</span>
                    <span class="d-sm-block d-none"><i class="fa fa-check-circle fa-fw me-1"></i> Punya Bukti Pembayaran</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'without_proof']) }}" class="nav-link {{ $tab === 'without_proof' ? 'active' : '' }}">
                    <span class="d-sm-none">Tanpa Bukti</span>
                    <span class="d-sm-block d-none"><i class="fa fa-clock fa-fw me-1"></i> Pending Tanpa Bukti</span>
                </a>
            </li>
        </ul>

        <div class="tab-content p-3">
            <div class="tab-pane fade active show">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap verification-table">
                    <thead>
                        <tr>
                            <th width="50">No.</th>
                            <th>Waktu</th>
                            <th>Order ID</th>
                            <th>Owner & Outlet</th>
                            <th>Provider</th>
                            <th>Nominal</th>
                            <th class="text-center">Bukti Pembayaran</th>
                            <th class="text-center">Status Bypass</th>
                            <th class="text-center">Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $trx)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $trx->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <strong>{{ $trx->order_id }}</strong>
                                <br>
                                <a href="{{ route($isAdminContext ? 'admin.transactions.show' : 'partner.transactions.show', $trx->id) }}" class="small text-primary">Lihat Detail</a>
                            </td>
                            <td>
                                <strong>{{ $trx->owner->brand_name ?? '-' }}</strong><br>
                                <small class="text-muted">{{ $trx->outlet->outlet_name ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-dark">{{ $trx->qris_provider_label }}</span>
                            </td>
                            <td>Rp {{ number_format($trx->amount, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if($trx->qrisTransaction && $trx->qrisTransaction->proof_of_payment)
                                    <a href="{{ asset('storage/' . $trx->qrisTransaction->proof_of_payment) }}" target="_blank" title="Lihat bukti pembayaran">
                                        <img src="{{ asset('storage/' . $trx->qrisTransaction->proof_of_payment) }}" alt="Bukti pembayaran {{ $trx->order_id }}" class="proof-thumb">
                                    </a>
                                @else
                                    <span class="empty-proof">
                                        <i class="fa fa-clock"></i> Belum ada bukti
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($trx->qrisTransaction && $trx->qrisTransaction->bypass_status === 'inactive' && $trx->qrisTransaction->proof_of_payment)
                                    <form action="{{ route($isAdminContext ? 'admin.transactions.update_bypass_status' : 'partner.transactions.update_bypass_status', $trx->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <select name="bypass_status" class="form-select form-select-sm d-inline-block bg-warning text-dark fw-bold bypass-select" 
                                            onchange="
                                                let select = this;
                                                if(select.value === 'active') {
                                                    swal({
                                                        title: 'Konfirmasi Bypass',
                                                        text: 'Aktifkan bypass mesin untuk transaksi ini?',
                                                        icon: 'warning',
                                                        buttons: {
                                                            cancel: {
                                                                text: 'Batal',
                                                                visible: true,
                                                                className: 'btn btn-secondary',
                                                                closeModal: true
                                                            },
                                                            confirm: {
                                                                text: 'Ya, Aktifkan!',
                                                                visible: true,
                                                                className: 'btn btn-warning text-dark',
                                                                closeModal: true
                                                            }
                                                        }
                                                    }).then((isConfirmed) => {
                                                        if (isConfirmed) {
                                                            select.form.submit();
                                                        } else {
                                                            select.value = 'inactive';
                                                        }
                                                    });
                                                }
                                            ">
                                            <option value="inactive" selected>Nonaktif</option>
                                            <option value="active">Aktif</option>
                                        </select>
                                    </form>
                                @elseif($trx->qrisTransaction && $trx->qrisTransaction->bypass_status && $trx->qrisTransaction->proof_of_payment)
                                    <span class="badge {{ $trx->qrisTransaction->bypass_status == 'active' ? 'bg-success' : 'bg-primary' }} fs-6 px-3 py-2">
                                        {{ $trx->qrisTransaction->bypass_status == 'active' ? 'Aktif' : 'Selesai' }}
                                    </span>
                                @else
                                    <span class="text-muted small">Menunggu Bukti</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="verification-actions">
                                    <a href="{{ route($isAdminContext ? 'admin.transactions.show' : 'partner.transactions.show', $trx->id) }}"
                                        class="btn btn-sm btn-info text-white"
                                        title="Lihat Detail Transaksi">
                                        <i class="fa fa-eye"></i>
                                    </a>

                                @if($isAdminContext)
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-primary btn-check-payment-gateway text-white"
                                        title="Cek Status {{ $trx->qris_provider_label }}"
                                        data-id="{{ $trx->id }}"
                                        data-provider="{{ strtolower($trx->qris_provider_label) }}"
                                        data-check-url="{{ route('admin.transactions.check_payment_gateway', $trx->id) }}">
                                        <i class="fa fa-search-dollar"></i>
                                    </button>
                                @endif

                                @if(!$trx->qrisTransaction || !$trx->qrisTransaction->proof_of_payment)
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-warning text-dark"
                                        title="Upload Bukti Pembayaran"
                                        data-bs-toggle="modal"
                                        data-bs-target="#proofUploadModal-{{ $trx->id }}">
                                        <i class="fa fa-upload"></i>
                                    </button>
                                @endif
                                </div>
                                
                                @if(!$trx->qrisTransaction || !$trx->qrisTransaction->proof_of_payment)
                                    <div class="modal fade text-start" id="proofUploadModal-{{ $trx->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form action="{{ route($isAdminContext ? 'admin.transactions.bypass' : 'partner.transactions.bypass', $trx->id) }}" method="POST" enctype="multipart/form-data" class="modal-content">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Upload Bukti Pembayaran</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Order ID</label>
                                                        <input type="text" class="form-control" value="{{ $trx->order_id }}" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="proof_of_payment_{{ $trx->id }}" class="form-label fw-bold">Bukti Pembayaran</label>
                                                        <input class="form-control" type="file" id="proof_of_payment_{{ $trx->id }}" name="proof_of_payment" accept="image/*" required>
                                                    </div>
                                                    <p class="text-muted mb-0">Bukti ini akan masuk ke daftar verifikasi manual dan bisa diaktifkan sebagai bypass bila pembayaran gateway tidak terbaca.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-warning text-dark fw-bold">
                                                        <i class="fa fa-upload me-1"></i> Upload
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fa fa-check-circle fa-3x mb-3 text-success"></i>
                                <h5>Belum ada transaksi pending</h5>
                                <p>Tidak ada data transaksi yang ditemukan untuk tab ini.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                    <div class="text-muted small">
                        Menampilkan {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }}
                        dari {{ $transactions->total() }} data
                    </div>
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>
<script>
$(document).ready(function() {
    $('.btn-check-payment-gateway').click(function() {
        let btn = $(this);
        let id = btn.data('id');
        let provider = String(btn.data('provider') || 'xendit').toUpperCase();
        let checkUrl = btn.data('check-url');
        let originalText = btn.html();
        
        btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
        
        $.ajax({
            url: checkUrl,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                btn.html(originalText).prop('disabled', false);
                if (res.status === 'success') {
                    swal({
                        title: "Pembayaran Ditemukan di " + provider + "!",
                        text: res.message,
                        icon: "success",
                    }).then(() => window.location.reload());
                } else if (res.status === 'not_found') {
                    swal("Belum Ada!", res.message, "warning");
                } else {
                    swal("Info", res.message, "info");
                }
            },
            error: function(err) {
                btn.html(originalText).prop('disabled', false);
                swal("Gagal!", "Terjadi kesalahan jaringan atau server.", "error");
            }
        });
    });

});
</script>
@endpush
