<?php

namespace App\Http\Controllers\Admin;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    public function index()
    {
        try {
            $members = Member::with('user')->get();
            return view('admin.members.index', compact('members'));
        } catch (\Exception $e) {
            return redirect()->route('admin.members.index')
                ->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        try {
            // Tampil form untuk membuat member baru
            return view('admin.members.create');
        } catch (\Exception $e) {
            return redirect()->route('admin.members.index')
                ->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        // dd($request);
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:users,email',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Membuat user baru dengan role member
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => bcrypt($request->password),
                    'role'     => 'member',
                ]);

                // Membuat member dengan relasi ke user
                Member::create([
                    'user_id'   => $user->id,
                    'is_verify' => false, // atau sesuai kebutuhan
                ]);
            });

            return redirect()->route('admin.members.index')
                ->with('success', 'Member berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('admin.members.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(Member $member)
    {
        try {
            return view('admin.members.edit', compact('member'));
        } catch (\Exception $e) {
            return redirect()->route('admin.members.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, Member $member)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $member->user_id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        try {
            DB::transaction(function () use ($request, $member) {
                $data = [
                    'name'  => $request->name,
                    'email' => $request->email,
                ];
                if ($request->filled('password')) {
                    $data['password'] = bcrypt($request->password);
                }

                // Update data pada user terkait
                $member->user()->update($data);
            });

            return redirect()->route('admin.members.index')
                ->with('success', 'Member berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('admin.members.index')
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(Member $member)
    {
        try {
            DB::transaction(function () use ($member) {
                // Hapus user yang terkait terlebih dahulu, jika memang tidak ada data lain yang bergantung
                $member->user()->delete();
                // Hapus member
                $member->delete();
            });

            return redirect()->route('admin.members.index')
                ->with('success', 'Member berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.members.index')
                ->with('error', $e->getMessage());
        }
    }
}
