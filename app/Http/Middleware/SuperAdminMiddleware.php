<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to access this area.');
        }

        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super admin access required.');
        }

        return $next($request);
    }
}
