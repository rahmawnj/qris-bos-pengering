<?php

namespace App\Http\Controllers\Partner;

use Carbon\Carbon;
use App\Models\Device;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Constants\OrderStatus;
use Illuminate\Validation\Rule;
use App\Models\DeviceTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\ManualTransactionDetail;

class DeviceController extends Controller
{
    public function serviceOrder()
    {
        $feature = getData();
        if (!$feature->can('partner.service-order.list')) {
            abort(403, 'Anda tidak memiliki izin.');
        }

        $finalStatuses = OrderStatus::finalStatuses();

        $transactions = getData()->transactions
            ->pendingServiceOrders($finalStatuses)
            ->with([
                'manualTransaction' => function ($query) {
                    $query->select([
                        'id',
                        'transaction_id',
                        'service_id',
                        'customer_name',
                        'customer_phone_number',
                        'cashier_name',
                        'payment_method',
                        'estimated_completion_at',
                        'notes',
                        'progress',
                        'addons',
                    ])->with([
                        'service:id,name',
                    ]);
                },
                'deviceTransactions:id,transaction_id,device_code,service_type,activated_at,status,bypass_activation',
            ])
            ->latest()
            ->simplePaginate(20);

        return view('partner.service_order.list', compact('transactions'));
    }


    public function serviceOrderDetail($id)
    {
        $outlets = getData()->outlets->pluck('id')->toArray();

        $transaction = Transaction::with(['manualDetails', 'deviceTransactions'])
            ->where('type', 'manual')
            ->whereIn('outlet_id', $outlets)
            ->findOrFail($id);

        return view('partner.service_order.detail', compact('transaction'));
    }

    public function activateDeviceService(Request $request, DeviceTransaction $deviceTransaction)
    {
        // $feature = getData();
        // if (!$feature->can('partner.service-orders.activate-device')) {
        //     abort(403, 'Anda tidak memiliki izin.');
        // }
        $now = Carbon::now();

        if (is_null($deviceTransaction->activated_at) || ($deviceTransaction->activated_at && $deviceTransaction->activated_at->diffInHours($now) < 24)) {
            $updateData = [
                'status' => true,
                'bypass_activation' => Carbon::now()
            ];

            if (is_null($deviceTransaction->activated_at)) {
                $updateData['activated_at'] = $now;
            }

            DB::beginTransaction();
            try {

                $deviceTransaction->update($updateData);

                DB::commit();
                return redirect()->back()->with('success', 'Layanan perangkat ' . $deviceTransaction->device_code . ' berhasil diaktifkan!');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to activate device service: ' . $e->getMessage(), [
                    'device_transaction_id' => $deviceTransaction->id,
                    'exception' => $e
                ]);
                return redirect()->back()->with('error', 'Gagal mengaktifkan layanan perangkat: ' . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('error', 'Layanan perangkat ' . $deviceTransaction->device_code . ' sudah selesai dan tidak dapat diaktifkan kembali karena sudah melewati batas 24 jam.');
        }
    }

    public function updateServiceProgress(Request $request, ManualTransactionDetail $manualTransactionDetail)
    {
        $request->validate([
            'progress' => ['required', Rule::in(OrderStatus::all())],
        ]);

        $manualTransactionDetail->progress = $request->progress;
        $manualTransactionDetail->save();

        $message = 'Status progress berhasil diperbarui ke ' . OrderStatus::label($request->progress);

        return back()->with('success', $message);
    }
}
