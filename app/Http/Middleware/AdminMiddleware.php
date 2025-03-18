<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the authenticated user is an admin (role_id = 2)
        if (auth()->user()->role_id !== 2) {
            abort(403, 'Unauthorized: Only admins can access this route.');
        }
        return $next($request);
    }
}