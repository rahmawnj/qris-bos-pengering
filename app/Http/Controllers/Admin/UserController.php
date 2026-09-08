<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::where('role', 'admin')->get();
            return view('admin.users.index', compact('users'));
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')->with('error', 'Failed to load users');
        }
    }

    public function create()
    {
        try {
            return view('admin.users.create');
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')->with('error', 'Failed to load create user form');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                  => 'required',
            'email'                 => 'required|email|unique:users',
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'admin',
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $path = $image->store('public/images/users');
                $imageUrl = Storage::url($path);
                $user->image = $imageUrl;
                $user->save();
            }

            return redirect()->route('admin.users.index')->with('success', 'User created successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')->with('error', 'Failed to create user');
        }
    }

    public function edit(User $user)
    {
        try {
            return view('admin.users.edit', compact('user'));
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')->with('error', 'Failed to load edit user form');
        }
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'                  => 'required',
            'email'                 => 'required|email|unique:users,email,' . $user->id,
            'password'              => 'nullable|min:6|confirmed',
            'password_confirmation' => 'nullable',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $data = [
                'name'  => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $path = $image->store('public/images/users');

                if ($user->image) {
                    $imageName = basename($user->image);
                    Storage::delete('public/images/users/' . $imageName);
                }

                $imageUrl = Storage::url($path);
                $user->image = $imageUrl;
                $user->save();
            }

            return redirect()->route('admin.users.index')->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')->with('error', 'Failed to update user');
        }
    }

    public function destroy(User $user)
    {
        try {
            if ($user->image) {
                $imageName = basename($user->image);
                Storage::delete('public/images/users/' . $imageName);
            }

            $user->delete();

            return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')->with('error', 'Failed to delete user');
        }
    }

    public function impersonate(Request $request, User $user)
    {
        if (!Auth::guard('admin_config')->check()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Akses ditolak. Hanya admin_config yang boleh impersonate.');
        }

        $request->session()->put('impersonating', true);
        $request->session()->put('impersonated_user_id', $user->id);

        Auth::guard('web')->login($user);

        return redirect()->route('home')
            ->with('success', 'Berhasil login sebagai: ' . $user->name);
    }
}
