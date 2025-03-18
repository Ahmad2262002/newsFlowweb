<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Add a comment
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'user_id' => 'required|exists:users,user_id',
            'article_id' => 'required|exists:articles,article_id',
        ]);

        $comment = Comment::create($validated);
        return response()->json($comment, 201);
    }

    // Delete a comment
    public function destroy($id)
    {
        Comment::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}