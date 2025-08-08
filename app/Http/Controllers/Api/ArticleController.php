<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Category;
use App\Models\Employee;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Mail\NewArticleNotification;
use Illuminate\Support\Facades\Mail;

class ArticleController extends Controller
{
    // Get all articles for admin interface
    public function adminIndex(Request $request)
    {
        try {
            $articles = Article::with(['employee.staff', 'categories'])
                ->when($request->status, fn($query) => $query->where('status', $request->status))
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $articles->getCollection()->transform(function ($article) {
                return $this->transformArticle($article);
            });

            return response()->json([
                'success' => true,
                'data' => $articles
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch articles', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch articles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get all published articles (for public API)
    public function index(Request $request)
    {
        try {
            $articles = Article::with(['employee', 'categories'])
                ->where('status', 1) // Only published articles
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $articles->getCollection()->transform(function ($article) {
                return $this->transformArticle($article);
            });

            return response()->json([
                'success' => true,
                'data' => $articles
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch articles', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch articles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Consolidated article management
    public function manage(Request $request)
    {
        $action = $request->input('action_type');
        $articleId = $request->input('article_id');

        switch ($action) {
            case 'add':
                return $this->store($request);
            case 'edit':
                return $this->update($request, $articleId);
            case 'delete':
                return $this->destroy($articleId);
            case 'publish':
                return $this->publish($articleId);
            case 'unpublish':
                return $this->unpublish($articleId);
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action'
                ], 400);
        }
    }




    // Create a new article
    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user->employee) {
                $employee = Employee::create([
                    'staff_id' => $user->staff_id,
                    'position' => 'Content Creator',
                    'hire_date' => now(),
                    'department' => 'Editorial'
                ]);
            } else {
                $employee = $user->employee;
            }

            $validated = $this->validateArticleRequest($request);
            $validated['employee_id'] = $employee->employee_id;

            if ($request->filled('article_photo')) {
                $validated['article_photo'] = $this->handleBase64Image($request->article_photo);
            }

            $article = Article::create($validated);

            // Ensure category_id exists before attaching
            if ($request->has('category_id') && Category::find($request->category_id)) {
                $article->categories()->attach($request->category_id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Article created successfully',
                'article' => $this->transformArticle($article->load('categories'))
            ], 201);
        } catch (\Exception $e) {
            Log::error('Article creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Article creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update an article
    // Update an article with all fields optional
    public function update(Request $request, $id)
    {
        try {

            $article = Article::findOrFail($id);
            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article not found'
                ], 404);
            }

            // Authorization check - ensure employee can only update their own articles
            if (auth()->user()->employee->employee_id !== $article->employee_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You can only update your own articles'
                ], 403);
            }

            // Validate with all fields optional
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'content' => 'sometimes|string',
                'source_name' => 'sometimes|string|max:255',
                'published_date' => 'sometimes|date',
                'author_name' => 'sometimes|string|max:255',
                'status' => 'sometimes|in:0,1,2',
                'article_photo' => 'sometimes|nullable|string',
                'category_id' => 'sometimes|exists:categories,category_id',
            ]);

            // Handle image update
            if ($request->has('article_photo')) {
                if ($request->filled('article_photo')) {
                    // New image provided - process it
                    $validated['article_photo'] = $this->handleBase64Image($request->article_photo);

                    // Delete old image if it exists
                    if ($article->article_photo) {
                        Storage::disk('public')->delete($article->article_photo);
                    }
                } else {
                    // Explicit empty string means remove the image
                    if ($article->article_photo) {
                        Storage::disk('public')->delete($article->article_photo);
                    }
                    $validated['article_photo'] = null;
                }
            }

            // Update only the provided fields
            $article->fill($validated)->save();

            // Update category relationship if provided
            if ($request->has('category_id')) {
                $article->categories()->sync([$request->category_id]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Article updated successfully',
                'article' => $this->transformArticle($article->load('categories'))
            ]);
        } catch (\Exception $e) {
            Log::error('Article update failed', [
                'article_id' => $id,
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            $errorMessage = 'Article update failed';
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                $errorMessage = 'Validation failed: ' . implode(' ', $e->validator->errors()->all());
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    // Delete an article
    // Change from private to public
    public function destroy($id)
    {
        try {
            $article = Article::findOrFail($id);

            // Add authorization check - ensure employee can only delete their own articles
            if (auth()->user()->employee->employee_id !== $article->employee_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You can only delete your own articles'
                ], 403);
            }

            if ($article->article_photo) {
                Storage::disk('public')->delete($article->article_photo);
            }

            $article->categories()->detach();
            $article->delete();

            return response()->json([
                'success' => true,
                'message' => 'Article deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Article deletion failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Article deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // Publish an article
    // Change from private to public
    public function publish($id)
    {
        try {
            $article = Article::findOrFail($id);
            $article->update(['status' => 1]);

            $this->sendNotifications($article);

            return response()->json([
                'success' => true,
                'message' => 'Article published successfully',
                'article' => $this->transformArticle($article)
            ]);
        } catch (\Exception $e) {
            Log::error('Article publish failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Article publish failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Change from private to public
    public function unpublish($id)
    {
        try {
            $article = Article::findOrFail($id);
            $article->update(['status' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Article unpublished successfully',
                'article' => $this->transformArticle($article)
            ]);
        } catch (\Exception $e) {
            Log::error('Article unpublish failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Article unpublish failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get a single article (admin view)
    public function adminShow($id)
    {
        try {
            $article = Article::with(['employee.staff', 'categories'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'article' => $this->transformArticle($article)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch article', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get a single published article (public view)
    public function show($id)
    {
        try {
            $article = Article::with(['employee', 'categories'])
                ->where('status', 1)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'article' => $this->transformArticle($article)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch article', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get article data for editing
    public function fetchForEdit($id)
    {
        try {
            $article = Article::with('categories')->findOrFail($id);
            return response()->json([
                'success' => true,
                'article' => $this->transformArticle($article)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch article for edit', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Fetch shares of an article
    public function shares($id)
    {
        try {
            $article = Article::findOrFail($id);
            $shares = $article->shares;

            return response()->json([
                'success' => true,
                'shares' => $shares
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch article shares', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch shares',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get articles by category
    public function getArticlesByCategory($category_id)
    {
        try {
            $category = Category::withCount('articles')->find($category_id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            $articles = Article::whereHas('categories', function ($query) use ($category_id) {
                $query->where('categories.category_id', $category_id);
            })
                ->with(['employee'])
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $articles->getCollection()->transform(function ($article) {
                return $this->transformArticle($article);
            });

            return response()->json([
                'success' => true,
                'data' => $articles,
                'category' => [
                    'category_id' => $category->category_id,
                    'name' => $category->name,
                    'description' => $category->description,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch articles by category', [
                'category_id' => $category_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch articles by category',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    // Validate article request
    protected function validateArticleRequest(Request $request, $isUpdate = false)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'source_name' => 'required|string',
            'published_date' => 'required|date',
            'author_name' => 'required|string',
            'status' => 'required|in:0,1,2',
            'article_photo' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,category_id',
        ];

        if ($isUpdate) {
            $rules = array_merge($rules, [
                'title' => 'sometimes|string|max:255',
                'content' => 'sometimes|string',
                'source_name' => 'sometimes|string',
                'published_date' => 'sometimes|date',
                'author_name' => 'sometimes|string',
                'status' => 'sometimes|in:0,1,2',
            ]);
        }

        return $request->validate($rules);
    }

    // Handle base64 image upload
    protected function handleBase64Image(string $base64Image): string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $extension = strtolower($type[1]);
            $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
        } else {
            throw new \Exception('Invalid base64 image string format.');
        }

        $imageData = base64_decode($base64Image);
        if ($imageData === false) {
            throw new \Exception('Base64 decode failed.');
        }

        $filename = uniqid() . '.' . $extension;
        $path = 'article_photos/' . $filename;

        Storage::disk('public')->put($path, $imageData);
        Log::info('Uploaded new article photo:', ['path' => $path]);

        return $path;
    }

    // Transform article data
    protected function transformArticle($article)
    {
        $articleData = $article->toArray();

        if (!empty($article->article_photo)) {
            $articleData['image_url'] = $this->getImageUrl($article->article_photo);
        } else {
            $articleData['image_url'] = null;
        }

        return $articleData;
    }

    // Generate full URL for stored images
    protected function getImageUrl($path)
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        return Storage::url($path);
    }

    // Send notifications for published articles
    protected function sendNotifications($article)
    {
        $users = User::with('staff')
            ->whereHas('staff', function ($query) {
                $query->whereNotNull('email');
            })
            ->when(config('app.env') === 'production', function ($query) {
                $query->where('wants_notifications', operator: true);
            })
            ->get();

        foreach ($users as $user) {
            try {
                Mail::to($user->staff->email)
                    ->queue(new NewArticleNotification($article, $user));
            } catch (\Exception $e) {
                Log::error("Failed to queue notification for user {$user->user_id}", [
                    'error' => $e->getMessage(),
                    'article_id' => $article->article_id
                ]);
            }
        }
    }

    // Show the form for creating a new article

    // Show the form for editing an article
    public function edit($id)
    {
        $article = Article::findOrFail($id);
        return view('admin.articles.edit', compact('article')); // Create this view file
    }

    // Display the specified article in admin panel
    public function showAdminArticle($id)
    {
        $article = Article::with(['employee.staff', 'categories'])->findOrFail($id);

        // Ensure $article is a model instance before accessing its properties
        if ($article && !($article instanceof \Illuminate\Database\Eloquent\Collection) && $article->article_photo) {
            $article->image_url = Storage::url($article->article_photo);
        }

        return view('admin.articles.show', compact('article'));
    }
    /**
     * Get articles created by the authenticated employee/by filtration
     */
    public function EmployeeArticles(Request $request)
    {
        try {
            $employeeId = auth()->user()->employee->employee_id;

            $articles = Article::with(['categories'])
                ->where('employee_id', $employeeId)
                ->when($request->status, function ($query) use ($request) {
                    return $query->where('status', $request->status);
                })
                ->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 10));

            $transformedArticles = $articles->getCollection()->map(function ($article) {
                return [
                    'id' => $article->article_id,
                    'title' => $article->title,
                    'content' => $article->content,
                    'source_name' => $article->source_name,
                    'published_date' => $article->published_date,
                    'author_name' => $article->author_name,
                    'status' => $article->status,
                    'image_url' => $this->getImageUrl($article->article_photo),
                    'created_at' => $article->created_at,
                    'categories' => $article->categories->map(function ($category) {
                        return [
                            'category_id' => $category->category_id, // Changed from id to category_id
                            'name' => $category->name
                        ];
                    })->toArray() // Ensure this is an array
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $transformedArticles,
                    'current_page' => $articles->currentPage(),
                    'last_page' => $articles->lastPage(),
                    'total' => $articles->total()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch employee articles', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch your articles',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // End of ArticleController


}
