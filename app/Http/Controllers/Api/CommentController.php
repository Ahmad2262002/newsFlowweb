<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    /**
     * Get all comments for an article
     */
    public function index(Request $request, Article $article = null)
    {
        // Handle both route patterns
        $articleId = $article ? $article->article_id : $request->input('article_id');
        
        if (!$articleId) {
            return response()->json([
                'success' => false,
                'message' => 'Article ID is required',
                'errors' => ['article_id' => ['The article id field is required.']]
            ], 422);
        }

        // Verify article exists (only needed for query parameter approach)
        if (!$article && !Article::where('article_id', $articleId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found'
            ], 404);
        }

        // Get comments with user and staff data
        $comments = Comment::with(['user.staff' => function($query) {
                $query->select('staff_id', 'username');
            }])
            ->where('article_id', $articleId)
            ->latest()
            ->get();

        // Transform the comments to include username
        $transformedComments = $comments->map(function ($comment) {
            return [
                'comment_id' => $comment->comment_id,
                'content' => $comment->content,
                'user_id' => $comment->user_id,
                'article_id' => $comment->article_id,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
                'user' => [
                    'user_id' => $comment->user_id,
                    'username' => $comment->user->staff->username ?? 'Unknown User'
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformedComments,
            'message' => 'Comments retrieved successfully'
        ]);
    }

    /**
     * Store a newly created comment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'article_id' => 'required|exists:articles,article_id',
        ]);

        $staff = Auth::user();
        $user = User::where('staff_id', $staff->staff_id)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User profile not found',
            ], 404);
        }

        Log::info('Comment created by user', [
            'user_id' => $user->user_id,
            'article_id' => $validated['article_id']
        ]);

        $comment = Comment::create([
            'content' => $validated['content'],
            'user_id' => $user->user_id,
            'article_id' => $validated['article_id'],
        ]);

        // Load user and staff data
        $comment->load(['user.staff']);

        return response()->json([
            'success' => true,
            'data' => [
                'comment' => [
                    'comment_id' => $comment->comment_id,
                    'content' => $comment->content,
                    'user_id' => $comment->user_id,
                    'article_id' => $comment->article_id,
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->updated_at,
                    'user' => [
                        'user_id' => $comment->user_id,
                        'username' => $comment->user->staff->username ?? 'Unknown User'
                    ]
                ]
            ],
            'message' => 'Comment created successfully'
        ], 201);
    }

    /**
     * Delete the specified comment
     */
    public function destroy($id)
    {
        $staff = Auth::user();
        $user = User::where('staff_id', $staff->staff_id)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User profile not found',
            ], 404);
        }

        $comment = Comment::where('comment_id', $id)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found or you do not have permission to delete it',
            ], 404);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }
}