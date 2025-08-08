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
        $this->middleware('auth:sanctum');
        $this->middleware('admin')->except(['index', 'show']);
    }

    /**
     * Unified response handler for both web and API
     */
    protected function respond($data, $view = null, $redirectRoute = null)
    {
        // API response
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json($data);
        }

        // Web response
        if ($view) {
            return view($view, $data);
        }

        if ($redirectRoute) {
            return redirect()->route($redirectRoute)->with($data);
        }

        return redirect()->back()->with($data);
    }

    /**
     * Get all categories (works for both API and web)
     */
    public function index()
    {
        try {
            // Use pagination for web, simple all() for API if preferred
            $categories = request()->wantsJson() 
                ? Category::all()
                : Category::paginate(10);

            $response = [
                'success' => true,
                'data' => $categories
            ];

            // Add pagination data for web
            if (!request()->wantsJson()) {
                $response['current_page'] = $categories->currentPage();
                $response['last_page'] = $categories->lastPage();
            }

            return $this->respond($response, 'admin.categories.index');

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Web-only: Show create form
     */
    public function create()
    {
        if (request()->wantsJson()) {
            abort(404);
        }
        return view('admin.categories.create');
    }

    /**
     * Store a new category (works for both)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
            'slug' => 'required|string|max:255|unique:categories',
        ]);

        if ($validator->fails()) {
            return $this->respond([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        $category = Category::create($validator->validated());
        
        return $this->respond([
            'success' => true,
            'data' => $category,
            'message' => 'Category created successfully'
        ], null, 'admin.categories.index');
    }

    /**
     * Show single category (works for both)
     */
    public function show($id)
    {
        $category = Category::findOrFail($id);
        
        return $this->respond([
            'success' => true,
            'data' => $category
        ], 'admin.categories.show');
    }

    /**
     * Web-only: Show edit form
     */
    public function edit($id)
    {
        if (request()->wantsJson()) {
            return $this->respond([
                'success' => true,
                'data' => Category::findOrFail($id)
            ]);
        }
        
        return view('admin.categories.edit', [
            'category' => Category::findOrFail($id)
        ]);
    }

    /**
     * Update category (works for both)
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:categories,name,'.$id.',category_id',
            'slug' => 'sometimes|string|max:255|unique:categories,slug,'.$id.',category_id',
        ]);

        if ($validator->fails()) {
            return $this->respond([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        $category->update($validator->validated());
        
        return $this->respond([
            'success' => true,
            'data' => $category,
            'message' => 'Category updated successfully'
        ], null, 'admin.categories.index');
    }

    /**
     * Delete category (works for both)
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->articles()->detach();
        $category->delete();
        
        return $this->respond([
            'success' => true,
            'message' => 'Category deleted successfully'
        ], null, 'admin.categories.index');
    }

    /**
     * API-only: Detach articles from category
     */
    public function detachArticles($id)
    {
        // Only available for API
        if (!request()->wantsJson()) {
            abort(404);
        }

        $category = Category::findOrFail($id);
        $category->articles()->detach();
        
        return response()->json([
            'success' => true,
            'message' => 'All articles have been detached from this category'
        ]);
    }
}