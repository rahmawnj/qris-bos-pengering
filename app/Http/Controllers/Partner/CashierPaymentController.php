<?php

namespace App\Http\Controllers\Partner;

use Carbon\Carbon;
use App\Models\Addon;
use App\Models\Device;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\ServiceOption;
use App\Models\DeviceTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ManualTransactionDetail;

class CashierPaymentController extends Controller
{
    public function create(Request $request)
    {
        $feature = getData();
        if (!$feature->can('partner.cashier.payment.create')) {
            abort(403, 'Anda tidak memiliki izin.');
        }

        $outlets = getData()->outlets->get();

        // Memuat relasi yang diperlukan untuk setiap outlet dalam koleksi.
        // Ini tetap sama karena eager loading dilakukan setelah outlet didapatkan.
	        $outlets->load([
	            'services.serviceOptions.serviceType',
	            'addons',
	            'devices'
	        ]);

        $outletServicesData = [];
        $outletAddonsData = [];

        foreach ($outlets as $outlet) {
            // Data Layanan (Services)
            $outletServicesData[$outlet->id] = [];
            foreach ($outlet->services as $service) {
                $serviceOptions = [];
                foreach ($service->serviceOptions as $sdo) {
                    if ($sdo->serviceType) {
                        $serviceOptions[] = [
                            'id' => $sdo->serviceType->id,
                            'name' => $sdo->serviceType->name,
                        ];
                    }
                }

                $outletServicesData[$outlet->id][$service->id] = [
                    'id' => $service->id,
                    'name' => $service->name,
                    'unit' => $service->unit,
                    'price' => (float) $service->price,
                    'service_options' => $serviceOptions,
                ];
            }

            // Data Add-on (Addons)
            $outletAddonsData[$outlet->id] = [];
            foreach ($outlet->addons as $addon) {
                $outletAddonsData[$outlet->id][$addon->id] = [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'category' => $addon->category,
                    'description' => $addon->description,
                    'price' => (float) $addon->price,
                ];
            }
        }

        return view('partner.cashier_payment.payment', compact('outlets', 'outletServicesData', 'outletAddonsData'));
    }

