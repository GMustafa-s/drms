<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictTenantAccess
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is logged in and has the 'Panel User' role
        if (auth()->check() && auth()->user()->hasRole('Panel User')) {
            // Restrict access to tenant-related areas
            abort(403, 'Access Denied. You do not have permission to access this area.');
        }

        return $next($request);
    }
}
