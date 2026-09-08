<?php

namespace App\Http\Controllers\Partner;

use App\Models\Addon;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AddonController extends Controller
{
    public function index()
    {
        $feature = getData();
        if (!$feature->can('partner.addons.index')) {
            abort(403, 'Anda tidak memiliki izin.');
        }
        $addons = getData()->addons->get();

        return view('partner.addons.index', compact('addons'));
    }

    public function create()
    {
        $feature = getData();
        if (!$feature->can('partner.addons.create')) {
            abort(403, 'Anda tidak memiliki izin.');
        }

        $outlets = getData()->outlets->get();
        $existingCategories = Addon::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();
        return view('partner.addons.create', compact('outlets', 'existingCategories'));
    }

    public function store(Request $request)
    {
        $feature = getData();
        if (!$feature->can('partner.addons.store')) {
            abort(403, 'Anda tidak memiliki izin.');
        }
        $request->validate([
            'outlet_id'   => 'required|exists:outlets,id',
            'name'        => [
                'required',
                'string',
                'max:255',
                Rule::unique('addons')->where(function ($query) use ($request) {
                    return $query->where('outlet_id', $request->outlet_id);
                }),
            ],
            'category'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'is_active'   => 'boolean',
        ]);

        // try {
        DB::transaction(function () use ($request) {
            Addon::create([
                'outlet_id'   => $request->outlet_id,
                'name'        => $request->name,
                'category'    => $request->category,
                'description' => $request->description,
                'price'       => $request->price,
                'is_active'   => $request->has('is_active'),
            ]);
        });

        return redirect()->route('partner.addons.index')->with('success', 'Add-on berhasil ditambahkan!');
        // } catch (\Exception $e) {
        //     Log::error('Error storing addon (partner): ' . $e->getMessage());
        //     return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan add-on.');
        // }
    }

    public function edit(Addon $addon)
    {
        $feature = getData();
        if (!$feature->can('partner.addons.edit')) {
            abort(403, 'Anda tidak memiliki izin.');
        }

        $addons = getData()->addons->get();
        $outlets = getData()->outlets->get();
        $existingCategories = Addon::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();
        return view('partner.addons.edit', compact('addon', 'outlets', 'existingCategories'));
    }


    public function update(Request $request, Addon $addon)
    {
        $feature = getData();
        if (!$feature->can('partner.addons.update')) {
            abort(403, 'Anda tidak memiliki izin.');
        }
        $request->validate([
            'outlet_id'   => 'required|exists:outlets,id',
            'name'        => [
                'required',
                'string',
                'max:255',
                Rule::unique('addons')->where(function ($query) use ($request) {
                    return $query->where('outlet_id', $request->outlet_id);
                })->ignore($addon->id),
            ],
            'category'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'is_active'   => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($request, $addon) {
                $addon->update([
                    'outlet_id'   => $request->outlet_id,
                    'name'        => $request->name,
                    'category'    => $request->category,
                    'description' => $request->description,
                    'price'       => $request->price,
                    'is_active'   => $request->has('is_active'),
                ]);
            });

            return redirect()->route('partner.addons.index')->with('success', 'Add-on berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Error updating addon (partner): ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui add-on.');
        }
    }

    public function destroy(Addon $addon)
    {
        $feature = getData();
        if (!$feature->can('partner.addons.destroy')) {
            abort(403, 'Anda tidak memiliki izin.');
        }
        try {
            DB::transaction(function () use ($addon) {
                $addon->delete();
            });

            return redirect()->route('partner.addons.index')->with('success', 'Add-on berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting addon (partner): ' . $e->getMessage());
            return redirect()->route('partner.addons.index')->with('error', 'Gagal menghapus add-on.');
        }
    }
}