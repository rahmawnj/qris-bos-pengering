<?php

namespace App\Http\Controllers\Partner;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BypassLogController extends Controller
{
       public function index(Request $request) // Inject Request object
    {
        // Assume getData() is available globally or injected
        $devices = getData()->devices;
        $id_devices = $devices->pluck('id')->toArray();

        $query = DB::table('bypass_records')
            ->join('devices', 'bypass_records.device_id', '=', 'devices.id')
            ->join('outlets', 'devices.outlet_id', '=', 'outlets.id')
            ->join('owners', 'outlets.owner_id', '=', 'owners.id')
            ->select(
                'bypass_records.*',
                'devices.name as device_name',
                'devices.code as device_code',
                'outlets.outlet_name as outlet_name', // Assuming outlets table has 'outlet_name'
                'outlets.code as outlet_code',
                'outlets.address as outlet_address',
                'owners.brand_name as brand_name'
            )
            ->whereIn('bypass_records.device_id', $id_devices); // Keep the device ID filter

        // --- Filters Start Here ---

        // Filter based on search keyword (outlet, device, status, or type)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('outlets.outlet_name', 'like', '%' . $search . '%')
                  ->orWhere('devices.name', 'like', '%' . $search . '%')
                  ->orWhere('devices.code', 'like', '%' . $search . '%')
                  ->orWhere('bypass_records.bypass_status', 'like', '%' . $search . '%')
                  ->orWhere('bypass_records.type', 'like', '%' . $search . '%'); // Also search in type
            });
        }

        // Filter based on bypass type ('type' - bypass or session)
        if ($request->filled('type')) {
            $query->where('bypass_records.type', $request->input('type'));
        }

        // Filter based on date range (daterange)
        if ($request->filled('daterange')) {
            $dateRange = explode(' - ', $request->input('daterange'));
            if (count($dateRange) === 2 && !empty($dateRange[0]) && !empty($dateRange[1])) {
                try {
                    $startDate = Carbon::createFromFormat('d/m/Y', $dateRange[0])->startOfDay();
                    $endDate = Carbon::createFromFormat('d/m/Y', $dateRange[1])->endOfDay();
                    $query->whereBetween('bypass_records.created_at', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    Log::error('Date Range Filter Error (Partner Bypass Logs): ' . $e->getMessage(), [
                        'daterange_input' => $request->input('daterange'),
                        'exception' => $e
                    ]);
                    // Optionally, you might want to redirect back with an error or ignore the filter
                }
            }
        }

        // --- Filters End Here ---

        $logs = $query->orderBy('bypass_records.created_at', 'desc')->paginate(15); // Adjust pagination as needed

        return view('partner.bypass_log', compact('logs'));
    }

}