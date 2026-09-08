<?php

namespace App\Http\Controllers\Partner;

use Carbon\Carbon;
use App\Models\Member;
use App\Models\Outlet;
use App\Models\TopupHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TopupController extends Controller
{
    public function showTopupForm()
    {
        $brand = getBrand();
        $members = $brand->members()->withPivot('amount')->with('user')->get();

        return view('partner.topup', compact('members'));
    }

    public function fetchMemberByRFID(Request $request)
    {
        $rfid = $request->input('rfid');
        $member = Member::where('rfid', $rfid)->first();

        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Member tidak ditemukan']);
        }

        return response()->json(['success' => true, 'member' => $member]);
    }
    public function processTopup(Request $request)
    {

        $request->validate([
            'member_id'    => 'required',
            'cashier_name' => 'nullable',
            'notes'        => 'nullable',
            'nominal'      => 'required|numeric|min:1',
        ]);

        $member = Member::find($request->member_id);
        if (!$member) {
            return redirect()->back()->withErrors('Member tidak ditemukan');
        }

        $outlet = Outlet::where('code', $request->out)->first();
        if (!$outlet) {
            return redirect()->back()->withErrors('Outlet tidak ditemukan');
        }
        // $outlet = getOutlet();

        DB::beginTransaction();
        try {
            // Update saldo pada tabel subscription (increment amount)
            DB::table('subscription')
                ->where('member_id', $member->id)
                ->where('owner_id',  $outlet->owner->id)
                ->increment('amount', $request->nominal);
            // Buat topup history record
            \App\Models\TopupHistory::create([
                'member_id'    => $member->id,
                'outlet_id'    => $outlet->id,
                'owner_id'     => $outlet->owner->id,
                'amount'       => $request->nominal,
                'time'         => now(),
                'timezone'     => $outlet->timezone,
                'cashier_name' => $request->cashier_name,
                'notes'        => $request->notes,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e . 'Topup gagal, silakan coba lagi.');
        }

        return redirect()->back()->withInput(['out' => $request->get('out')])
            ->with('success', $member->user->name . ' telah berhasil Topup sebesar Rp. ' . number_format($request->nominal, 0, ',', '.'));
    }


    public function topupHistories(Request $request)
    {
        $query = \App\Models\TopupHistory::with('member.user');

        $ownerOutletIds = getData()->outlets->pluck('id')->toArray();
        $query->whereIn('outlet_id', $ownerOutletIds);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $endDate   = \Carbon\Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('time', [$startDate, $endDate]);
        }

        if ($request->filled('member')) {
            $query->whereHas('member.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->member . '%');
            });
        }

        $topupHistories = $query->orderBy('time', 'desc')->paginate(15);
        return view('partner.topup_histories', compact('topupHistories'));
    }
}