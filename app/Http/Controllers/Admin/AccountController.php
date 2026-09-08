<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Owner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    private function accountListQuery(?string $search = null)
    {
        $query = User::query();

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function allAccounts(Request $request)
    {
        if ($request->ajax() || $request->has('draw')) {
            $baseQuery = $this->accountListQuery();

            $recordsTotal = (clone $baseQuery)->count();

            if ($search = $request->input('search.value')) {
                $baseQuery = $this->accountListQuery($search);
            }

            $recordsFiltered = (clone $baseQuery)->count();
            $start = max((int) $request->input('start', 0), 0);
            $length = (int) $request->input('length', 10);
            $length = $length > 0 ? min($length, 100) : 10;

            $users = $baseQuery
                ->latest('id')
                ->skip($start)
                ->take($length)
                ->get();

            $canImpersonate = Auth::guard('admin_config')->check() && !session('impersonating');

            $data = $users->map(function (User $user, int $index) use ($start, $canImpersonate) {
                $roleClass = match ($user->role) {
                    'admin' => 'badge bg-danger',
                    'owner' => 'badge bg-primary',
                    'cashier' => 'badge bg-info',
                    'member' => 'badge bg-success',
                    default => 'badge bg-secondary',
                };

                $actions = '';

                if ($canImpersonate) {
                    $actions = '<form action="' . e(route('admin.users.impersonate', $user->id)) . '" method="POST" style="display:inline-block;">'
                        . csrf_field()
                        . '<button type="submit" class="btn btn-sm btn-info" onclick="return confirm(\'Login sebagai user ini?\')">'
                        . '<i class="fa fa-user-shield"></i> Impersonate</button></form>';
                }

                $image = $user->image ? asset($user->image) : asset('assets/img/default-user.png');

                return [
                    'number' => $start + $index + 1,
                    'image' => '<img src="' . e($image) . '" alt="Foto Pengguna" style="width:50px;height:50px;object-fit:cover;border-radius:50%;">',
                    'name' => e($user->name ?? '-'),
                    'email' => e($user->email ?? '-'),
                    'role' => '<span class="' . e($roleClass) . '">' . e(ucfirst($user->role ?? '-')) . '</span>',
                    'created_at' => e($user->created_at ? $user->created_at->format('d M Y H:i') : '-'),
                    'actions' => $actions,
                ];
            });

            return response()->json([
                'draw' => (int) $request->input('draw'),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        $users = collect();

        return view('admin.accounts', compact('users'));
    }

    public function exportData(Request $request)
    {
        $users = $this->accountListQuery($request->input('search'))
            ->latest('id')
            ->get();

        $data = $users->map(function (User $user, int $index) {
            return [
                'number' => $index + 1,
                'name' => $user->name ?? '-',
                'email' => $user->email ?? '-',
                'role' => ucfirst($user->role ?? '-'),
                'created_at' => $user->created_at ? $user->created_at->format('d M Y H:i') : '-',
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function updateOwnerStatus(Request $request, Owner $owner)
    {
        // 1. Validasi Input
        $request->validate([
            'status' => ['required', 'boolean'],
            // account_expires_at boleh kosong (nullable) dan harus berupa tanggal valid
            'account_expires_at' => ['nullable', 'date'],
        ]);

        // 2. Update data Owner
        $owner->status = $request->input('status');

        // Mengatur account_expires_at. Jika input kosong, set null di database.
        $owner->account_expires_at = $request->filled('account_expires_at')
                                        ? $request->input('account_expires_at')
                                        : null;

        $owner->save();

        // 3. Redirect dengan pesan sukses
        return redirect()
            ->route('admin.owners.index') // Sesuaikan dengan route ke halaman daftar owner Anda
            ->with('success', 'Status dan Masa Aktif Brand **' . $owner->brand_name . '** berhasil diperbarui.');
    }

    public function impersonateOwner(Request $request, Owner $owner)
    {
        if (!Auth::guard('admin_config')->check()) {
            return redirect()->route('admin.owners.index')
                ->with('error', 'Akses ditolak. Hanya admin_config yang boleh login sebagai pemilik.');
        }

        if (!$owner->user) {
            return redirect()->route('admin.owners.index')
                ->with('error', 'User pemilik tidak ditemukan.');
        }

        // Simpan penanda impersonate di session agar bisa ditampilkan di UI jika perlu
        $request->session()->put('impersonating', true);
        $request->session()->put('impersonated_owner_id', $owner->id);

        Auth::guard('web')->login($owner->user);

        return redirect()->route('partner.dashboard')
            ->with('success', 'Berhasil login sebagai pemilik: ' . $owner->user->name);
    }

    public function stopImpersonate(Request $request)
    {
        // Logout dari guard web, kembali ke admin_config
        Auth::guard('web')->logout();
        $request->session()->forget(['impersonating', 'impersonated_owner_id']);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Kembali ke akun admin.');
    }
}
