<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Domicile;
use Illuminate\Support\Facades\Storage;

class DomicileController extends Controller
{
    public function index()
    {
        try {
            $domiciles = Domicile::all();
            return view('dashboard.domiciles.index', compact('domiciles'));
        } catch (\Exception $e) {
            return redirect()->route('domiciles.index')->with('error', $e);
        }
    }

    public function create()
    {
        return view('dashboard.domiciles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'city' => 'required|unique:domiciles,city',
        ]);

        try {
            Domicile::create([
                'city' => $request->city,
            ]);
            return redirect()->route('domiciles.index')->with('success', 'Domicile created successfully');
        } catch (\Exception $e) {
            return redirect()->route('domiciles.index')->with('error', $e);
        }
    }

    public function edit(Domicile $domicile)
    {
        return view('dashboard.domiciles.edit', compact('domicile'));
    }

    public function update(Request $request, Domicile $domicile)
    {
        $request->validate([
            'city' => 'required|unique:domiciles,city,' . $domicile->id,
        ]);

        try {
            $domicile->update([
                'city' => $request->city,
            ]);
            return redirect()->route('domiciles.index')->with('success', 'Domicile updated successfully');
        } catch (\Exception $e) {
            return redirect()->route('domiciles.index')->with('error', $e);
        }
    }

    public function destroy(Domicile $domicile)
    {
        try {
            $domicile->delete();
            return redirect()->route('domiciles.index')->with('success', 'Domicile deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('domiciles.index')->with('error', $e);
        }
    }
}
