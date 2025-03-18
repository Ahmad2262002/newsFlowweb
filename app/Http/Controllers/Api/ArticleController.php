<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
// Get all articles (with optional filters)
public function index(Request $request)
{
    $articles = Article::with('employee') // Include all employee data
        ->when($request->status, fn($query) => $query->where('status', $request->status))
        ->paginate(10);

    return response()->json($articles);
}


     /// for Creating a new article
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'source_name' => 'required|string',
            'published_date' => 'required|date',
            'author_name' => 'required|string',
            'status' => 'required|in:0,1,2', // 0 = draft, 1 = published, 2 = unpublished
            'employee_id' => 'required|exists:employees,employee_id'
        ]);

        $article = Article::create($validated);
        return response()->json($article, 201);
    }
   

    // to Get a single article
    public function show($id)
    {
        $article = Article::with('employee')->findOrFail($id);
        return response()->json($article);
    }

    //to  Update an article
    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);
        //"[]"
        $validated = $request->validate('[...]'); // Similar to store()

        $article->update($validated);
        return response()->json($article);
    }

//to Delete it 
public function destroy($id)
{
    Article::findOrFail($id)->delete();
    return response()->json(null, 204);
}
}
