<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Like;
use App\Models\Comment;
use Illuminate\Http\Request;

class InteractionCountController extends Controller
{
    /**
     * Get counts for an article
     */
    public function getCounts($articleId)
    {
        // Verify article exists
        if (!Article::where('article_id', $articleId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found'
            ], 404);
        }

        $likeCount = Like::where('article_id', $articleId)->count();
        $commentCount = Comment::where('article_id', $articleId)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'article_id' => (int)$articleId,
                'like_count' => $likeCount,
                'comment_count' => $commentCount
            ],
            'message' => 'Counts retrieved successfully'
        ]);
    }
}