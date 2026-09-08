<?php

namespace App\Http\Controllers\Partner;

use Carbon\Carbon;
use App\Models\ServiceType;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\MemberTransactionDetail;
use App\Models\Outlet;

class MemberPaymentController extends Controller
{
    public function create(Request $request)
    {

      
        $devices = getData()->devices;
        $members = getBrand()->members()
            ->wherePivot('is_verified', 1)
            ->with('user')
            ->get()
            ->sortBy(function ($member) {
                return $member->user->name;
            });


        return view('partner.member_payment.payment', compact('devices', 'members'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount'       => 'required|integer|min:1',
            'cashier_name' => 'nullable|string',
            'notes'        => 'nullable|string',
            'service_type' => 'required',
            'member_id'    => 'required|exists:members,id',
            'device_id'    => 'required|exists:devices,id',
        ]);

        $outlet = Outlet::find($request->outlet_id);

        $serviceType = ServiceType::find($request->service_type);
        $paymentMethod = 'member';
        $time = \Carbon\Carbon::now();

        $order_id = generateOrderId($outlet->code, $paymentMethod, $time, $serviceType->name);

        DB::beginTransaction();
        try {
            $device = $outlet->devices()->where('id', $request->device_id)->first();
            if (!$device) {
                throw new \Exception('Device tidak ditemukan pada outlet ini.');
            }

            // Cek subscription dan potong saldo
            $subscription = DB::table('subscription')
                ->where('member_id', $request->member_id)
                ->where('owner_id', $outlet->owner->id)
                ->lockForUpdate() // supaya tidak race condition
                ->first();

            if (!$subscription) {
                throw new \Exception('Member belum terdaftar dalam subscription dengan owner ini.');
            }

            if ($subscription->amount < $request->amount) {
                throw new \Exception('Saldo member tidak mencukupi.');
            }

            // Potong saldo
            DB::table('subscription')
                ->where('member_id', $request->member_id)
                ->where('owner_id', $outlet->owner->id)
                ->update([
                    'amount' => $subscription->amount - $request->amount,
                    'updated_at' => now(),
                ]);

            // Simpan transaksi
            $transaction = Transaction::create([
                'outlet_id'    => $outlet->id,
                'device_code'  => $device->code,
                'order_id'     => $order_id,
                'amount'       => $request->amount,
                'time'         => $time,
                'type'         => $paymentMethod,
                'service_type' => $serviceType->name,
                'status'       => 'success',
                'timezone'     => $outlet->timezone,
                'owner_id'     => $outlet->owner->id,
            ]);

            MemberTransactionDetail::create([
                'transaction_id' => $transaction->id,
                'member_id'      => $request->member_id,
                'cashier_name'   => $request->cashier_name,
                'notes'          => $request->notes,
            ]);

            // Update status device
            // $device->device_status = $serviceType->slug;
            // $device->save();

            DB::commit();
            return redirect()->back()->with('success', 'Transaksi Member berhasil sebesar Rp. ' . number_format($request->amount, 0, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi member: ' . $e->getMessage());
        }
    }

    public function member_transaction(Request $request)
    {
        $query = MemberTransactionDetail::with(['transaction.owner', 'transaction.outlet']);
        $query->whereHas('transaction', function ($q) use ($request) {

            $outletIds = getData()->outlets->pluck('id')->toArray();
            $q->whereIn('outlet_id', $outletIds);

            // Filter berdasarkan status transaksi
            if ($request->filled('status')) {
                $q->where('status', $request->status);
            }

            // Filter berdasarkan tipe transaksi
            if ($request->filled('type')) {
                $q->where('type', $request->type);
            }

            // Filter berdasarkan tanggal transaksi (dibandingkan dengan created_at di tabel transactions)
            if ($request->filled('start_date')) {
                $q->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $q->whereDate('created_at', '<=', $request->end_date);
            }

            // Filter pencarian: Order ID, Amount, atau Owner (nama user)
            if ($request->filled('search')) {
                $search = $request->search;
                $q->where(function ($q1) use ($search) {
                    $q1->where('order_id', 'like', '%' . $search . '%')
                        ->orWhere('amount', 'like', '%' . $search . '%')
                        ->orWhereHas('owner.user', function ($q2) use ($search) {
                            $q2->where('name', 'like', '%' . $search . '%');
                        });
                });
            }
        });

        $memberTransactions = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('partner.member_payment.transactions', compact('memberTransactions'));
    }
}