<?php

namespace App\Http\Controllers\Partner;

use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class WithdrawalController extends Controller
{
    public function withdrawal_request()
    {
        $feature = getData();
        if (!$feature->can('withdrawal.request')) {
            abort(403, 'Anda tidak memiliki izin.');
        }

        $owner = getBrand();

        // Total yang sudah ditarik dan disetujui
        $totalWithdrawnApproved = Withdrawal::where('owner_id', $owner->id)
            ->where('status', 'approved')
            ->sum('amount');

        // Total penarikan yang masih pending
        $totalWithdrawnPending = Withdrawal::where('owner_id', $owner->id)
            ->where('status', 'pending')
            ->sum('amount');

        // Ambil saldo langsung dari kolom balance owner agar tidak menghitung ulang dari transaksi.
        $availableBalance = max((int) ($owner->balance ?? 0), 0);

        // Cek apakah ada penarikan pending
        $hasPendingWithdrawal = Withdrawal::where('owner_id', $owner->id)
            ->where('status', 'pending')
            ->exists();

        // Beberapa informasi tambahan yang mungkin berguna
        $minWithdrawalAmount = 10000; // Contoh minimal penarikan
        $withdrawalFee = (bool) ($owner->withdrawal_fee_charged ?? true)
            ? (int) config('app.withdrawal_fee', 15000)
            : 0;
        $processingTimeInfo = 'Biasanya diproses dalam 1-2 hari kerja.'; // Contoh informasi waktu proses
        $withdrawalFeeInfo = $withdrawalFee > 0
            ? 'Biaya penarikan berlaku sesuai pengaturan owner.'
            : 'Tidak ada biaya penarikan.';

        return view('partner.withdrawal.form', compact(
            'availableBalance', // Ini akan menjadi 'maxWithdrawal' yang lebih informatif
            'hasPendingWithdrawal',
            'minWithdrawalAmount',
            'withdrawalFee',
            'processingTimeInfo',
            'withdrawalFeeInfo',
            'totalWithdrawnPending', // Untuk ditampilkan sebagai informasi tambahan
            'totalWithdrawnApproved' // Untuk ditampilkan sebagai informasi tambahan
        ));
    }

       public function withdrawal_store(Request $request)
    {
        // Get the authenticated owner (brand) first so fee logic follows the current owner setting.
        $owner = getBrand(); // Assuming getBrand() returns the authenticated Owner model

        // Pastikan owner ada dan memiliki ID
        if (!$owner || !$owner->id) {
            return back()->withErrors(['error' => 'Data pemilik tidak ditemukan.']);
        }

        // Sebaiknya, biaya penarikan dan minimal penarikan disimpan di config/app.php
        // Contoh: config('app.withdrawal_fee') dan config('app.min_withdrawal_amount')
        $withdrawalFee = (bool) ($owner->withdrawal_fee_charged ?? true)
            ? (int) config('app.withdrawal_fee', 15000)
            : 0;
        $minWithdrawalAmount = config('app.min_withdrawal_amount', 5000); // Ambil dari config, default 50000

        // Check feature access (if applicable, ensure getData() is correctly defined)
        $feature = getData(); // Asumsi getData() mengembalikan objek yang bisa diakses dengan can()
        if (!$feature || !$feature->can('withdrawal.request')) {
            abort(403, 'Anda tidak memiliki izin untuk melakukan penarikan dana.');
        }

        // Ambil saldo langsung dari tabel owners agar validasi ringan dan konsisten.
        $availableBalance = max((int) ($owner->balance ?? 0), 0);

        // Validasi data request
        $request->validate([
            'amount' => [
                'required',
                'integer',
                'min:' . $minWithdrawalAmount, // Jumlah harus lebih dari atau sama dengan minimal penarikan
                // Validasi custom untuk memastikan jumlah tidak negatif setelah dikurangi biaya
                function ($attribute, $value, $fail) use ($withdrawalFee) {
                    if ($value < $withdrawalFee) {
                        $fail("Jumlah penarikan harus lebih besar dari biaya penarikan (Rp " . number_format($withdrawalFee, 0, ',', '.') . ").");
                    }
                },
            ],
            'notes' => 'nullable|string|max:255',
            // Validasi untuk detail bank yang sekarang dikirim via hidden input
        ], [
            'amount.min'                       => 'Jumlah penarikan minimal adalah Rp ' . number_format($minWithdrawalAmount, 0, ',', '.') . '.',
            'bank_name.required'               => 'Nama bank tidak boleh kosong. Harap lengkapi informasi bank Anda di profil.',
            'bank_account_number.required'     => 'Nomor rekening tidak boleh kosong. Harap lengkapi informasi bank Anda di profil.',
            'bank_account_holder_name.required' => 'Nama pemilik rekening tidak boleh kosong. Harap lengkapi informasi bank Anda di profil.',
        ]);

        $requestedAmount = $request->amount;

        $totalAmountToDeductFromBalance = $requestedAmount + $withdrawalFee;

        $netAmountTransferred = $requestedAmount - $withdrawalFee;

        if ($totalAmountToDeductFromBalance > $availableBalance) {
            return back()->withErrors(['amount' => 'Jumlah penarikan (termasuk biaya) melebihi saldo yang tersedia. Saldo saat ini: Rp ' . number_format($availableBalance, 0, ',', '.') . '. Total yang dibutuhkan: Rp ' . number_format($totalAmountToDeductFromBalance, 0, ',', '.') . '.']);
        }

        $hasPendingWithdrawal = Withdrawal::where('owner_id', $owner->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingWithdrawal) {
            return back()->withErrors(['error' => 'Anda memiliki permintaan penarikan yang sedang dalam proses. Harap tunggu hingga penarikan sebelumnya selesai sebelum mengajukan yang baru.']);
        }


        DB::beginTransaction();

        try {
            // Catat saldo sebelum penarikan
            $amountBeforeFee = $availableBalance;
            $amountAfterFee = $availableBalance - $totalAmountToDeductFromBalance;

            Withdrawal::create([
                'owner_id'                 => $owner->id,
                'amount'                   => $totalAmountToDeductFromBalance, // 'amount' di DB: total yang dipotong dari saldo (termasuk biaya)
                'requested_amount'         => $requestedAmount, // Jumlah yang diminta user
                'withdrawal_fee'           => $withdrawalFee,
                'net_amount_transferred'   => $netAmountTransferred,
                'status'                   => 'pending',
                'notes'                    => $request->notes,
                // Mengambil data bank dari objek $owner yang sudah ditarik dari database
                // Ini lebih aman karena data bank adalah data profil owner, bukan input user yang bisa dimanipulasi
                'bank_name'                => $owner->bank_name,
                'bank_account_number'      => $owner->bank_account_number,
                'bank_account_holder_name' => $owner->bank_account_holder_name,
                'amount_before_fee'        => $amountBeforeFee, // Saldo sebelum penarikan ini
                'amount_after_fee'         => $amountAfterFee, // Saldo setelah penarikan ini
            ]);

            DB::commit(); // Konfirmasi transaksi

            return redirect()->route('partner.withdrawal.histories')
                ->with('success', 'Permintaan withdrawal berhasil dikirim. Jumlah yang akan Anda terima adalah Rp ' . number_format($netAmountTransferred, 0, ',', '.') . '.');
        } catch (\Throwable $e) {
            DB::rollBack(); 
            report($e); // Laporkan exception untuk debugging

            return back()->withErrors(['error' => 'Terjadi kesalahan saat mengirim withdrawal. Silakan coba lagi.']);
        }
    }



    public function histories(Request $request)
    {
        $feature = getData();
        if (!$feature->can('withdrawal.histories')) {
            abort(403, 'Anda tidak memiliki izin.');
        }

        $owner = getBrand();

        $totalPendingWithdrawalsAmount = Withdrawal::where('owner_id', $owner->id)
            ->where('status', 'pending')
            ->sum('amount');

        // Ambil saldo langsung dari kolom balance owner.
        $availableBalance = max((int) ($owner->balance ?? 0), 0);

        // --- Query untuk Riwayat Penarikan ---
        $query = $owner->withdrawals();

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Rentang Waktu
        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            if (count($dates) === 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]);
                // Pastikan mencakup seluruh hari dengan menambahkan ' 23:59:59' ke end date
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }
        }

        // --- Data Summary untuk Kartu (berdasarkan filter saat ini jika ada, atau semua jika tidak ada filter) ---
        // Kloning query *sebelum* pagination untuk menghitung summary
        $summaryQuery = clone $query;

        $totalWithdrawalsCount = $summaryQuery->count();
        $approvedWithdrawalsCount = (clone $summaryQuery)->where('status', 'approved')->count();
        $rejectedWithdrawalsCount = (clone $summaryQuery)->where('status', 'rejected')->count();
        $pendingWithdrawalsCount = (clone $summaryQuery)->where('status', 'pending')->count(); // Jumlah pending

        $totalWithdrawalsAmountInTable = (clone $summaryQuery)->sum('amount'); // Total jumlah uang di tabel hasil filter

        $approvedWithdrawalsAmount = (clone $summaryQuery)->where('status', 'approved')->sum('amount');
        $rejectedWithdrawalsAmount = (clone $summaryQuery)->where('status', 'rejected')->sum('amount');
        // $pendingWithdrawalsAmount tidak lagi dipakai untuk kartu di sini, karena sudah jadi bagian dari availableBalance
        // dan status 'pending' akan ditampilkan secara spesifik di atas tabel.

        // Lanjutkan dengan pagination untuk tabel utama
        $withdrawalHistories = $query->latest()->paginate(10);

        return view('partner.withdrawal.histories', compact(
            'withdrawalHistories',
            'availableBalance', // Saldo yang bisa ditarik (untuk kartu besar)
            'totalWithdrawalsCount', // Total semua permintaan (untuk kartu kecil)
            'totalWithdrawalsAmountInTable', // Total jumlah uang yang ditampilkan di tabel (sesuai filter)
            'approvedWithdrawalsCount', // Jumlah permintaan disetujui (untuk kartu kecil)
            'approvedWithdrawalsAmount', // Jumlah uang disetujui (untuk kartu kecil)
            'rejectedWithdrawalsCount', // Jumlah permintaan ditolak (untuk kartu kecil)
            'rejectedWithdrawalsAmount', // Jumlah uang ditolak (untuk kartu kecil)
            'pendingWithdrawalsCount', // Jumlah permintaan pending (untuk peringatan di atas tabel)
            'totalPendingWithdrawalsAmount' // Jumlah uang pending (untuk peringatan di atas tabel)
        ));
    }
}
