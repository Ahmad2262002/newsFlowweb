<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;

class ArticleController extends Controller
{
    // Get all articles (admin and employees can view)
    public function index(Request $request)
    {
        $articles = Article::with('employee')
            ->when($request->status, fn($query) => $query->where('status', $request->status))
            ->paginate(10);

        return response()->json($articles);
    }

    // Create a new article (employees only)
    public function store(Request $request)
    {

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'source_name' => 'required|string',
            'published_date' => 'required|date',
            'author_name' => 'required|string',
            'status' => 'required|in:0,1,2', // 0 = draft, 1 = published, 2 = unpublished
        ]);
        //add employee_id
        $validated['employee_id'] = auth()->user()->employee->employee_id;
        $article = Article::create($validated);
        return response()->json($article, 201);
    }

    // Get a single article (admin and employees can view)
    public function show($id)
    {
        $article = Article::with('employee')->findOrFail($id);
        return response()->json($article);
    }

    // Update an article (employees only)
    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        $validated = $request->validate([
            'title' => 'string|max:255',
            'content' => 'string',
            'source_name' => 'string',
            'published_date' => 'date',
            'author_name' => 'string',
            'status' => 'in:0,1,2',
        ]);

        $article->update($validated);
        return response()->json($article);
    }

    // Publish an article (admin only)
    public function publish($id)
    {
        $article = Article::findOrFail($id);
        $article->update(['status' => 1]); // Publish
        return response()->json($article);
    }

    // Unpublish an article (admin only)
    public function unpublish($id)
    {
        $article = Article::findOrFail($id);
        $article->update(['status' => 0]); // Unpublish
        return response()->json($article);
    }
}