<?php

namespace App\Http\Controllers\Partner;

use App\Models\Outlet;
use App\Models\QrisBillingPayment;
use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class OutletController extends Controller
{
    public function list()
    {
        $outlets = getData()->outlets
            ->with(['devices', 'services', 'cashiers', 'qrisBillingPayments'])
            ->withCount([
                'devices',
                'services',
                'cashiers',
            ])
            ->get();

        return view('partner.outlets.list', compact('outlets'));
    }

    public function detail(Outlet $outlet, Request $request)
    {

        $currentTab = $request->query('page', 'detail');
        $serviceTypes = ServiceType::all();

        return view('partner.outlets.detail', compact('outlet', 'serviceTypes', 'currentTab'));
    }

    public function billing(Outlet $outlet)
    {
        $this->authorizeOutlet($outlet);

        if (!$outlet->qris_billing_enabled) {
            return redirect()->route('partner.outlets.list')
                ->with('error', 'Outlet ini belum memiliki fitur perpanjangan QRIS.');
        }

        return redirect()->route('partner.qris-billing.show', $outlet);
    }

    public function uploadBillingProof(Request $request, Outlet $outlet)
    {
        $this->authorizeOutlet($outlet);

        if (!$outlet->qris_billing_enabled) {
            return redirect()->route('partner.outlets.list')
                ->with('error', 'Outlet ini belum memiliki fitur perpanjangan QRIS.');
        }

        $validated = $request->validate([
            'proof_of_payment' => 'required|image|max:5120',
        ]);

        $outlet->loadMissing(['devices', 'qrisBillingPayments']);
        $summary = $outlet->qrisBillingUnpaidSummary();

        if ($summary['pending_count'] > 0) {
            return redirect()->back()->with('error', 'Bukti pembayaran perpanjangan sudah dikirim dan masih menunggu konfirmasi.');
        }

        if (empty($summary['periods'])) {
            return redirect()->back()->with('error', 'Outlet masih aktif, belum perlu perpanjangan.');
        }

        $startDate = now()->copy()->startOfDay()->format('Y-m-d');
        $endDate = now()->copy()->addMonthNoOverflow()->startOfDay()->format('Y-m-d');

        $path = $request->file('proof_of_payment')->store('qris_billing_proofs', 'public');

        QrisBillingPayment::create([
            'outlet_id' => $outlet->id,
            'period_start' => $startDate,
            'period_end' => $endDate,
            'amount' => $outlet->qris_billing_amount,
            'status' => 'pending',
            'paid_at' => null,
            'proof_of_payment' => $path,
        ]);

        return redirect()->back()
            ->with('success', 'Bukti pembayaran perpanjangan berhasil diunggah. Tunggu konfirmasi dari administrator.');
    }

 public function serviceType(Request $request, Outlet $outlet)
    {
        $rules = [
            'services' => 'nullable|array',
            'services.*.name' => 'sometimes|required|string|max:255',
            'services.*.price' => 'sometimes|required|numeric|min:0',
            // --- Penambahan Validasi Unit untuk Layanan yang Sudah Ada ---
            'services.*.unit' => 'sometimes|required|in:kg,pcs,liter,hour,unit',
            // -----------------------------------------------------------------
            'services.*.service_type_ids' => 'nullable|array',
            'services.*.service_type_ids.*' => 'exists:service_types,id',
            'services.*._delete' => 'nullable|boolean', // Flag to mark for deletion

            'new_services' => 'nullable|array',
            'new_services.*.name' => 'required|string|max:255',
            'new_services.*.price' => 'required|numeric|min:0',
            // --- Penambahan Validasi Unit untuk Layanan Baru ---
            'new_services.*.unit' => 'required|in:kg,pcs,liter,hour,unit',
            // -----------------------------------------------------
            'new_services.*.service_type_ids' => 'nullable|array',
            'new_services.*.service_type_ids.*' => 'exists:service_types,id',
        ];

        // Custom validation messages
        $messages = [
            'services.*.name.required' => 'Nama layanan tidak boleh kosong.',
            'services.*.price.required' => 'Harga layanan tidak boleh kosong.',
            'services.*.price.numeric' => 'Harga layanan harus berupa angka.',
            'services.*.price.min' => 'Harga layanan tidak boleh negatif.',
            // --- Pesan Unit yang Sudah Ada ---
            'services.*.unit.required' => 'Unit layanan tidak boleh kosong.',
            'services.*.unit.in' => 'Unit layanan yang dipilih tidak valid.',
            // ----------------------------------
            'services.*.service_type_ids.*.exists' => 'Salah satu tipe layanan yang dipilih tidak valid.',

            'new_services.*.name.required' => 'Nama layanan baru tidak boleh kosong.',
            'new_services.*.price.required' => 'Harga layanan baru tidak boleh kosong.',
            'new_services.*.price.numeric' => 'Harga layanan baru harus berupa angka.',
            'new_services.*.price.min' => 'Harga layanan baru tidak boleh negatif.',
            // --- Pesan Unit Baru ---
            'new_services.*.unit.required' => 'Unit layanan baru tidak boleh kosong.',
            'new_services.*.unit.in' => 'Unit layanan baru yang dipilih tidak valid.',
            // -----------------------
            'new_services.*.service_type_ids.*.exists' => 'Salah satu tipe layanan baru yang dipilih tidak valid.',
        ];

        // Validate the incoming request data
        $validatedData = $request->validate($rules, $messages);

        // Start a database transaction to ensure atomicity
        DB::beginTransaction();

        try {
            // 1. Process Existing Services
            if (isset($validatedData['services'])) {
                foreach ($validatedData['services'] as $serviceId => $serviceData) {
                    $service = $outlet->services()->find($serviceId);

                    if ($service) {
                        // If service is marked for deletion
                        if (isset($serviceData['_delete']) && $serviceData['_delete'] == 1) {
                            $service->delete(); // Delete the service
                            // Detach all associated service types from the pivot table
                            $service->serviceTypes()->detach();
                        } else {
                            // Update basic service details, termasuk 'unit'
                            $service->name = $serviceData['name'];
                            $service->price = $serviceData['price'];
                            $service->unit = $serviceData['unit']; // <-- MEMASUKKAN UNIT
                            $service->save();

                            // Synchronize service_type_ids for the existing service
                            $serviceTypeIds = $serviceData['service_type_ids'] ?? [];
                            $service->serviceTypes()->sync($serviceTypeIds);
                        }
                    }
                }
            }

            // 2. Process New Services
            if (isset($validatedData['new_services'])) {
                foreach ($validatedData['new_services'] as $newServiceData) {
                    // Create a new service instance, termasuk 'unit'
                    $newService = new Service([
                        'name' => $newServiceData['name'],
                        'price' => $newServiceData['price'],
                        'unit' => $newServiceData['unit'], // <-- MEMASUKKAN UNIT
                        'outlet_id' => $outlet->id, // Associate with the current outlet
                    ]);
                    $outlet->services()->save($newService); // Save the new service

                    // Attach service_type_ids for the newly created service
                    $newServiceTypeIds = $newServiceData['service_type_ids'] ?? [];
                    $newService->serviceTypes()->attach($newServiceTypeIds);
                }
            }

            // Commit the transaction if all operations are successful
            DB::commit();

            return redirect()->route('partner.outlets.detail', ['outlet' => $outlet->id, 'tab' => 'services'])
                ->with('success', 'Daftar layanan berhasil diperbarui!');
        } catch (\Exception $e) {
            // Rollback the transaction in case of any error
            DB::rollBack();
            // Log the error for debugging purposes
            Log::error("Error updating services for outlet {$outlet->id}: " . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal memperbarui daftar layanan. Silakan coba lagi.')
                ->withInput();
        }
    }

    public function update(Request $request, Outlet $outlet)
    {
        try {
            $request->validate([
                'outlet_name' => 'required|string|max:255',
                'address' => 'nullable|string|max:500',
                'phone_number' => 'nullable|string|max:20',
                'timezone' => 'required|string',
            ]);

            $data = $request->only([
                'outlet_name',
                'address',
                'phone_number',
                'timezone',
            ]);

            $outlet->update($data);

            return redirect()->route('partner.outlets.detail', [
                'outlet' => $outlet->id,
                'tab' => 'edit-profile'
            ])->with('success', 'Profil outlet berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->route('partner.outlets.detail', [
                'outlet' => $outlet->id,
                'tab' => 'edit-profile'
            ])->with('error', 'Gagal memperbarui profil outlet: ' . $e->getMessage());
        }
    }


    public function destroy(Outlet $outlet)
    {
        $feature = getData();
        if (!$feature->can('partner.outlets.destroy')) {
            abort(403, 'Anda tidak memiliki izin.');
        }

        try {
            $outlet_name = $outlet->outlet_name;
            $outlet->delete();

            return redirect()->route('partner.outlets.list')
                ->with('success', 'Outlet "' . $outlet_name . '" berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('partner.outlets.list')
                ->with('error', 'Gagal menghapus outlet: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Outlet $outlet)
    {
        $feature = getData();
        if (!$feature->can('partner.outlets.update-status')) {
            abort(403, 'Anda tidak memiliki izin.');
        }

        $request->validate([
            'status' => 'required|boolean',
        ]);

        // try {
        $statusText = $request->status ? 1 : 0;
        $outlet->status = $statusText;
        $outlet->save();

        return redirect()->back()->with('success', "Status outlet `{$outlet->outlet_name}` berhasil diubah menjadi `{$statusText}`.");
        // } catch (\Exception $e) {
        //     Log::error("Failed to update outlet status for ID: {$outlet->id}. Error: " . $e->getMessage(), [
        //         'exception' => $e,
        //         'outlet_id' => $outlet->id,
        //     ]);

        //     return redirect()->back()->with('error', 'Terjadi kesalahan saat mengubah status outlet. Silakan coba lagi.');
        // }
    }

    public function store(Request $request)
    {
        $feature = getData();
        if (!$feature->can('partner.outlets.store')) {
            abort(403, 'Anda tidak memiliki izin.');
        }

        $request->validate([
            'outlet_name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'timezone' => 'required|in:WIB,WITA,WIT',
            'status' => 'boolean',
        ]);

        try {
            $outlet = new Outlet();
            $outlet->owner_id = getBrand()->id;
            $outlet->outlet_name = $request->outlet_name;
            $outlet->code =  $this->generateUniqueCode();
            $outlet->address = $request->address;
            $outlet->phone_number = $request->phone_number;
            $outlet->timezone = $request->timezone;
            $outlet->save();

            return redirect()->route('partner.outlets.list')->with('success', 'Outlet "' . $outlet->outlet_name . '" berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error adding new outlet: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan outlet. Silakan coba lagi. Error: ' . $e->getMessage());
        }
    }

    private function generateUniqueCode()
    {
        do {
            $code = 'OUT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        } while (Outlet::where('code', $code)->exists());

        return $code;
    }

    private function authorizeOutlet(Outlet $outlet): void
    {
        $outletIds = getData()->getOutletIds();

        if (empty($outletIds) && \Illuminate\Support\Facades\Auth::guard('outlet')->check()) {
            $outletIds = [\Illuminate\Support\Facades\Auth::guard('outlet')->id()];
        }

        abort_unless(in_array($outlet->id, $outletIds), 403, 'Akses outlet ditolak.');
    }
}
