<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\User;
use App\Models\Article;
use Illuminate\Http\Request;

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
}