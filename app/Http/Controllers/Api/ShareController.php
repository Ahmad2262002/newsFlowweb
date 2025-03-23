<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Share;
use App\Models\Article;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    // Share an article
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'article_id' => 'required|exists:articles,article_id',
        ]);

        // Create the share record
        $share = Share::create($validated);

        // Generate a shareable URL
        $shareableUrl = url("/api/user/articles/{$share->article_id}/shares/{$share->share_id}");

        // Return the share record and the shareable URL
        return response()->json([
            'share' => $share,
            'shareable_url' => $shareableUrl,
        ], 201);
    }

    // Fetch shares for an article
    public function shares($id)
    {
        $article = Article::findOrFail($id);
        $shares = $article->shares; // Ensure the `shares` relationship is defined in the Article model
        return response()->json($shares);
    }
}