<?php

namespace App\Http\Controllers\API;

use App\Models\Device;
use App\Models\Outlet;
use App\Models\ServiceType;
use App\Models\Transaction;
use Illuminate\Http\Request;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class CashierController extends Controller
{
    public function getPrice($deviceId, $serviceTypeId)
    {
        $device = Device::findOrFail($deviceId);
        $serviceType = ServiceType::findOrFail($serviceTypeId);


        $pivot = $device->serviceTypes()->where('service_type_id', $serviceTypeId)->first();
         Log::info('Fetching price for device', [
            'device_id' => $deviceId,
            'service_type_id' => $serviceTypeId,
            'price' => $pivot->pivot->price
        ]);

        if (!$pivot) {
            return response()->json(['message' => 'Harga tidak ditemukan'], 404);
        }

        return response()->json([
            'price' => $pivot->pivot->price
        ]);
    }


}
