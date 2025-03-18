<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Share;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    // Share an article
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'article_id' => 'required|exists:articles,article_id',
        ]);

        $share = Share::create($validated);
        return response()->json($share, 201);
    }
}