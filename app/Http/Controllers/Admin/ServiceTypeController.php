<?php

namespace App\Http\Controllers\Admin;

use App\Models\ServiceType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ServiceTypeController extends Controller
{
    public function index()
    {
        try {
            $serviceTypes = ServiceType::all();

            return view('admin.service_types.index', compact('serviceTypes'));
        } catch (\Exception $e) {
            return redirect()->route('admin.service_types.index')
                ->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        try {
            return view('admin.service_types.create');
        } catch (\Exception $e) {
            return redirect()->route('admin.service_types.index')
                ->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:service_types,name',
        ]);

        try {
            ServiceType::create($request->only('name'));
            return redirect()->route('admin.service_types.index')
                ->with('success', 'Service Type berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('admin.service_types.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(ServiceType $serviceType)
    {
        try {
            return view('admin.service_types.edit', compact('serviceType'));
        } catch (\Exception $e) {
            return redirect()->route('admin.service_types.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, ServiceType $serviceType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:service_types,name,' . $serviceType->id,
        ]);

        try {
            $serviceType->update($request->only('name'));
            return redirect()->route('admin.service_types.index')
                ->with('success', 'Service Type berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('admin.service_types.index')
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(ServiceType $serviceType)
    {
        try {
            $serviceType->delete();
            return redirect()->route('admin.service_types.index')
                ->with('success', 'Service Type berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.service_types.index')
                ->with('error', $e->getMessage());
        }
    }
}