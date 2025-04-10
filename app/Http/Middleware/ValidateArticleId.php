<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateArticleId
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->has('article_id')) {
            return response()->json([
                'message' => 'Article ID is required',
                'errors' => ['article_id' => ['The article id field is required.']]
            ], 422);
        }

        return $next($request);
    }
}