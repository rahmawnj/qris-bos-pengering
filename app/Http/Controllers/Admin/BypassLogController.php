<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Support\TransactionAccessScope;

class BypassLogController extends Controller
{
    protected function buildQuery(Request $request, ?array $deviceIds = null, ?array $outletIds = null)
    {
        $query = DB::table('bypass_records')
            ->join('devices', 'bypass_records.device_id', '=', 'devices.id')
            ->leftJoin('outlets', 'devices.outlet_id', '=', 'outlets.id')
            ->leftJoin('owners', 'outlets.owner_id', '=', 'owners.id')
            ->select(
                'bypass_records.*',
                'devices.name as device_name',
                'devices.code as device_code',
                'outlets.outlet_name as outlet_name',
                'outlets.code as outlet_code',
                'outlets.address as outlet_address',
                'owners.brand_name as brand_name'
            );

        if (is_array($deviceIds) && empty($deviceIds)) {
            $query->whereRaw('1 = 0');
        } elseif (!empty($deviceIds)) {
            $query->whereIn('bypass_records.device_id', $deviceIds);
        }
        if (is_array($outletIds) && empty($outletIds)) {
            $query->whereRaw('1 = 0');
        } elseif (!empty($outletIds)) {
            $query->whereIn('outlets.id', $outletIds);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('outlets.outlet_name', 'like', '%' . $search . '%')
                    ->orWhere('devices.name', 'like', '%' . $search . '%')
                    ->orWhere('devices.code', 'like', '%' . $search . '%')
                    ->orWhere('bypass_records.bypass_status', 'like', '%' . $search . '%')
                    ->orWhere('bypass_records.type', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('type')) {
            $query->where('bypass_records.type', $request->input('type'));
        }

        if ($request->filled('daterange')) {
            $dateRange = explode(' - ', $request->input('daterange'));
            if (count($dateRange) === 2 && !empty($dateRange[0]) && !empty($dateRange[1])) {
                try {
                    $startDate = Carbon::createFromFormat('d/m/Y', trim($dateRange[0]))->startOfDay();
                    $endDate = Carbon::createFromFormat('d/m/Y', trim($dateRange[1]))->endOfDay();
                    $query->whereBetween('bypass_records.created_at', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    Log::error('Date Range Filter Error: ' . $e->getMessage(), [
                        'daterange_input' => $request->input('daterange'),
                        'exception' => $e
                    ]);
                }
            }
        }

        return $query;
    }

    public function index(Request $request)
    {
        $isAdminContext = TransactionAccessScope::isAdminContext();

        $deviceIds = null;
        $outletIds = null;

        if (!$isAdminContext) {
            $deviceIds = getData()->devices->pluck('id')->toArray();
            $outletIds = getData()->getOutletIds();
        }

        $logs = $this->buildQuery($request, $deviceIds, $outletIds)
            ->orderBy('bypass_records.created_at', 'desc')
            ->paginate(50);

        $routeName = $isAdminContext ? 'admin.bypass.logs' : 'partner.bypass.logs';
        $breadcrumbItems = $isAdminContext ? ['Admin', 'Monitoring', 'Bypass Logs'] : ['Partner', 'Monitoring', 'Bypass Logs'];

        return view('admin.bypass_log', compact('logs', 'routeName', 'breadcrumbItems'));
    }
}
