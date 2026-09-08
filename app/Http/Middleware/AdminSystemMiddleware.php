<?php

namespace App\Http\Middleware;

use Closure;

class AdminSystemMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!session('admin_system')) {
            abort(403, 'ADMIN SYSTEM ONLY');
        }

        return $next($request);
    }
}
