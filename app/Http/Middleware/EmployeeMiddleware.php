<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployeeMiddleware
{
    public function handle(Request $request, Closure $next): Response
{
    if (auth()->check() && auth()->user()->role_id == 3) {
        // Check if staff has an employee record
        if (!auth()->user()->employee) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found'
                ], 403);
            }
            return redirect('/dashboard')->with('error', 'Employee record not found');
        }
        
        return $next($request);
    }

    if ($request->wantsJson()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized access - Employee only'
        ], 403);
    }

    return redirect('/dashboard')->with('error', 'Unauthorized access');
}
}