  public function store(Request $request)
    {
        // 1. Cek Izin Pengguna
        $feature = getData();
        if (!$feature->can('partner.cashier.payment.create')) {
            abort(403, 'Anda tidak memiliki izin.');
        }

        // 2. Validasi Input
        $request->validate([
            'outlet_id'              => 'required|exists:outlets,id',
            'service_id'             => 'required|exists:services,id',
            'amount'                 => 'required|numeric|min:0',
            'payment_method'         => 'required|in:cash,non_cash',
            'cashier_name'           => 'nullable|string|max:255',
            'notes'                  => 'nullable|string',
            'addon_ids'              => 'nullable|string',
            'addon_qty'              => 'nullable|array',
            'addon_qty.*'            => 'nullable|numeric|min:1',
            // START: Validasi Kolom Baru
'quantity' => 'required|numeric|min:0.01',
            'unit'                   => 'nullable|string|max:50', // Satuan unit, optional
            // END: Validasi Kolom Baru
            'device_assignments'     => 'nullable|array',
            'device_assignments.*'   => 'nullable|exists:devices,id',
            'estimated_completion_at' => 'nullable|date',
            'customer_name'          => 'nullable|string|max:255',
            'customer_phone_number'  => 'nullable|string|max:20',
        ]);

        $dataFetcher = getData();

        $outlet = $dataFetcher->outlets->find($request->outlet_id);

        // Jika outlet tidak ditemukan atau user tidak memiliki akses ke outlet ini.
        if (!$outlet) {
            return redirect()->back()->with('error', 'Outlet tidak ditemukan atau Anda tidak memiliki akses ke outlet ini.');
        }

        // 3. Ambil Data Layanan dan Cek Perangkat (jika ada Service Option)
        $service = Service::with('serviceOptions.serviceType')->findOrFail($request->service_id);
        $serviceOptions = $service->serviceOptions
            ->filter(fn($serviceOption) => !is_null($serviceOption->serviceType))
            ->unique('service_type_id')
            ->values();

        $deviceAssignments = [];

        if ($serviceOptions->isNotEmpty()) {
            $request->validate([
                'device_id' => 'required|exists:devices,id',
            ]);

            $selectedDeviceId = $request->device_id;
            $device = $outlet->devices()->find($selectedDeviceId);

            if (!$device) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'device_id' => 'Perangkat tidak ditemukan atau bukan milik outlet ini.',
                    ]);
            }

            foreach ($serviceOptions as $serviceOption) {
                $deviceAssignments[$serviceOption->service_type_id] = $device;
            }
        }


        // 4. Memproses Add-on yang dipilih
        $addonsToSave = [];
        if ($request->filled('addon_ids')) {
            $selectedAddonIds = explode(',', $request->addon_ids);
            // Membersihkan dan mengubah ID add-on menjadi integer
            $selectedAddonIds = array_map('intval', array_filter($selectedAddonIds, 'is_numeric'));
            $addonQtyMap = $request->input('addon_qty', []);

            if (!empty($selectedAddonIds)) {
                $addons = Addon::whereIn('id', $selectedAddonIds)->get();

                foreach ($addons as $addon) {
                    $qty = isset($addonQtyMap[$addon->id]) ? (int) $addonQtyMap[$addon->id] : 1;
                    $qty = max(1, $qty);
                    $addonsToSave[] = [
                        'id'     => $addon->id,
                        'name'   => $addon->name,
                        'price'  => $addon->price,
                        'category' => $addon->category,
                        'qty'    => $qty,
                    ];
                }
            }
        }

        $paymentMethod = 'manual'; // Tipe pembayaran ini dianggap 'manual'
        $time = Carbon::now(); // Waktu saat ini untuk transaksi

        // 5. Generate Order ID
        $order_id = generateOrderId($outlet->code, $paymentMethod, $time, 'multi');

        // 6. Memulai Transaksi Database
        DB::beginTransaction();
        try {

            // 6.1. Buat data Transaksi Utama
            $transaction = Transaction::create([
                'owner_id'           => $outlet->owner->id, // Owner diambil dari relasi outlet
                'outlet_id'          => $outlet->id,
                'order_id'           => $order_id,
                'amount'             => $request->amount,
                'timezone'           => $outlet->timezone,
                'time'               => $time,
                'date'               => $time->toDateString(),
                'type'               => $paymentMethod,
                'status'             => 'success', // Asumsi pembayaran manual selalu sukses langsung
                'notes'              => $request->notes,
                'total_amount'       => $request->amount,
                'fee_amount'         => 0
            ]);

            // 6.2. Buat data Detail Transaksi Manual (Termasuk Quantity & Unit)
            ManualTransactionDetail::create([
                'transaction_id'        => $transaction->id,
                'payment_method'        => $request->payment_method,
                'service_id'            => $service->id,
                'addons'                => $addonsToSave,
                'notes'                 => $request->notes,
                'service_price'         => $service->price,
            'cashier_name'          => $request->cashier_name ?: (Auth::user()->name ?? null),
                'quantity'              => $request->quantity,
                'unit'                  => $service->unit,
                // END: Simpan Kolom Baru
                'estimated_completion_at' => $request->estimated_completion_at ? Carbon::parse($request->estimated_completion_at)->toDateTimeString() : null,
                'customer_name'         => $request->customer_name,
                'customer_phone_number' => $request->customer_phone_number,
            ]);

            // 6.3. Buat data Device Transaction (jika ada Service Option)
            if ($serviceOptions->isNotEmpty()) {
                foreach ($serviceOptions as $serviceOption) {
                    $assignedDevice = $deviceAssignments[$serviceOption->service_type_id] ?? null;

                    if (!$assignedDevice || !$serviceOption->serviceType) {
                        throw new \RuntimeException('Perangkat untuk layanan ' . ($serviceOption->serviceType->name ?? 'terpilih') . ' belum valid.');
                    }

                    DeviceTransaction::create([
                        'transaction_id' => $transaction->id,
                        'device_code'    => $assignedDevice->code,
                        'service_type'   => $serviceOption->serviceType->name,
                        'status'         => true, // Menandai sesi perangkat aktif
                    ]);
                }
            }

            // 7. Commit Transaksi
            DB::commit();

            // 8. Respon Sukses
            return redirect()->back()->with([
                'success' => 'Transaksi manual berhasil dilakukan sebesar Rp. ' . number_format($request->amount, 0, ',', '.') . ' dengan jumlah unit ' . $request->quantity . ' ' . $request->unit . '.',
                'new_transaction' => $transaction // Kirim objek transaksi
            ]);
        } catch (\Exception $e) {
            // 9. Rollback dan Respon Error
            DB::rollBack();
            Log::error('Cashier payment failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->all(),
                'exception_trace' => $e->getTraceAsString(),
            ]);
            // Mengarahkan kembali dengan pesan error
            return redirect()->back()->with('error', 'Pembayaran kasir gagal disimpan: ' . $e->getMessage());
        }
    }
}
