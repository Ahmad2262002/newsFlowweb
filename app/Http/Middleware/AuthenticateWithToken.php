<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateWithToken
{
    public function handle(Request $request, Closure $next)
    {
        // Check for API token first
        if ($token = $request->bearerToken() ?? $request->token) {
            if (Auth::guard('sanctum')->check()) {
                return $next($request);
            }
        }

        // Fall back to web authentication
        if (Auth::guard('web')->check()) {
            return $next($request);
        }

        return redirect()->route('login');
    }
}