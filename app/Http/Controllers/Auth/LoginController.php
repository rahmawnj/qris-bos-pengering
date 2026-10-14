<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{

    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Reset notifikasi QRIS billing setiap kali partner berhasil login.
     */
    protected function authenticated(Request $request, $user)
    {
        $request->session()->flash('reset_qris_billing_alerts', true);
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
