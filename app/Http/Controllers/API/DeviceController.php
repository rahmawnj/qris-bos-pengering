<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Device;
use Illuminate\Http\Request;
use App\Models\DeviceTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class DeviceController extends Controller
{
    public function checkDeviceStatus(Request $request)
    {
        $requestedServiceType = $this->normalizeServiceType($request->query('service_type'));

        // Validate if the 'device_code' parameter is present in the request.
        if (!$request->has('device_code')) {
            return response()->json([
                'status' => 'failure',
                'status_device' => 'off',
                'message' => 'Parameter device_code harus disertakan.',
                'activation_date' => null,
                'source' => null
            ], 400);
        }

        // Find the device based on the provided 'device_code'.
        $device = Device::with('outlet')->where('code', $request->device_code)->first();

        // If the device is not found, return a 404 response.
        if (!$device) {
            return response()->json([
                'status' => 'failure',
                'status_device' => 'off',
                'message' => 'Device tidak ditemukan.',
                'activation_date' => null,
                'source' => null
            ], 404);
        }

        if ($device->outlet?->has_overdue_billing) {
        return response()->json([
        'status' => 'failure',
        'status_device' => 'off',
        'message' => 'Masa aktif QRIS habis. Silakan perpanjang.',
        'activation_date' => null,
        'source' => 'qris_billing'
        ], 200);
        }

        $deviceBypassActivation = null;
        if (
            $device->device_status !== 'off' &&
            $this->serviceTypeMatches($device->device_status, $requestedServiceType) &&
            $device->bypass_activation &&
            Carbon::parse($device->bypass_activation)->greaterThanOrEqualTo(Carbon::now()->subHours(24))
        ) {
            $deviceBypassActivation = Carbon::parse($device->bypass_activation);
        }

        // Ambil sesi drop-off aktif terbaru untuk device ini. Jika washer dan dryer
        // berbagi kode device, IoT bisa mengirim service_type agar tidak mengambil
        // aktivasi milik layanan lain.
        $deviceTransactionBypassActivation = DeviceTransaction::where('device_code', $request->device_code)
            ->when($requestedServiceType !== 'off', function ($query) use ($requestedServiceType) {
                $query->whereRaw("LOWER(REPLACE(service_type, ' ', '_')) = ?", [$requestedServiceType]);
            })
            ->whereNotNull('bypass_activation')
            ->where('status', true)
            ->where('bypass_activation', '>=', Carbon::now()->subHours(24))
            ->orderByDesc('bypass_activation')
            ->orderByDesc('id')
            ->first();

        // Get the bypass_activation from the found device transaction.
        $transactionBypassActivation = $deviceTransactionBypassActivation?->bypass_activation;

        $activations = [];

        if ($deviceBypassActivation) {
            $activations[] = [
                'time' => $deviceBypassActivation,
                'source' => 'bypass',
                'serviceType' => $this->normalizeServiceType($device->device_status),
                'note' => $device->bypass_note,
                'model' => $device
            ];
        }

        if ($transactionBypassActivation) {
            $activations[] = [
                'time' => Carbon::parse($transactionBypassActivation),
                'source' => 'session',
                'serviceType' => $this->normalizeServiceType($deviceTransactionBypassActivation->service_type),
                'note' => 'Pesanan Drop-off',
                'model' => $deviceTransactionBypassActivation
            ];
        }

        $qrisBypass = \App\Models\QrisTransactionDetail::where('device_code', $request->device_code)
            ->when($requestedServiceType !== 'off', function ($query) use ($requestedServiceType) {
                $query->whereRaw("LOWER(REPLACE(service_type, ' ', '_')) = ?", [$requestedServiceType]);
            })
            ->where('bypass_status', 'active')
            ->whereNotNull('bypass_activation')
            ->where('bypass_activation', '>=', Carbon::now()->subHours(24))
            ->latest('bypass_activation')
            ->first();

        if ($qrisBypass) {
            $activations[] = [
                'time' => Carbon::parse($qrisBypass->bypass_activation),
                'source' => 'qris_bypass',
                'serviceType' => $this->normalizeServiceType($qrisBypass->service_type),
                'note' => 'Bypass QRIS', // Simplified note
                'model' => $qrisBypass
            ];
        }

        $earliestValidBypassActivation = null;
        $sourceOfActivation = null;
        $serviceType = null;
        $bypassNote = null;
        $activeModel = null;

        if (count($activations) > 0) {
            usort($activations, function($a, $b) {
                return $b['time']->timestamp <=> $a['time']->timestamp;
            });
            $selected = $activations[0];
            
            $earliestValidBypassActivation = $selected['time'];
            $sourceOfActivation = $selected['source'];
            $serviceType = $selected['serviceType'];
            $bypassNote = $selected['note'];
            $activeModel = $selected['model'];
        }

        if ($earliestValidBypassActivation) {
            DB::table('bypass_records')->insert([
                'device_id' => $device->id,
                'type' => $sourceOfActivation === 'qris_bypass' ? 'bypass' : $sourceOfActivation,
                'bypass_activation' => $earliestValidBypassActivation,
                'bypass_status' => $serviceType,
                'note' => $bypassNote,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        if ($sourceOfActivation == 'bypass') {
            $activeModel->device_status = 'off';
            $activeModel->save();
        } elseif ($sourceOfActivation == 'session') {
            $activeModel->status = false;
            $activeModel->save();
        } elseif ($sourceOfActivation == 'qris_bypass') {
            $activeModel->bypass_status = 'activated';
            $activeModel->save();
        }

        $serviceType = $this->normalizeServiceType($serviceType ?? 'off');

        return response()->json([
            'status' => 'success',
            'status_device' => $serviceType,
            'source' => $sourceOfActivation,
            'activation_date' => $earliestValidBypassActivation?->format('Y-m-d H:i:s'),
            'message' => 'Status diterima'
        ]);
    }

	public function toggleStatus(Request $request, Device $device)
	{
	    // try {
    
    // Validasi input
    // $request->validate([
    //     'device_status' => 'required|string',
    //     'bypass_note' => 'required|string|min:5', // Pastikan catatan tidak kosong
    // ]);

	    $device->loadMissing('outlet');

	    if ($device->outlet?->has_overdue_billing) {
	        return response()->json([
	            'status' => 'error',
	            'message' => 'Masa aktif QRIS habis. Silakan perpanjang.',
	        ], 200);
	    }

	    Log::info($request);
    $device->device_status = $request->input('device_status');
    $device->bypass_activation = Carbon::now();
    $device->bypass_note = $request->input('bypass_note'); // Simpan catatan
    $device->save();

    Log::info('Device status updated', [
        'device_code' => $device->code,
        'new_status' => $device->device_status,
        'bypass_note' => $device->bypass_note // Log juga catatannya
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Device ' . $device->code . ' berhasil diperbarui menjadi "' . $device->device_status . '"'
	    ]);
	    // } catch (\Exception $e) {
    //      return response()->json([
    //          'status' => 'error',
    //          'message' => 'Gagal memperbarui status device. ' . $e->getMessage()
    //      ], 500);
    // }
	}

	private function normalizeServiceType(?string $serviceType): string
	{
	    $serviceType = trim((string) $serviceType);

	    return $serviceType === '' ? 'off' : str($serviceType)->slug('_')->toString();
	}

    private function serviceTypeMatches(?string $serviceType, string $requestedServiceType): bool
    {
        return $requestedServiceType === 'off'
            || $this->normalizeServiceType($serviceType) === $requestedServiceType;
    }
	}
