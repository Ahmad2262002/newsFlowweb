<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function __construct()
    {
        // Use auth:sanctum instead of auth:api to match your existing setup
        $this->middleware('auth:sanctum');
        
        // Apply admin middleware to all methods except index and show
        $this->middleware('admin')->except(['index', 'show']);
    }

    // Get all categories (available to all authenticated users)
    public function index()
    {
        $categories = Category::paginate(10);
        return response()->json($categories);
    }

    // Create a new category (admin only - protected by admin middleware)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
            'slug' => 'required|string|max:255|unique:categories',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $category = Category::create($validator->validated());
        return response()->json($category, 201);
    }

    // Get a single category (available to all authenticated users)
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    // Update a category (admin only - protected by admin middleware)
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:categories,name,'.$id.',category_id',
            'slug' => 'sometimes|string|max:255|unique:categories,slug,'.$id.',category_id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $category->update($validator->validated());
        return response()->json($category);
    }

    // Delete a category (admin only - protected by admin middleware)
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
}