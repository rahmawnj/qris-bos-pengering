<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $userRole = null;

        // Cek session hardcoded admin lebih dulu.
        if (session('is_hardcoded_admin') && session()->has('admin_data.role')) {
            $userRole = session('admin_data.role');
        }

        // Jika bukan hardcoded admin, cek guard web.
        if (!$userRole && Auth::guard('web')->check()) {
            $userRole = Auth::guard('web')->user()->role;
        }

        // Fallback jika guard outlet tersedia dan login aktif.
        if (
            !$userRole &&
            array_key_exists('outlet', config('auth.guards', [])) &&
            Auth::guard('outlet')->check()
        ) {
            $userRole = 'outlet';
        }

        // Izinkan jika role cocok
        if ($userRole && in_array($userRole, $roles)) {
            return $next($request);
        }

        // Jika tidak login sama sekali
        if (!$userRole) {
            return redirect()->route('login');
        }

        abort(403, 'Akses Ditolak.');
    }
}
