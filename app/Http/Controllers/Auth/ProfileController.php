<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule; // Import untuk Rule::unique

class ProfileController extends Controller
{
    public function form()
    {
        return view('auth.profile');
    }

    public function submit(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'image'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // Max 2MB
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        if ($request->filled('password')) {
            $rules['current_password'] = ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('Password saat ini salah.');
                }
            }];
        }

        $request->validate($rules);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password') && Hash::check($request->current_password, $user->password)) {
            $user->password = Hash::make($request->password);
        } elseif ($request->filled('password') && !Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Password saat ini salah.'])->withInput();
        }


        if ($request->hasFile('image')) {
            $currentImagePath = str_replace(asset(''), '', $user->image); // Konversi URL ke path relatif
            if ($user->image && !str_contains($currentImagePath, 'default-user.png') && Storage::disk('public')->exists(str_replace('storage/', '', $currentImagePath))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $currentImagePath));
            }
            $path = $request->file('image')->store('images/users', 'public');
            $user->image = Storage::url($path); // Simpan URL publik
        }

        $user->save();

        return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
    }
}