<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon; // Import the Addon model
use App\Models\Outlet; // Import Outlet model for dropdowns
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule; // Import Rule class for unique validation

class AddonController extends Controller
{
    /**
     * Display a listing of the addons.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        try {
            $addons = Addon::with('outlet')->get(); // Eager load outlet data
            return view('admin.addons.index', compact('addons'));
        } catch (\Exception $e) {
            Log::error('Error fetching addons for admin index: ' . $e->getMessage());
            return redirect()->route('admin.addons.index')
                ->with('error', 'Terjadi kesalahan saat memuat daftar add-on: ' . $e->getMessage());
        }
    }

     public function create()
    {
        $outlets = Outlet::all(); // Get all outlets for the dropdown (as already present)

        // Mengambil semua kategori unik dari semua add-on di database
        $existingCategories = Addon::whereNotNull('category') // Hanya ambil yang punya kategori
                                   ->distinct() // Ambil nilai unik
                                   ->pluck('category') // Ambil hanya kolom 'category'
                                   ->toArray(); // Konversi ke array PHP

        return view('admin.addons.create', compact('outlets', 'existingCategories'));
    }
    /**
     * Store a newly created addon in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'outlet_id'   => 'required|exists:outlets,id',
            'name'        => [
                'required',
                'string',
                'max:255',
                // Ensure 'name' is unique within the selected 'outlet_id'
                Rule::unique('addons')->where(function ($query) use ($request) {
                    return $query->where('outlet_id', $request->outlet_id);
                }),
            ],
            'category'    => 'nullable|string|max:255', // Validation for category
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'is_active'   => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($request) {
                Addon::create([
                    'outlet_id'   => $request->outlet_id,
                    'name'        => $request->name,
                    'category'    => $request->category, // Store category
                    'description' => $request->description,
                    'price'       => $request->price,
                    'is_active'   => $request->has('is_active'),
                ]);
            });

            return redirect()->route('admin.addons.index')
                ->with('success', 'Add-on berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error storing new addon: ' . $e->getMessage());
            return redirect()->route('admin.addons.create')
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menambahkan add-on: ' . $e->getMessage());
        }
    }

   public function edit(Addon $addon)
    {
        $outlets = Outlet::all();

        $existingCategories = Addon::whereNotNull('category') // Hanya ambil yang punya kategori
                                   ->distinct() // Ambil nilai unik
                                   ->pluck('category') // Ambil hanya kolom 'category'
                                   ->toArray(); // Konversi ke array PHP

        return view('admin.addons.edit', compact('addon', 'outlets', 'existingCategories'));
    }

    /**
     * Update the specified addon in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Addon  $addon
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Addon $addon)
    {
        $request->validate([
            'outlet_id'   => 'required|exists:outlets,id',
            'name'        => [
                'required',
                'string',
                'max:255',
                // Ensure 'name' is unique within the selected 'outlet_id', excluding the current addon
                Rule::unique('addons')->where(function ($query) use ($request) {
                    return $query->where('outlet_id', $request->outlet_id);
                })->ignore($addon->id),
            ],
            'category'    => 'nullable|string|max:255', // Validation for category
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'is_active'   => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($request, $addon) {
                $addon->update([
                    'outlet_id'   => $request->outlet_id,
                    'name'        => $request->name,
                    'category'    => $request->category, // Update category
                    'description' => $request->description,
                    'price'       => $request->price,
                    'is_active'   => $request->has('is_active'),
                ]);
            });

            return redirect()->route('admin.addons.index')
                ->with('success', 'Add-on berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Error updating addon ' . $addon->id . ': ' . $e->getMessage());
            return redirect()->route('admin.addons.edit', $addon->id)
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui add-on: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified addon from storage.
     *
     * @param  \App\Models\Addon  $addon
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Addon $addon)
    {
        try {
            DB::transaction(function () use ($addon) {
                $addon->delete();
            });

            return redirect()->route('admin.addons.index')
                ->with('success', 'Add-on berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting addon ' . $addon->id . ': ' . $e->getMessage());
            return redirect()->route('admin.addons.index')
                ->with('error', 'Terjadi kesalahan saat menghapus add-on: ' . $e->getMessage());
        }
    }
}