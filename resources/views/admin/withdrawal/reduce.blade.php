@props([
    'items' => ['Admin', 'Withdrawal Management', 'Kurangi Saldo Owner'],
    'title' => 'Pengurangan Saldo Owner',
    'subtitle' => 'Lakukan penarikan dana paksa/pengurangan saldo dari Owner/Partner.',
])

{{-- current_balance diisi dari kolom owners.balance --}}

@extends('layouts.dashboard.app')

@push('styles')
<style>
    .reduce-page {
        padding-bottom: 24px;
    }

    .reduce-page .section-heading {
        margin: 0 0 12px;
        color: #1f2937;
        font-size: .95rem;
        font-weight: 700;
    }

    .reduce-panel {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, .08);
        overflow: hidden;
    }

    .reduce-panel .panel-body {
        padding: 22px;
    }

    .reduce-page .form-label {
        color: #1f2937;
        font-size: .82rem;
        font-weight: 700;
    }

    .reduce-page .form-control,
    .reduce-page .form-select,
    .reduce-page .select2-container .select2-selection--single {
        border-color: #d8dee6;
        border-radius: 7px;
    }

    .reduce-page textarea.form-control {
        min-height: 96px;
        resize: vertical;
    }

    .input-group-amount .input-group-text {
        min-width: 58px;
        justify-content: center;
        background: #f4f6f8;
        border-color: #d8dee6;
        color: #374151;
        font-size: 1.05rem;
        font-weight: 700;
    }

    .input-group-amount .form-control-lg {
        font-size: 1.05rem;
        font-weight: 600;
    }

    .summary-box,
    .info-card {
        position: relative;
        margin-bottom: 18px;
        padding: 20px 22px;
        border: 1px solid #e5eaf0;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 4px 166px rgba(15, 23, 42, .08);
        overflow: hidden;
    }

    .summary-box {
        min-height: 126px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        transition: box-shadow .2s ease;
    }

    .summary-box:before,
    .info-card:before {
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        content: "";
        background: #6c757d;
    }

    .summary-box.balance-display:before {
        background: #00ac69;
    }

    .info-card.warning-detail:before {
        background: #f59f00;
    }

    .info-card.owner-detail:before {
        background: #dc3545;
    }

    .summary-box .label,
    .info-card .label {
        margin-bottom: 8px;
        color: #64748b;
        font-size: .82rem;
        font-weight: 600;
    }

    .summary-box .value {
        color: #111827;
        font-size: 1.55rem;
        font-weight: 700;
        line-height: 1.15;
    }

    .currency-prefix {
        margin-right: 4px;
        color: #64748b;
        font-size: 1rem;
        font-weight: 500;
    }

    .summary-icon {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #dcfce7;
        color: #00ac69;
        font-size: 1.35rem;
        flex: 0 0 auto;
    }

    .info-card h6 {
        margin-bottom: 12px;
        color: #1f2937;
        font-size: .88rem;
        font-weight: 700;
    }

    .info-card p {
        margin-bottom: 8px;
        color: #374151;
        font-size: .9rem;
    }

    .info-card p:last-child {
        margin-bottom: 0;
    }

    .reduce-submit-help {
        min-height: 18px;
        font-size: .78rem;
    }

    .highlight-balance {
        animation: pulse 1s ease-in-out;
    }

    @keyframes pulse {
        0% { box-shadow: 0 4px 16px rgba(15, 23, 42, .08), 0 0 0 0 rgba(0, 172, 105, .28); }
        70% { box-shadow: 0 4px 16px rgba(15, 23, 42, .08), 0 0 0 10px rgba(0, 172, 105, 0); }
        100% { box-shadow: 0 4px 16px rgba(15, 23, 42, .08), 0 0 0 0 rgba(0, 172, 105, 0); }
    }

    @media (max-width: 991.98px) {
        .reduce-panel .panel-body,
        .summary-box,
        .info-card {
            padding: 18px;
        }
    }
</style>
@endpush

@section('content')
<x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

