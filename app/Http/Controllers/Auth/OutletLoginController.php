<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Outlet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class OutletLoginController extends Controller
{
    /**
     * Redirect outlet setelah login berhasil.
     *
     * @var string
     */
    protected $redirectTo = '/partner/dashboard';

    /**
     * Tampilkan halaman login outlet.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.outlet-login');
    }

    /**
     * Proses login outlet.
     *
     * Input: username dan password.
     * Proses:
     *   - Cari record Outlet berdasarkan username.
     *   - Jika ditemukan, ambil email dari owner (melalui relasi owner → user).
     *   - Gunakan email tersebut sebagai credential untuk login dengan guard default (web).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $request->validate([
            'pin' => 'required|array|min:6',
            'pin.*' => 'numeric|digits:1',
            'password' => 'required|string',
        ]);

        // Gabungkan pin menjadi satu string
        $pin = implode('', $request->input('pin'));

        // Cari outlet berdasarkan username (pin)
        $outlet = Outlet::where('username', $pin)->first();

        // Cek apakah outlet ditemukan dan password cocok
        if (!$outlet || !Hash::check($request->input('password'), $outlet->password)) {
            return back()->withErrors([
                'pin' => 'PIN atau password salah.',
            ])->withInput();
        }

        // Login outlet
        Auth::guard('outlet')->login($outlet, $request->filled('remember'));
        $request->session()->regenerate();

        return redirect()->intended('partner/dashboard');
    }


    public function login2(Request $request)
    {
        // Validasi input
        $request->validate([
            'pin' => 'required',
            'password' => 'required|string',
        ]);

        $pin = implode('', $request->input('pin'));
        $outlet = Outlet::where('username', $pin)->first();
        if (!$outlet) {
            return back()->withErrors([
                'username' => 'These credentials do not match our records...',
            ])->withInput($request->only('username'));
        }
        if (!$outlet->owner || !$outlet->owner->user) {
            return back()->withErrors([
                'username' => 'Owner email not found for this outlet.',
            ])->withInput($request->only('username'));
        }
        $ownerEmail = $outlet->owner->user->email;
        $user = User::where('email', $ownerEmail)->first();
        $user['outlet'] = $outlet;
        if ($user) {
            Auth::login($user, $request->filled('remember'));
            $request->session()->regenerate();
            session([
                'role'   => 'outlet',
                'outlet' => $user,
            ]);
            return redirect()->intended($this->redirectTo);
        }

        return back()->withErrors([
            'username' => 'These credentials do not match our records.',
        ])->withInput($request->only('username'));
    }

    /**
     * Proses logout.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('outlet.login');
    }
}