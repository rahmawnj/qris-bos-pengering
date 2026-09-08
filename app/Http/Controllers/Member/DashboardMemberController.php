<?php

namespace App\Http\Controllers\Member;

use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardMemberController extends Controller
{
    public function dashboard()
    {
        return view('member.dashboard');
    }

    public function membership()
    {
        return view('member.membership');
    }

    public function outlet_list()
    {
        return view('member.outlet_list');
    }

      public function subscription(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:owners,id',
        ]);

        $member = Auth::user()->member;

        if ($member->owners()->where('owner_id', $request->owner_id)->exists()) {
            return back()->with('info', 'Sudah subscribe ke owner ini.');
        }

        $member->owners()->attach($request->owner_id, [
            'is_verified' => false,
            'amount' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Berhasil subscribe, menunggu verifikasi.');
    }

}
