<?php

namespace App\Http\Controllers\Partner;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function form(Request $request)
    {
        $currentPage = $request->query('page', 'profile');

        return view('partner.brand.edit-profile', compact('currentPage'));
    }

    public function submit(Request $request)
    {
        $owner = getBrand(); // Assuming getBrand() retrieves the authenticated owner/brand

        $page = $request->query('page', 'profile'); // Get the page from the query parameter

        if ($page === 'profile') {
            $rules = [
                'brand_name'    => ['required', 'string', 'max:255'],
                'brand_email'   => ['required', 'email', 'max:255', Rule::unique('owners')->ignore($owner->id)],
                'brand_phone'   => ['nullable', 'string', 'max:20'],
                'brand_address' => ['nullable', 'string', 'max:255'],
                'brand_logo'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            ];

            $request->validate($rules);

            $owner->brand_name  = $request->brand_name;
            $owner->brand_email = $request->brand_email;
            $owner->brand_phone = $request->brand_phone;
            $owner->address     = $request->brand_address;

            if ($request->hasFile('brand_logo')) {
                if ($owner->brand_logo && Storage::disk('public')->exists($owner->brand_logo)) {
                    Storage::disk('public')->delete($owner->brand_logo);
                }
                $path = $request->file('brand_logo')->store('brand_logos', 'public');
                $owner->brand_logo = $path;
            }

            $message = 'Informasi Brand berhasil diperbarui!';
        } elseif ($page === 'bank') {
            // Rules for Bank Information
            $rules = [
                'bank_name'             => ['nullable', 'string', 'max:255'],
                'bank_account_number'   => ['nullable', 'string', 'max:50'],
                'bank_account_holder_name' => ['nullable', 'string', 'max:255'],
            ];

            // Validate the request
            $request->validate($rules);

            // Update Bank Information
            $owner->bank_name               = $request->bank_name;
            $owner->bank_account_number     = $request->bank_account_number;
            $owner->bank_account_holder_name = $request->bank_account_holder_name;

            $message = 'Informasi Bank berhasil diperbarui!';
        } else {
            return redirect()->back()->with('error', 'Halaman tidak valid!');
        }

        $owner->save();

        // Redirect back to the correct tab after update
        return redirect()->route('partner.brand.profile.edit', ['page' => $page])->with('success', $message);
    }
}