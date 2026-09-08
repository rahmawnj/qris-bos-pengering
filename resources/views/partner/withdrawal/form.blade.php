@props([
'items' => ['Partner', 'Keuangan', 'Tarik Uang'],
'title' => 'Penarikan Dana',
'subtitle' => 'Kelola dan tarik saldo usaha Laundry Anda dengan mudah dan aman.',
])

@extends('layouts.dashboard.app')

@push('styles')
	<style>
	    .withdrawal-page {
	        background: transparent;
	    }

    .withdrawal-card {
        border: 0;
        border-radius: 10px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, .08);
        overflow: hidden;
    }

    .balance-card {
        min-height: 150px;
        padding: 24px;
        border-radius: 10px;
        color: #fff;
        background: linear-gradient(135deg, #0d6efd 0%, #1687ff 55%, #2aa7ff 100%);
        box-shadow: 0 8px 18px rgba(13, 110, 253, .22);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .balance-card .label,
    .balance-card .hint {
        color: rgba(255, 255, 255, .9);
        font-size: .82rem;
    }

    .balance-card .value {
        margin: 10px 0 6px;
        font-size: 1.85rem;
        font-weight: 700;
        line-height: 1.1;
    }

    .balance-icon {
        width: 64px;
        height: 64px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        color: #0d6efd;
        background: rgba(255, 255, 255, .9);
        font-size: 1.65rem;
    }

    .section-card {
        padding: 22px;
        background: #fff;
    }

    .section-title {
        margin-bottom: 18px;
        color: #1f2937;
        font-size: .96rem;
        font-weight: 700;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .summary-tile {
        min-height: 112px;
        padding: 18px;
        border: 1px solid #e5eaf0;
        border-radius: 8px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .summary-tile .label {
        color: #475569;
        font-size: .78rem;
    }

    .summary-tile .value {
        margin: 10px 0 5px;
        color: #111827;
        font-size: 1.25rem;
        font-weight: 700;
    }

    .summary-tile .hint {
        color: #64748b;
        font-size: .72rem;
    }

    .summary-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 1rem;
    }

    .summary-icon.warning {
        color: #f59e0b;
        background: #fff3cd;
    }

    .summary-icon.success {
        color: #16a34a;
        background: #dcfce7;
    }

    .info-list {
        display: grid;
        gap: 11px;
        margin-bottom: 0;
        padding-left: 0;
        list-style: none;
    }

    .info-list li {
        display: flex;
        gap: 10px;
        color: #334155;
        font-size: .82rem;
        line-height: 1.45;
    }

    .info-list i {
        margin-top: 3px;
        color: #0d6efd;
        font-size: .72rem;
    }

    .withdrawal-panel .panel-heading {
        padding: 18px 22px;
        background: #fff;
        border-bottom: 1px solid #e9eef5;
    }

    .withdrawal-panel .panel-title {
        color: #1f2937;
        font-size: 1.05rem;
        font-weight: 700;
    }

    .withdrawal-panel .panel-subtitle {
        margin-top: 4px;
        color: #64748b;
        font-size: .78rem;
    }

    .bank-info-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        border: 1px solid #e5eaf0;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }

    .bank-info-item {
        min-height: 70px;
        padding: 14px 16px;
        display: grid;
        grid-template-columns: 22px minmax(0, 1fr);
        gap: 10px;
        align-items: center;
        border-right: 1px solid #e5eaf0;
    }

    .bank-info-item:last-child {
        border-right: 0;
    }

    .bank-info-item i {
        color: #0d6efd;
        text-align: center;
    }

    .bank-info-label {
        display: block;
        color: #475569;
        font-size: .68rem;
        font-weight: 700;
    }

    .bank-info-value {
        display: block;
        margin-top: 4px;
        color: #111827;
        font-size: .78rem;
        font-weight: 500;
        overflow-wrap: anywhere;
    }

    .bank-alert {
        margin-top: 16px;
        padding: 11px 14px;
        border: 1px solid #dbeafe;
        border-radius: 7px;
        color: #1d4ed8;
        background: #eff6ff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        font-size: .78rem;
    }

    .form-label-lg {
        font-size: .86rem;
        font-weight: 700;
        color: #334155;
    }

    .input-group-amount .input-group-text {
        color: #64748b;
        background: #f1f5f9;
        border-color: #d8e0ea;
        font-weight: 700;
    }

    .input-group-amount .form-control {
        border-color: #d8e0ea;
    }

    .withdrawal-result {
        padding: 17px;
        border: 1px solid #eef2f6;
        border-radius: 8px;
        background: #fbfdff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .fee-block,
    .receive-block {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .fee-icon,
    .receive-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .fee-icon {
        color: #8b5cf6;
        background: #f3e8ff;
    }

    .receive-icon {
        color: #16a34a;
        background: #dcfce7;
    }

    .withdrawal-result .label {
        color: #475569;
        font-size: .75rem;
        font-weight: 700;
    }

    .withdrawal-result .value,
    #total_disbursed_amount {
        color: #0d6efd;
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .char-count {
        color: #94a3b8;
        font-size: .72rem;
        text-align: right;
    }

    @media (max-width: 767.98px) {
        .bank-info-grid,
        .summary-grid {
            grid-template-columns: 1fr;
        }

        .bank-info-item {
            border-right: 0;
            border-bottom: 1px solid #e5eaf0;
        }

        .bank-info-item:last-child {
            border-bottom: 0;
        }

        .withdrawal-result,
        .bank-alert {
            align-items: stretch;
            flex-direction: column;
        }
    }
</style>

@endpush

@section('content')
<x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

@php
    $maxWithdrawalAmount = max($availableBalance - $withdrawalFee, 0);
    $defaultWithdrawalAmount = $maxWithdrawalAmount >= $minWithdrawalAmount ? $maxWithdrawalAmount : $minWithdrawalAmount;
@endphp

<div class="withdrawal-page">
    <div class="row g-4">
        <div class="col-xl-5">
            <div class="balance-card mb-4">
                <div>
                    <div class="label">Saldo Tersedia <i class="fas fa-info-circle ms-1"></i></div>
                    <div class="value">Rp {{ number_format($availableBalance, 0, ',', '.') }}</div>
                    <div class="hint">Saldo ini dapat Anda tarik kapan saja.</div>
                </div>
                <div class="balance-icon">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>

            <div class="withdrawal-card section-card mb-4">
                <h5 class="section-title">Ringkasan Penarikan</h5>
                <div class="summary-grid">
                    <div class="summary-tile">
                        <div>
                            <div class="label">Pending</div>
                            <div class="value">Rp {{ number_format($totalWithdrawnPending, 0, ',', '.') }}</div>
                            <div class="hint">Menunggu diproses</div>
                        </div>
                        <span class="summary-icon warning"><i class="fas fa-hourglass-half"></i></span>
                    </div>
                    <div class="summary-tile">
                        <div>
                            <div class="label">Sudah Ditarik</div>
                            <div class="value">Rp {{ number_format($totalWithdrawnApproved, 0, ',', '.') }}</div>
                            <div class="hint">Berhasil ditarik</div>
                        </div>
                        <span class="summary-icon success"><i class="fas fa-check-circle"></i></span>
                    </div>
                </div>
            </div>

            <div class="withdrawal-card section-card">
                <h5 class="section-title"><i class="fas fa-info-circle text-primary me-2"></i>Informasi Penting</h5>
                <ul class="info-list">
                    <li><i class="fas fa-check"></i><span>Minimal penarikan adalah <strong>Rp {{ number_format($minWithdrawalAmount, 0, ',', '.') }}</strong>.</span></li>
                    <li><i class="fas fa-check"></i><span>Biaya penarikan adalah <strong>Rp {{ number_format($withdrawalFee, 0, ',', '.') }}</strong> per transaksi.</span></li>
                    <li><i class="fas fa-check"></i><span>{{ $processingTimeInfo }}</span></li>
                    <li><i class="fas fa-check"></i><span>Penarikan hanya dapat dilakukan ke rekening atas nama pemilik outlet.</span></li>
                </ul>
                @if ($hasPendingWithdrawal)
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="fa fa-exclamation-triangle me-2"></i> Anda memiliki permintaan penarikan yang sedang diproses.
                    </div>
                @endif
            </div>
        </div>

        <div class="col-xl-7">
            <div class="panel withdrawal-panel mb-4">
                <div class="panel-heading">
                    <h4 class="panel-title">Ajukan Penarikan Dana</h4>
                    <div class="panel-subtitle">Isi informasi di bawah untuk mengajukan penarikan saldo.</div>
                </div>
                <div class="panel-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('partner.withdrawal.store') }}" method="POST" id="withdrawalForm">
                        @csrf

                        <h5 class="section-title text-primary"><i class="fas fa-university me-2"></i>Informasi Rekening Bank</h5>
                        <div class="bank-info-grid mb-3">
                            <div class="bank-info-item">
                                <i class="fas fa-university"></i>
                                <div>
                                    <span class="bank-info-label">Nama Bank</span>
                                    <span id="bank_name_display" class="bank-info-value">{{ getBrand()->bank_name ?? 'Belum diatur' }}</span>
                                </div>
                            </div>
                            <div class="bank-info-item">
                                <i class="fas fa-credit-card"></i>
                                <div>
                                    <span class="bank-info-label">Nomor Rekening</span>
                                    <span id="bank_account_number_display" class="bank-info-value">{{ getBrand()->bank_account_number ?? 'Belum diatur' }}</span>
                                </div>
                            </div>
                            <div class="bank-info-item">
                                <i class="fas fa-user"></i>
                                <div>
                                    <span class="bank-info-label">Nama Pemilik Rekening</span>
                                    <span id="bank_account_holder_name_display" class="bank-info-value">{{ getBrand()->bank_account_holder_name ?? 'Belum diatur' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bank-alert mb-4">
                            <span><i class="fas fa-info-circle me-2"></i>Informasi bank ini diambil dari pengaturan profil Anda.</span>
                            <a href="{{ route('partner.brand.profile.edit', ['page' => 'bank']) }}" class="fw-semibold">Edit Profil Brand <i class="fas fa-external-link-alt ms-1"></i></a>
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label form-label-lg">Jumlah Penarikan <span class="text-danger">*</span></label>
                            <div class="input-group input-group-amount">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" min="{{ $minWithdrawalAmount }}"
                                    max="{{ $maxWithdrawalAmount }}" id="amount" name="amount"
                                    placeholder="Masukkan jumlah penarikan"
                                    value="{{ old('amount', $defaultWithdrawalAmount) }}"
                                    required>
                            </div>
                            <div class="form-text text-muted">
                                Maksimal penarikan: Rp {{ number_format($maxWithdrawalAmount, 0, ',', '.') }}
                            </div>
                            <div id="amount-feedback" class="invalid-feedback d-block"></div>
                        </div>

                        <div class="withdrawal-result mb-4">
                            <div class="fee-block">
                                <span class="fee-icon"><i class="fas fa-gem"></i></span>
                                <div>
                                    <div class="label">Biaya Admin</div>
                                    <div class="value text-dark">Rp {{ number_format($withdrawalFee, 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <div class="receive-block">
                                <div>
                                    <div class="label">Total yang Akan Dipotong</div>
                                    <div id="total_disbursed_amount">Rp 0</div>
                                </div>
                                <span class="receive-icon"><i class="fas fa-wallet"></i></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label form-label-lg">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
                            <textarea class="form-control" id="notes" name="notes" maxlength="200" placeholder="Tulis catatan atau keperluan penarikan Anda..."
                                rows="4">{{ old('notes') }}</textarea>
                            <div class="char-count"><span id="notes-count">0</span> / 200</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" id="submitWithdrawalBtn"
                            {{ $hasPendingWithdrawal || $maxWithdrawalAmount < $minWithdrawalAmount ? 'disabled' : '' }}>
                            <i class="fas fa-paper-plane me-2"></i> Ajukan Penarikan
                        </button>
                        @if ($hasPendingWithdrawal)
                            <small class="text-warning mt-2 d-block">
                                Anda memiliki permintaan penarikan yang sedang <strong>pending</strong>. Tidak dapat mengajukan penarikan baru.
                            </small>
                        @elseif ($maxWithdrawalAmount < $minWithdrawalAmount)
                            <small class="text-danger mt-2 d-block">
                                Saldo tersedia Anda belum cukup untuk minimal penarikan dan biaya admin.
                            </small>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/jquery.maskedinput/src/jquery.maskedinput.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>
<script>
$(document).ready(function() {
const amountInput = $('#amount');
const totalDisbursedAmountDisplay = $('#total_disbursed_amount'); // Mengubah nama variabel untuk mencerminkan elemen div
const submitBtn = $('#submitWithdrawalBtn');
const amountFeedback = $('#amount-feedback');

        // Variabel yang disediakan dari sisi server (pastikan ini diteruskan dari controller Anda)
        const availableBalance = {{ $availableBalance }};
        const minWithdrawal = {{ $minWithdrawalAmount }};
        const hasPending = {{ $hasPendingWithdrawal ? 'true' : 'false' }};
        const withdrawalFee = {{ (int) $withdrawalFee }};

        // Ambil nilai dari elemen div yang menampilkan teks bank
        // Penting: Menggunakan .text() atau .html() untuk mendapatkan konten teks
        const bankName = $('#bank_name_display').text().trim();
        const bankAccountNumber = $('#bank_account_number_display').text().trim();
        const bankAccountHolderName = $('#bank_account_holder_name_display').text().trim();

        const bankDetailsMissing = (bankName === 'Belum diatur' || bankAccountNumber === 'Belum diatur' || bankAccountHolderName === 'Belum diatur');

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        // Fungsi untuk menghitung dan menampilkan total jumlah yang akan dipotong dari saldo
        function updateTotalDisbursedAmount() {
            let currentAmount = parseFloat(amountInput.val());
            if (isNaN(currentAmount) || currentAmount <= 0) {
                totalDisbursedAmountDisplay.html(`<span class="currency-prefix">Rp</span>${formatRupiah(0)}`);
                return;
            }
            const total = currentAmount + withdrawalFee;
            totalDisbursedAmountDisplay.html(`<span class="currency-prefix">Rp</span>${formatRupiah(total)}`);
        }

        // Fungsi untuk memperbarui status disabled tombol submit dan menampilkan umpan balik
        function updateSubmitButtonStatus() {
            let currentAmount = parseFloat(amountInput.val());
            let totalAmountWithFee = currentAmount + withdrawalFee;

            amountInput.removeClass('is-invalid');
            amountFeedback.text('');
            submitBtn.prop('disabled', false);

            if (bankDetailsMissing) {
                submitBtn.prop('disabled', true);
                amountFeedback.html('<i class="fas fa-exclamation-circle me-1"></i> Harap lengkapi <a href="{{ route('partner.brand.profile.edit', ['page' => 'bank']) }}">informasi bank</a> Anda terlebih dahulu.');
                amountInput.addClass('is-invalid');
                updateTotalDisbursedAmount();
                return;
            }

            if (hasPending) {
                submitBtn.prop('disabled', true);
                updateTotalDisbursedAmount();
                return;
            }

            if (isNaN(currentAmount) || currentAmount <= 0) {
                amountInput.addClass('is-invalid');
                amountFeedback.text('Jumlah penarikan harus angka positif.');
                submitBtn.prop('disabled', true);
            } else if (currentAmount < minWithdrawal) {
                amountInput.addClass('is-invalid');
                amountFeedback.text(
                    `Jumlah penarikan minimal adalah Rp ${formatRupiah(minWithdrawal)}.`
                );
                submitBtn.prop('disabled', true);
            } else if (totalAmountWithFee > availableBalance) {
                amountInput.addClass('is-invalid');
                amountFeedback.text(
                    `Jumlah penarikan (termasuk biaya Rp ${formatRupiah(withdrawalFee)}) melebihi saldo tersedia (Rp ${formatRupiah(availableBalance)}). Total yang dibutuhkan: Rp ${formatRupiah(totalAmountWithFee)}.`
                );
                submitBtn.prop('disabled', true);
            }

            updateTotalDisbursedAmount();
        }

        // Pemeriksaan dan pembaruan awal saat halaman dimuat
        updateTotalDisbursedAmount();
        updateSubmitButtonStatus();

        // Dengarkan perubahan input pada bidang jumlah
        amountInput.on('input', function() {
            updateSubmitButtonStatus();
        });

        // Tangani pengajuan formulir dengan konfirmasi SweetAlert
        $('#withdrawalForm').on('submit', function(e) {
            updateSubmitButtonStatus();

            if (submitBtn.prop('disabled')) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: amountFeedback.text() || 'Terjadi kesalahan validasi. Silakan periksa kembali formulir Anda.',
                });
            } else {
                const requestedAmount = parseFloat(amountInput.val());
                const totalDeducted = requestedAmount + withdrawalFee;

                Swal.fire({
                    title: 'Konfirmasi Penarikan',
                    html: `Anda akan menarik dana sebesar <strong>Rp ${formatRupiah(requestedAmount)}</strong>.<br>` +
                          `Biaya penarikan: <strong>Rp ${formatRiah(withdrawalFee)}</strong>.<br>` +
                          `Total yang akan dipotong dari saldo Anda: <strong>Rp ${formatRupiah(totalDeducted)}</strong>.<br><br>` +
                          `Lanjutkan?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Ajukan',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) {
                        e.currentTarget.submit();
                    } else {
                        e.preventDefault();
                    }
                });
            }
        });
    });
</script>

@endpush
