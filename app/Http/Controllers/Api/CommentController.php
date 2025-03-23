<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Import the Log facade

class CommentController extends Controller
{
    // Add a comment
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'content' => 'required|string',
            'article_id' => 'required|exists:articles,article_id',
        ]);

        // Get the authenticated staff
        $staff = Auth::user();

        // Retrieve the associated user
        $user = User::where('staff_id', $staff->staff_id)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User profile not found',
            ], 404);
        }

        // Debugging: Log the authenticated user
        Log::info('Authenticated User ID: ' . $user->user_id);

        // Create the comment
        $comment = Comment::create([
            'content' => $validated['content'],
            'user_id' => $user->user_id, // Use the authenticated user's ID
            'article_id' => $validated['article_id'],
        ]);

        return response()->json($comment, 201);
    }

    // Delete a comment
    public function destroy($id)
    {
        // Get the authenticated staff
        $staff = Auth::user();

        // Retrieve the associated user
        $user = User::where('staff_id', $staff->staff_id)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User profile not found',
            ], 404);
        }

        // Find the comment
        $comment = Comment::where('comment_id', $id)
            ->where('user_id', $user->user_id) // Ensure the comment belongs to the authenticated user
            ->first();

        if (!$comment) {
            return response()->json([
                'message' => 'Comment not found or you do not have permission to delete it',
            ], 404);
        }

        // Delete the comment
        $comment->delete();

        return response()->json(null, 204); // 204 No Content for successful deletion
    }
}