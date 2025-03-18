<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    // Submit feedback
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'user_id' => 'required|exists:users,user_id',
            'article_id' => 'required|exists:articles,article_id',
        ]);

        $feedback = Feedback::create($validated);
        return response()->json($feedback, 201);
    }
}