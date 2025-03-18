<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployeeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the authenticated user is an employee (role_id = 3)
        if (auth()->user()->role_id !== 3) {
            abort(403, 'Unauthorized: Only employees can access this route.');
        }
        return $next($request);
    }
}
