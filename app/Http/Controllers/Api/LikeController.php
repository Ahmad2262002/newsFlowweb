<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\User;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LikeController extends Controller
{
    // Like an article
    public function store(Request $request)
    {
        // Check if the user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Get the authenticated staff
        $staff = auth()->user();

        // Retrieve the associated user
        $user = User::where('staff_id', $staff->staff_id)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User profile not found',
            ], 404);
        }

        // Validate the request
        $validated = $request->validate([
            'article_id' => 'required|exists:articles,article_id',
        ]);

        // Check if the user has already liked the article
        $existingLike = Like::where('user_id', $user->user_id)
            ->where('article_id', $validated['article_id'])
            ->first();

        if ($existingLike) {
            return response()->json([
                'success' => false,
                'message' => 'You have already liked this article',
            ], 400);
        }

        // Create a new like
        $like = Like::create([
            'user_id' => $user->user_id,
            'article_id' => $validated['article_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Article liked successfully',
            'data' => $like
        ], 201);
    }

    // Unlike an article
    public function destroy($id)
    {
        // Get the authenticated staff
        $staff = auth()->user();

        // Retrieve the associated user
        $user = User::where('staff_id', $staff->staff_id)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User profile not found',
            ], 404);
        }

        // Find the like record and ensure it belongs to the authenticated user
        $like = Like::where('like_id', $id)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$like) {
            return response()->json([
                'success' => false,
                'message' => 'Like not found or you do not have permission to delete it',
            ], 404);
        }

        $like->delete();

        return response()->json([
            'success' => true,
            'message' => 'Like removed successfully'
        ]);
    }
    public function getLikedArticles(Request $request)
{
    try {
        // Check authentication
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Get the authenticated staff
        $staff = auth()->user();

        // Retrieve the associated user
        $user = User::where('staff_id', $staff->staff_id)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User profile not found',
            ], 404);
        }

        // Get all liked articles with like_id for this user
        $likes = Like::where('user_id', $user->user_id)
            ->with(['article' => function($query) {
                $query->where('status', 1)
                    ->select('article_id', 'title', 'content');
            }])
            ->get(['like_id', 'article_id']);

        // Filter out articles that might be null (if status was 0)
        $articles = $likes->filter(function($like) {
            return $like->article !== null;
        })->map(function($like) {
            return [
                'id' => $like->article->article_id,
                'like_id' => $like->like_id,
                'title' => $like->article->title,
                'content' => $like->article->content
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $articles->values() // Reset keys to sequential numbers
        ], 200);
        
    } catch (\Exception $e) {
        // Log the error for debugging
        Log::error('Failed to fetch liked articles: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch liked articles: ' . $e->getMessage(),
            'error_details' => env('APP_DEBUG') ? $e->getTrace() : null
        ], 500);
    }
}
}