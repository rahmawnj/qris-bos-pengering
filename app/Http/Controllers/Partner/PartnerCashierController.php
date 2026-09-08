<?php

namespace App\Http\Controllers\partner;

use App\Models\User;
use App\Models\Outlet;
use App\Models\Cashier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PartnerCashierController extends Controller
{
    public function index()
    {
        $cashiers = getData()->cashiers->get();
        $outlets = getData()->outlets->get();

        return view('partner.cashier_list', compact('cashiers', 'outlets'));
    }

    

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'outlet_id' => 'nullable|exists:outlets,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role'     => 'cashier'
                ]);

                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('public/images/users');
                    $user->image = Storage::url($path);
                    $user->save();
                }

                $user->cashier()->create([
                    'outlet_id' => $request->outlet_id,
                    'status'    => false
                ]);
            });

            return redirect()->route('partner.cashiers.list')
                ->with('success', 'Cashier berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()->route('partner.cashiers.list')
                ->with('error', $e->getMessage());
        }
    }

   

    public function update(Request $request, Cashier $cashier)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $cashier->user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'outlet_id' => 'nullable|exists:outlets,id',
        ]);

        try {
            DB::transaction(function () use ($request, $cashier) {
                $cashier->update([
                    'outlet_id' => $request->outlet_id,
                ]);

                $user = $cashier->user;
                $user->name = $request->name;
                $user->email = $request->email;
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }
                if ($request->hasFile('image')) {
                    if ($user->image) {
                        $imageName = basename($user->image);
                        Storage::delete('public/images/users/' . $imageName);
                    }
                    $path = $request->file('image')->store('public/images/users');
                    $user->image = Storage::url($path);
                }
                $user->save();
            });

            return redirect()->route('partner.cashiers.list')
                ->with('success', 'Cashier berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('partner.cashiers.list')
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(Cashier $cashier)
    {
        try {
            DB::transaction(function () use ($cashier) {
                $user = $cashier->user;
                if ($user->image) {
                    $imageName = basename($user->image);
                    Storage::delete('public/images/users/' . $imageName);
                }
                $user->delete();
            });

            return redirect()->route('partner.cashiers.list')
                ->with('success', 'Cashier berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('partner.cashiers.list')
                ->with('error', $e->getMessage());
        }
    }
}