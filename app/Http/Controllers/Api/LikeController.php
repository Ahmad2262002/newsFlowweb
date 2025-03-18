<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    // Like an article
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'article_id' => 'required|exists:articles,article_id',
        ]);

        $like = Like::create($validated);
        return response()->json($like, 201);
    }

    // Unlike an article
    public function destroy($id)
    {
        Like::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}