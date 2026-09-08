<?php

namespace App\Http\Controllers\Admin;

use App\Models\Owner;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WithdrawalController extends Controller
{
    public function withdrawal_request(Withdrawal $withdrawal)
    {
        // Eager load owner dan user untuk menghindari N+1 problem
        $withdrawal->load('owner.user');

        $owner = $withdrawal->owner;
        $currentOwnerBalance = $owner ? max((int) ($owner->balance ?? 0), 0) : 0;


        // Passed ke view
        return view('admin.withdrawal.confirm', compact('withdrawal', 'currentOwnerBalance'));
    }

   public function withdrawal_store(Request $request)
{
    $request->validate([
        'withdrawal_id' => 'required|exists:withdrawals,id',
        'action'        => 'required|in:approve,reject',
    ]);

    DB::beginTransaction();

    try {
        // lockForUpdate mencegah double click atau bentrokan data di DB
        $withdrawal = Withdrawal::lockForUpdate()->findOrFail($request->withdrawal_id);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Withdrawal sudah diproses sebelumnya.');
        }

        if ($request->action === 'approve') {
            // 1. Cek apakah saldo owner cukup (Safety Check tambahan)
            $owner = DB::table('owners')->where('id', $withdrawal->owner_id)->first();

            if ($owner->balance < $withdrawal->amount) {
                return back()->with('error', 'Saldo owner tidak mencukupi untuk penarikan ini.');
            }

            // 2. Kurangi saldo owner
            DB::table('owners')
                ->where('id', $withdrawal->owner_id)
                ->decrement('balance', $withdrawal->amount);

            // 3. Update status withdrawal
            $withdrawal->approved_at = now();
            $withdrawal->status = 'approved';

        } else {
            // Jika Reject, saldo tidak dikurangi
            $withdrawal->status = 'rejected';
            $withdrawal->notes = $request->notes ?? 'Withdrawal ditolak oleh admin.';
        }

        $withdrawal->save();

        DB::commit();

        return redirect()
            ->route('admin.withdrawal.histories')
            ->with('success', 'Withdrawal berhasil diperbarui sebagai ' . $withdrawal->status . '.');

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error("Withdrawal Error: " . $e->getMessage());
        return back()->with('error', 'Terjadi kesalahan saat memproses withdrawal: ' . $e->getMessage());
    }
}

    public function histories(Request $request)
    {
        $totalUnwithdrawnFunds = max((int) Owner::sum('balance'), 0);


        // 2. Summary for Withdrawal Requests (Global)
        $globalWithdrawalsQuery = Withdrawal::query();

        $totalGlobalWithdrawalsCount = $globalWithdrawalsQuery->count();
        $approvedGlobalWithdrawalsCount = (clone $globalWithdrawalsQuery)->where('status', 'approved')->count();
        $rejectedGlobalWithdrawalsCount = (clone $globalWithdrawalsQuery)->where('status', 'rejected')->count();
        $pendingGlobalWithdrawalsCount = (clone $globalWithdrawalsQuery)->where('status', 'pending')->count();

        $approvedGlobalWithdrawalsAmount = (clone $globalWithdrawalsQuery)->where('status', 'approved')->sum('amount');
        $rejectedGlobalWithdrawalsAmount = (clone $globalWithdrawalsQuery)->where('status', 'rejected')->sum('amount');
        $pendingGlobalWithdrawalsAmount = (clone $globalWithdrawalsQuery)->where('status', 'pending')->sum('amount');


        // --- Query for Withdrawal Histories Table ---
        $query = Withdrawal::with('owner.user'); // Eager load owner and user to avoid N+1 problem

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by daterange (created_at)
        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            if (count($dates) === 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]);
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }
        }

        // Search by owner brand_name or user name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('owner', function ($q) use ($search) {
                $q->where('brand_name', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQ) use ($search) {
                        $userQ->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Clone query for table footer sums (after all filters but before pagination)
        $tableFooterQuery = clone $query;
        $totalAmountInTable = $tableFooterQuery->sum('amount');
        $totalCountInTable = $tableFooterQuery->count();


        // Get paginated results
        $withdrawalHistories = $query->latest()->paginate(10);

        return view('admin.withdrawal.histories', compact(
            'withdrawalHistories',
            'totalUnwithdrawnFunds', // Total dana yang belum ditarik oleh semua owner
            'totalGlobalWithdrawalsCount',
            'approvedGlobalWithdrawalsCount',
            'rejectedGlobalWithdrawalsCount',
            'pendingGlobalWithdrawalsCount',
            'approvedGlobalWithdrawalsAmount',
            'rejectedGlobalWithdrawalsAmount',
            'pendingGlobalWithdrawalsAmount',
            'totalAmountInTable', // Total amount of currently filtered/displayed withdrawals
            'totalCountInTable' // Total count of currently filtered/displayed withdrawals
        ));
    }

    public function listWithdrawals()
    {
        // Pastikan admin memiliki izin untuk melihat daftar penarikan
        // Sesuaikan dengan sistem izin Anda jika ada, contoh:
        // if (!auth()->user()->can('view-withdrawals')) {
        //     abort(403, 'Anda tidak memiliki izin untuk melihat permintaan penarikan.');
        // }

        // Query dasar untuk permintaan pending
        $baseQuery = Withdrawal::with('owner.user')
                               ->where('status', 'pending');

        // Total count untuk ditampilkan di ringkasan (opsional, bisa dihitung dari $withdrawals->count() juga)
        $pendingWithdrawalCount = (clone $baseQuery)->count(); // Gunakan clone agar tidak memengaruhi query utama

        // Ambil data untuk tabel
        $withdrawals = $baseQuery->latest()->get();

        // Hitung total jumlah dan total count untuk footer tabel dari data yang sudah diambil
        $totalAmountInTable = $withdrawals->sum('amount');
        $totalCountInTable = $withdrawals->count();


        return view('admin.withdrawal.list', compact(
            'withdrawals',
            'pendingWithdrawalCount', // Tambahkan ini untuk ditampilkan di ringkasan
            'totalAmountInTable',
            'totalCountInTable'
        ));
    }

    public function reduceCreate()
    {
        // 1. Ambil semua Owners
        $owners = Owner::with('user')->get();

        foreach ($owners as $owner) {
            $owner->current_balance = max((int) ($owner->balance ?? 0), 0);
        }

        return view('admin.withdrawal.reduce', compact('owners'));
    }

    public function reduceStore(Request $request)
{
    $withdrawalFee = 15000;

    $request->validate([
        'owner_id' => 'required|exists:owners,id',
        'amount' => 'required|numeric|min:' . $withdrawalFee,
        'note' => 'required|string|max:255',
    ]);

    DB::beginTransaction();

    try {
        // 1. Ambil data owner dan kunci barisnya
        $owner = Owner::lockForUpdate()->findOrFail($request->owner_id);

        $totalAmountToDeduct = (int) $request->amount;
        $requestedAmount = $totalAmountToDeduct - $withdrawalFee;

        // 2. CEK SALDO: Sekarang langsung ambil dari kolom balance
        $currentOwnerBalance = $owner->balance;

        // Cek apakah saldo cukup
        if ($currentOwnerBalance < $totalAmountToDeduct) {
            DB::rollBack();
            $errorMessage = 'Gagal: Saldo Owner saat ini (Rp' . number_format($currentOwnerBalance, 0, ',', '.') . ') tidak mencukupi.';
            return back()->withInput()->with('error', $errorMessage);
        }

        // 3. KURANGI SALDO OWNER (Decrement)
        // Kita kurangi kolom balance di tabel owners
        $owner->decrement('balance', $totalAmountToDeduct);

        // 4. Catat riwayat di tabel Withdrawal
        Withdrawal::create([
            'owner_id'                 => $owner->id,
            'amount'                   => $totalAmountToDeduct,
            'requested_amount'         => $requestedAmount,
            'withdrawal_fee'           => $withdrawalFee,
            'net_amount_transferred'   => $requestedAmount,

            'status'                   => 'approved',
            'type'                     => 'admin_reduce',

            'bank_name'                => $owner->bank_name ?? 'ADMIN REDUCTION',
            'bank_account_number'      => $owner->bank_account_number ?? '-',
            'bank_account_holder_name' => $owner->bank_account_holder_name ?? 'SYSTEM',

            'approved_by'              => Auth::id(),
            'approved_at'              => now(),
            'notes'                    => 'Pengurangan saldo paksa oleh Admin: ' . ($request->note),

            'amount_before_fee'        => $currentOwnerBalance,
            'amount_after_fee'         => $currentOwnerBalance - $totalAmountToDeduct,
        ]);

        DB::commit();

        return redirect()->route('admin.withdrawal.histories')
            ->with('success', 'Pengurangan saldo owner berhasil. Balance saat ini: Rp' . number_format($owner->fresh()->balance, 0, ',', '.'));

    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'Kesalahan: ' . $e->getMessage());
    }
}




}
