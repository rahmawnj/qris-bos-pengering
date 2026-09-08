<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('admin_config')->check()) {
        return $next($request);
    }

    if (Auth::check() && Auth::user()->role === 'admin') {
        return $next($request);
    }
        // Jika dua-duanya gagal, tendang ke login
        return redirect()->route('login')->with('error', 'Anda tidak memiliki akses.');
    }
}
