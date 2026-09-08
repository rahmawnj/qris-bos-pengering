<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Outlet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OwnerOutletController extends Controller
{
    // public function form($outlet_id)
    // {
    //     // Ambil outlet berdasarkan outlet_id
    //     $outlet = Outlet::findOrFail($outlet_id);
    //     // Ambil semua owner (user dengan role 'owner')
    //     $owners = \App\Models\Owner::with('user')->get();

    //     return view('admin.owner_outlets.form', compact('outlet', 'owners'));
    // }

    // public function submit(Request $request, $outlet_id)
    // {
    //     // Validasi input, pastikan request->owners adalah array id owner
    //     $request->validate([
    //         'owners' => 'required|array',
    //         'owners.*' => 'exists:owners,id'
    //     ]);

    //     $outlet = Outlet::findOrFail($outlet_id);
    //     // Sinkronisasi pivot: set relasi many-to-many
    //     $outlet->owners()->sync($request->owners);

    //     return back()->with('success', 'Outlets updated successfully for user');
    // }

}
