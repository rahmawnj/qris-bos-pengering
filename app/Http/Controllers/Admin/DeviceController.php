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
        $devices = Device::with(['outlet.owner'])->latest('id')->get();
        $serviceTypes = ServiceType::orderBy('name')->get(['id', 'name']);

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
