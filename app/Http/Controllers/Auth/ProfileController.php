<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function form()
    {
        $isConfigAdmin = Auth::guard('admin_config')->check();
        $user = Auth::guard('admin_config')->user() ?? Auth::guard('web')->user();

        abort_unless($user, 401);

        return view('auth.profile', compact('user', 'isConfigAdmin'));
    }

    public function submit(Request $request)
    {
        // Akun super admin berasal dari konfigurasi, bukan tabel users.
        if (Auth::guard('admin_config')->check()) {
            return redirect()->route('profile.form')
                ->with('error', 'Profil Super Admin dikelola melalui konfigurasi aplikasi.');
        }

        $user = Auth::guard('web')->user();
        abort_unless($user, 401);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        if ($request->filled('password')) {
            $rules['current_password'] = [
                'required',
                function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('Password saat ini salah.');
                    }
                },
            ];
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('image')) {
            $oldImage = $user->image;

            if ($oldImage) {
                $oldPath = str_replace('/storage/', '', parse_url($oldImage, PHP_URL_PATH) ?? $oldImage);

                if ($oldPath && !str_contains($oldPath, 'default-user.png')) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $path = $request->file('image')->store('images/users', 'public');
            $user->image = Storage::url($path);
        }

        $user->save();

        return redirect()->route('profile.form')->with('success', 'Profil berhasil diperbarui!');
    }
}
