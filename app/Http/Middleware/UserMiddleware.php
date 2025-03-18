<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the authenticated user is a regular user (role_id = 4)
        if (auth()->user()->role_id !== 4) {
            abort(403, 'Unauthorized: Only users can access this route.');
        }
        return $next($request);
    }
}