<div class="reduce-page">
<div class="row g-4">
    {{-- Kolom Kiri: Formulir Pengurangan Saldo --}}
    <div class="col-lg-6">
        <h5 class="section-heading">Formulir Pengurangan Saldo</h5>
        <div class="panel panel-inverse reduce-panel mb-4">
            <div class="panel-heading">
                <h4 class="panel-title">Lakukan Pengurangan Saldo</h4>
            </div>
            <div class="panel-body">

                {{-- Alert Section --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                {{-- End Alert Section --}}

                <form action="{{ route('admin.withdrawal.reduce.store') }}" method="POST" id="reduceForm">
                    @csrf

                    {{-- 1. Pemilihan Owner --}}
                    <div class="mb-4">
                        <label for="owner_id" class="form-label">Pilih Owner/Partner <span class="text-danger">*</span></label>
                        <select name="owner_id" id="owner_id" class="form-control select2 @error('owner_id') is-invalid @enderror" required>
                            <option value="" data-balance="0">-- Pilih Owner --</option>
                            @foreach ($owners as $owner)
                                <option
                                    value="{{ $owner->id }}"
                                    {{ old('owner_id') == $owner->id ? 'selected' : '' }}
                                    data-balance="{{ $owner->current_balance }}"
                                >
                                    {{ $owner->brand_name ?? 'N/A' }} ({{ $owner->user->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                        @error('owner_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 2. Jumlah Pengurangan (Total Kotor) --}}
                    <div class="mb-3">
                        <label for="amount" class="form-label">Total Pengurangan (Termasuk Pajak <strong>Rp 15.000</strong>) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-amount">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control form-control-lg @error('amount') is-invalid @enderror"
                                id="amount" name="amount" min="15000"
                                placeholder="Minimal Rp 15.000"
                                value="{{ old('amount') }}" required>
                        </div>
                        <div id="amount-feedback" class="invalid-feedback d-block">
                            @error('amount') {{ $message }} @enderror
                        </div>
                        <small class="form-text text-muted">
                            Jumlah yang dimasukkan adalah <strong>Total Potongan Saldo</strong> (Potongan Bersih + Biaya Admin).
                        </small>
                    </div>

                    {{-- 3. Catatan Admin --}}
                    <div class="mb-4">
                        <label for="note" class="form-label">Catatan Admin <span class="text-danger">*</span></label>
                        <textarea name="note" id="note" rows="3" class="form-control @error('note') is-invalid @enderror" required placeholder="Contoh: Pengurangan karena biaya perbaikan device X.">{{ old('note') }}</textarea>
                        @error('note')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-danger btn-lg w-100" id="submitReduceBtn" disabled>
                        <i class="fas fa-minus-circle me-2"></i> Potong Saldo Sekarang
                    </button>
                    <small class="form-text text-danger mt-2 d-block text-center reduce-submit-help" id="global_error_message">
                        Harap pilih Owner dan masukkan jumlah pengurangan.
                    </small>
                </form>
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Informasi Saldo Owner yang Dipilih & Konfirmasi --}}
    <div class="col-lg-6">
        <h5 class="section-heading">Informasi Saldo Owner</h5>

        {{-- Saldo Tersedia --}}
        <div class="summary-box balance-display">
            <div>
                <div class="label">Saldo Tersedia untuk Dikurangi</div>
                <div class="value" id="current_balance_display">
                    <span class="currency-prefix">Rp</span>0
                </div>
            </div>
            <span class="summary-icon"><i class="fa fa-wallet"></i></span>
        </div>

        {{-- Detail Potongan --}}
        <h5 class="section-heading mt-4">Rincian Simulasi Potongan</h5>
        <div class="info-card warning-detail">
            <h6><i class="fas fa-money-check-alt me-2"></i> Detail Transaksi</h6>
            <p>Biaya Admin (Pajak): <strong><span class="text-danger">Rp 15.000</span></strong></p>
            <p>Potongan Bersih (yang dicatat di `requested_amount`): <strong id="net_reduced_display" class="text-success">Rp 0</strong></p>
            <p class="mt-2">Total Potongan Saldo (Jumlah Input Anda): <strong id="gross_reduced_display" class="text-danger">Rp 0</strong></p>
        </div>

        {{-- Owner yang Dipilih --}}
        <h5 class="section-heading mt-4">Detail Owner</h5>
        <div class="info-card owner-detail">
            <h6><i class="fas fa-user-circle me-2"></i> Owner Terpilih</h6>
            <p id="owner_name_display">Pilih Owner dari formulir di samping.</p>
            <p id="owner_brand_display" class="mt-1 text-muted"></p>
        </div>
    </div>
</div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Konstanta Biaya Admin
        const ADMIN_FEE = 15000;
        const MIN_AMOUNT = ADMIN_FEE;

        // Pilihan Element
        const ownerSelect = document.getElementById('owner_id');
        const amountInput = document.getElementById('amount');
        const balanceDisplay = document.getElementById('current_balance_display');
        const netReducedDisplay = document.getElementById('net_reduced_display');
        const grossReducedDisplay = document.getElementById('gross_reduced_display');
        const ownerNameDisplay = document.getElementById('owner_name_display');
        const ownerBrandDisplay = document.getElementById('owner_brand_display');
        const submitButton = document.getElementById('submitReduceBtn');
        const amountFeedback = document.getElementById('amount-feedback');
        const globalErrorMessage = document.getElementById('global_error_message');

        // Fungsi Format Rupiah
        function formatRupiah(number) {
            if (number === undefined || number === null || isNaN(number)) return '0';
            const integerNumber = parseInt(number);
            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(integerNumber);
        }

        // Fungsi utama untuk memperbarui tampilan saldo, simulasi, dan status tombol
        function updateUIAndStatus() {
            const selectedOption = ownerSelect.options[ownerSelect.selectedIndex];
            const ownerId = selectedOption.value;
            const balance = selectedOption.getAttribute('data-balance');
            const ownerName = selectedOption.text.match(/\(([^)]+)\)/) ? selectedOption.text.match(/\(([^)]+)\)/)[1] : '-';
            const ownerBrand = selectedOption.text.split('(')[0].trim() || '-';

            let totalAmountToDeduct = parseFloat(amountInput.value) || 0;
            // Hitung Potongan Bersih (Total Potongan - Biaya Admin)
            let netAmountReduced = totalAmountToDeduct - ADMIN_FEE;
            let ownerBalance = parseInt(balance) || 0;
            let isValid = true;
            let errorMessage = '';

            // Update Tampilan Saldo & Detail Owner
            balanceDisplay.innerHTML = `<span class="currency-prefix">Rp</span>${formatRupiah(ownerBalance)}`;
            ownerNameDisplay.innerHTML = `<i class="fas fa-user-circle me-2"></i> ${ownerName}`;
            ownerBrandDisplay.innerHTML = `Brand: <strong>${ownerBrand}</strong>`;

            // Update Detail Potongan
            grossReducedDisplay.innerHTML = `Rp ${formatRupiah(totalAmountToDeduct)}`;
            netReducedDisplay.innerHTML = `Rp ${formatRupiah(netAmountReduced)}`;

            // Hapus kelas error/feedback lama
            amountInput.classList.remove('is-invalid');
            amountFeedback.innerHTML = '';

            // --- Validasi ---
            if (!ownerId) {
                isValid = false;
                errorMessage = 'Harap pilih Owner terlebih dahulu.';
            } else if (isNaN(totalAmountToDeduct) || totalAmountToDeduct < MIN_AMOUNT) {
                isValid = false;
                errorMessage = `Jumlah potongan minimal adalah Rp ${formatRupiah(MIN_AMOUNT)} (biaya admin).`;
                amountInput.classList.add('is-invalid');
                amountFeedback.innerHTML = `Jumlah minimal harus Rp ${formatRupiah(MIN_AMOUNT)}.`;
            } else if (totalAmountToDeduct > ownerBalance) {
                isValid = false;
                errorMessage = `Potongan Kotor (${formatRupiah(totalAmountToDeduct)}) melebihi saldo tersedia (${formatRupiah(ownerBalance)}).`;
                amountInput.classList.add('is-invalid');
                amountFeedback.innerHTML = `Total potongan melebihi saldo tersedia (Rp ${formatRupiah(ownerBalance)}).`;
            }

            // Update Tombol Submit
            submitButton.disabled = !isValid;

            // Update Pesan Error Global
            if (isValid) {
                globalErrorMessage.innerHTML = 'Klik tombol di bawah untuk memproses pengurangan.';
                globalErrorMessage.classList.remove('text-danger');
                globalErrorMessage.classList.add('text-success');
            } else {
                globalErrorMessage.innerHTML = errorMessage || 'Terjadi kesalahan validasi. Periksa formulir.';
                globalErrorMessage.classList.remove('text-success');
                globalErrorMessage.classList.add('text-danger');
            }

            // Tambahkan efek highlight pada balance display saat ganti owner
            if(ownerId) {
                const summaryBox = balanceDisplay.closest('.summary-box');
                summaryBox.classList.add('highlight-balance');
                setTimeout(() => {
                    summaryBox.classList.remove('highlight-balance');
                }, 1000);
            }
        }

        // Event Listeners
        ownerSelect.addEventListener('change', updateUIAndStatus);
        amountInput.addEventListener('input', updateUIAndStatus);

        // Inisialisasi awal
        updateUIAndStatus();

        // Tangani konfirmasi formulir menggunakan SweetAlert2
        $('#reduceForm').on('submit', function(e) {
            updateUIAndStatus();
            if (submitButton.disabled) {
                e.preventDefault();
            } else {
                 e.preventDefault();
                 const totalAmountToDeduct = parseFloat(amountInput.value);
                 const netAmountReduced = totalAmountToDeduct - ADMIN_FEE;
                 const ownerName = ownerSelect.options[ownerSelect.selectedIndex].text.split('(')[0].trim();

                 Swal.fire({
                     title: 'Konfirmasi Potong Saldo',
                     html: `Anda akan <strong>MEMOTONG</strong> saldo Owner <strong>${ownerName}</strong> dengan rincian:<br><br>` +
                           `<strong>Total Potongan Saldo:</strong> <strong>Rp ${formatRupiah(totalAmountToDeduct)}</strong><br>` +
                           `<strong>Potongan Bersih:</strong> <strong>Rp ${formatRupiah(netAmountReduced)}</strong><br>` +
                           `<strong>Biaya Admin:</strong> <strong>Rp ${formatRupiah(ADMIN_FEE)}</strong><br><br>` +
                           `Aksi ini tidak dapat dibatalkan. Lanjutkan?`,
                     icon: 'warning',
                     showCancelButton: true,
                     confirmButtonText: 'Ya, Potong Saldo',
                     cancelButtonText: 'Batal',
                     confirmButtonColor: '#dc3545',
                 }).then((result) => {
                     if (result.isConfirmed) {
                         // Lanjutkan pengiriman form setelah konfirmasi
                         e.currentTarget.submit();
                     }
                 });
            }
        });
    });
</script>
@endpush
