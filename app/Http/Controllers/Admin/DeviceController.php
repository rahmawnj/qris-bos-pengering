<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Outlet;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $serviceTypes = ServiceType::orderBy('name')->get(['id', 'name']);

        // Gunakan method index yang sama untuk halaman biasa dan request DataTables AJAX.
        if ($request->ajax() || $request->has('draw')) {
            $query = Device::with(['outlet.owner']);
            $recordsTotal = (clone $query)->count();

            $isExport = $request->boolean('export');
            $search = trim((string) $request->input('search.value', $request->input('search', '')));
            if ($search !== '') {
                $query->where(function ($deviceQuery) use ($search) {
                    $deviceQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('outlet', function ($outletQuery) use ($search) {
                            $outletQuery->where('outlet_name', 'like', "%{$search}%")
                                ->orWhereHas('owner', function ($ownerQuery) use ($search) {
                                    $ownerQuery->where('brand_name', 'like', "%{$search}%");
                                });
                        });
                });
            }

            $recordsFiltered = (clone $query)->count();
            $start = max((int) $request->input('start', 0), 0);
            $length = (int) $request->input('length', 10);
            $length = $length > 0 ? min($length, 100) : 10;

            $devicesQuery = $query->latest('id');
            $devices = $isExport
                ? $devicesQuery->get()
                : $devicesQuery->skip($start)->take($length)->get();

            if ($isExport) {
                $start = 0;
            }

            $csrf = csrf_token();
            $data = $devices->map(function (Device $device, int $index) use ($start, $serviceTypes, $csrf) {
                $outlet = $device->outlet;
                $owner = $outlet?->owner;
                $logo = $owner?->brand_logo
                    ? asset($owner->brand_logo)
                    : asset('assets/img/default-user.png');

                $statusOptions = '<option value="off"' . ($device->device_status === 'off' ? ' selected' : '') . '>off</option>';
                foreach ($serviceTypes as $type) {
                    $value = (string) $type->slug;
                    $selected = (string) $device->device_status === $value ? ' selected' : '';
                    $statusOptions .= '<option value="' . e($value) . '"' . $selected . '>' . e($type->name) . '</option>';
                }

                $statusSelect = '<select class="device-status-select form-control" data-device-id="' . (int) $device->id . '" data-original="' . e((string) $device->device_status) . '">'
                    . $statusOptions
                    . '</select>';

                $actions = '<a href="' . e(route('admin.devices.edit', $device)) . '" class="btn btn-primary btn-sm">'
                    . '<i class="fas fa-edit"></i> Sunting</a> '
                    . '<form action="' . e(route('admin.devices.destroy', $device)) . '" method="POST" class="d-inline device-delete-form">'
                    . '<input type="hidden" name="_token" value="' . e($csrf) . '">'
                    . '<input type="hidden" name="_method" value="DELETE">'
                    . '<button type="submit" class="btn btn-danger btn-sm">'
                    . '<i class="fas fa-trash"></i> Hapus</button></form>';

                $statusText = $device->device_status === 'off'
                    ? 'off'
                    : ($serviceTypes->first(fn ($type) => $type->slug === $device->device_status)?->name
                        ?? $device->device_status
                        ?? '-');
                $brandText = ($owner?->brand_name ?? '-') . ' - ' . ($outlet?->outlet_name ?? '-');
                $deviceText = ($device->name ?? '-') . ' (' . ($device->code ?? '-') . ')';

                return [
                    'number' => $start + $index + 1,
                    'brand' => '<div style="display:flex;align-items:center;gap:10px;">'
                        . '<img src="' . e($logo) . '" alt="Logo Brand" style="width:50px;height:50px;object-fit:cover;border-radius:50%;">'
                        . '<div><div>' . e($owner?->brand_name ?? '-') . '</div>'
                        . '<div style="font-size:.9em;color:gray;">' . e($outlet?->outlet_name ?? '-') . '</div></div></div>',
                    'brand_text' => $brandText,
                    'device' => '<div>' . e($device->name ?? '-') . '</div>'
                        . '<div style="font-size:.9em;color:gray;">' . e($device->code ?? '-') . '</div>',
                    'device_text' => $deviceText,
                    'status' => $statusSelect,
                    'status_text' => $statusText,
                    'actions' => $actions,
                ];
            });

            if ($isExport) {
                return response()->json(['data' => $data]);
            }

            return response()->json([
                'draw' => (int) $request->input('draw'),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        // Data tabel diisi melalui jQuery DataTables, bukan dirender sekaligus di Blade.
        $devices = collect();

        return view('admin.devices.index', compact('devices', 'serviceTypes'));
    }

    public function create()
    {
        try {
            $outlets = Outlet::all();
            return view('admin.devices.create', compact('outlets'));
        } catch (\Exception $e) {
            return redirect()->route('admin.devices.index')
                ->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'outlet_id' => 'required|exists:outlets,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Simpan device tanpa kode dulu
                $device = Device::create([
                    'name'      => $request->name,
                    'outlet_id' => $request->outlet_id,
                    'status'    => false,
                    'code'      => null, // Temporary null, nanti di-update
                ]);

                // 2. Generate kode pakai ID yang baru dibuat
                $code = Device::generateUniqueCode($device->id);

                // 3. Update device dengan kode
                $device->update(['code' => $code]);
            });

            return redirect()->route('admin.devices.index')
                ->with('success', 'Device berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('admin.devices.index')
                ->with('error', 'Gagal menambahkan device: ' . $e->getMessage());
        }
    }


    public function edit(Device $device)
    {
        try {
            $outlets = Outlet::all();
            return view('admin.devices.edit', compact('device', 'outlets'));
        } catch (\Exception $e) {
            return redirect()->route('admin.devices.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, Device $device)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'outlet_id' => 'required|exists:outlets,id',
            // Status diubah oleh admin melalui form edit
        ]);

        try {
            DB::transaction(function () use ($request, $device) {
                $device->update([
                    'name'      => $request->name,
                    'outlet_id' => $request->outlet_id,
                    'status'    => $request->status,
                ]);
            });

            return redirect()->route('admin.devices.index')
                ->with('success', 'Device berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('admin.devices.index')
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(Device $device)
    {
        try {
            DB::transaction(function () use ($device) {
                $device->delete();
            });

            return redirect()->route('admin.devices.index')
                ->with('success', 'Device berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.devices.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Generate kode unik untuk device dengan format "DEV-XXXXXX"
     */
    private function generateUniqueCode()
    {
        do {
            $code = 'DEV-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        } while (Device::where('code', $code)->exists());

        return $code;
    }

}
