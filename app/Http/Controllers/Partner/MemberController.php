<?php

namespace App\Http\Controllers\Partner;

use App\Models\Member;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MemberController extends Controller
{

    public function verified()
    {

        try {
            $brand = getBrand();
            $verifiedMembers = $brand->members()->wherePivot('is_verified', 1)->get();
            $verifiedMembersCount = $verifiedMembers->count();
            $unverifiedMembersCount = $brand->members()->wherePivot('is_verified', 0)->count();

            return view('partner.members.verified', compact('verifiedMembers', 'verifiedMembersCount', 'unverifiedMembersCount'));
        } catch (\Exception $e) {
            return redirect()->route('partner.members.verified')->with('error', $e->getMessage());
        }
    }

    /**
     * Tampilkan halaman member yang belum diverifikasi.
     */
    public function unverified()
    {

        try {
            $brand = getBrand();
            $unverifiedMembers = $brand->members()->wherePivot('is_verified', 0)->get();
            $verifiedMembersCount = $brand->members()->wherePivot('is_verified', 1)->count();
            $unverifiedMembersCount = $brand->members()->wherePivot('is_verified', 0)->count();

            return view('partner.members.unverified', compact('unverifiedMembers', 'verifiedMembersCount', 'unverifiedMembersCount'));
        } catch (\Exception $e) {
            return redirect()->route('partner.members.unverified')->with('error', $e->getMessage());
        }
    }

    /**
     * Verifikasi subscription member (set is_verified menjadi 1 pada pivot).
     */
    public function verify(Request $request, Member $member)
    {
        $feature = getData();
        if (!$feature->can('partner.members.verify')) {
            abort(403, 'Anda tidak memiliki izin.');
        }
        try {
            $brand = getBrand();
            $brand->members()->updateExistingPivot($member->id, ['is_verified' => 1]);
            return redirect()->back()->with('success', 'Verifikasi subscription member berhasil.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus subscription member (hapus record pivot).
     */
    public function destroySubscription(Member $member)
    {

        $feature = getData();
        if (!$feature->can('partner.members.subscription.destroy')) {
            abort(403, 'Anda tidak memiliki izin.');
        }
        try {
            $brand = getBrand();
            $brand->members()->detach($member->id);
            return redirect()->back()->with('success', 'Subscription member berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}