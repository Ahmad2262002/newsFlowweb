<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\User; 
use App\Mail\NewArticleNotification;
use Illuminate\Support\Facades\Mail;


class ArticleController extends Controller
{
    // Get all articles (admin and employees can view)
   public function index(Request $request)
{
    try {
        $articles = Article::with(['employee', 'categories'])
            ->when($request->status, fn($query) => $query->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $transformedArticles = $articles->getCollection()->map(function ($article) {
            return $this->transformArticle($article);
        });

        $articles->setCollection($transformedArticles);

        return response()->json($articles);
    } catch (\Exception $e) {
        Log::error('Failed to fetch articles', ['message' => $e->getMessage()]);
        return response()->json([
            'error' => 'Failed to fetch articles',
            'message' => $e->getMessage()
        ], 500);
    }
}

    // Create a new article (employees only)
    public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'source_name' => 'required|string',
            'published_date' => 'required|date',
            'author_name' => 'required|string',
            'status' => 'required|in:0,1,2',
            'article_photo' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,category_id',
        ]);

        $validated['employee_id'] = auth()->user()->employee->employee_id;

        if ($request->filled('article_photo')) {
            $base64Image = $request->article_photo;
            $imagePath = $this->handleBase64Image($base64Image);
            $validated['article_photo'] = $imagePath;
        }

        $article = Article::create($validated);
        
        if ($request->has('category_id')) {
            $article->categories()->attach($request->category_id);
        }
        
        $article->load('categories');
        
        // Only send notifications if status is 1 (published)
        if ($article->status == 1) {
            $users = User::with('staff')
                ->whereHas('staff', function($query) {
                    $query->whereNotNull('email');
                })
                ->when(config('app.env') === 'production', function($query) {
                    $query->where('wants_notifications', true);
                })
                ->get();

            foreach ($users as $user) {
                try {
                    Mail::to($user->staff->email)
                        ->queue(new NewArticleNotification($article, $user));
                } catch (\Exception $e) {
                    Log::error("Failed to queue notification for user {$user->user_id}", [
                        'error' => $e->getMessage(),
                        'article_id' => $article->article_id,
                        'email' => $user->staff->email ?? 'no email'
                    ]);
                }
            }
        }

        return response()->json($article, 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Article creation failed', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'error' => 'Article creation failed',
            'message' => $e->getMessage()
        ], 500);
    }
}



    // Get a single article (admin and employees can view)
    public function show($id)
    {
        $article = Article::with('employee','categories')->findOrFail($id);
        return response()->json($article);
    }

    // Update an article (employees only)
    public function update(Request $request, $id)
{
    try {
        $article = Article::findOrFail($id);

        $validated = $request->validate([
            'title' => 'string|max:255',
            'content' => 'string',
            'source_name' => 'string',
            'published_date' => 'date',
            'author_name' => 'string',
            'status' => 'in:0,1,2',
            'article_photo' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,category_id',
        ]);

        if ($request->filled('article_photo')) {
            $base64Image = $request->article_photo;
            $imagePath = $this->handleBase64Image($base64Image);
            $validated['article_photo'] = $imagePath;
            
            if ($article->article_photo) {
                Storage::delete($article->article_photo);
            }
        }

        $article->update($validated);

        // Sync the category if provided
        if ($request->has('category_id')) {
            $article->categories()->sync([$request->category_id]);
        } else {
            // If no category_id is provided, detach all categories
            $article->categories()->detach();
        }
        
        // Load the category relationship for the response
        $article->load('categories');

        return response()->json($article);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Article update failed', ['message' => $e->getMessage()]);
        return response()->json([
            'error' => 'Article update failed',
            'message' => $e->getMessage()
        ], 500);
    }
}
    // Publish an article (admin only)
    public function publish($id)
{
    $article = Article::findOrFail($id);
    $article->update(['status' => 1]); // Publish
    
    // Send notifications after publishing
    $users = User::with('staff')
        ->whereHas('staff', function($query) {
            $query->whereNotNull('email');
        })
        ->when(config('app.env') === 'production', function($query) {
            $query->where('wants_notifications', true);
        })
        ->get();

    foreach ($users as $user) {
        try {
            Mail::to($user->staff->email)
                ->queue(new NewArticleNotification($article, $user));
        } catch (\Exception $e) {
            Log::error("Failed to queue notification for user {$user->user_id}", [
                'error' => $e->getMessage(),
                'article_id' => $article->article_id,
                'email' => $user->staff->email ?? 'no email'
            ]);
        }
    }

    return response()->json($article);
}
    // Unpublish an article (admin only)
    public function unpublish($id)
    {
        $article = Article::findOrFail($id);
        $article->update(['status' => 0]); // Unpublish
        return response()->json($article);
    }

    // Fetch shares of an article
    public function shares($id)
    {
        // Ensure the article exists
        $article = Article::findOrFail($id);

        // Fetch the shares for the article
        $shares = $article->shares;

        return response()->json($shares);
    }

    protected function handleBase64Image(string $base64Image): string
    {
        // Extract base64 string
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $extension = strtolower($type[1]); // jpg, png, gif, etc.
            $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
        } else {
            throw new \Exception('Invalid base64 image string format.');
        }

        $imageData = base64_decode($base64Image);

        if ($imageData === false) {
            throw new \Exception('Base64 decode failed.');
        }

        // Save file
        $filename = uniqid() . '.' . $extension;
        $path = 'article_photos/' . $filename;
        
        Storage::disk('public')->put($path, $imageData);

        Log::info('Uploaded new article photo:', ['path' => $path]);

        return $path;
    }

    protected function transformArticle($article)
{
    $articleData = $article->toArray();
    
    if (!empty($article->article_photo)) {
        $articleData['image_url'] = $this->getImageUrl($article->article_photo);
    } else {
        $articleData['image_url'] = null;
    }
    
    // Include categories in the transformed data
    $articleData['categories'] = $article->categories;

    return $articleData;
}

    /**
     * Generate full URL for stored images
     */
    protected function getImageUrl($path)
    {
        // Check if the path is already a URL
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Generate URL for locally stored files
        return Storage::url($path);
    }

/**
 * Get articles by category (for users)
 */
/**
 * Get articles by category (for users)
 */
/**
 * Get articles by category (for users)
 */
public function getArticlesByCategory($category_id)
{
    try {
        // Validate that the category exists
        $category = Category::withCount('articles')->find($category_id);
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        // Get paginated articles
        $articles = Article::whereHas('categories', function ($query) use ($category_id) {
                $query->where('categories.category_id', $category_id);
            })
            ->with(['employee'])
            ->where('status', 1) // Only published articles
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Transform the articles collection
        $transformedCollection = $articles->getCollection()->map(function ($article) {
            return $this->transformArticle($article);
        });

        $articles->setCollection($transformedCollection);

        // Get the pagination data as an array
        $response = $articles->toArray();
        
        // Add the category information
        $response['category'] = [
            'category_id' => $category->category_id,
            'name' => $category->name,
            'description' => $category->description,
            // Add other category fields if needed
        ];

        return response()->json($response);

    } catch (\Exception $e) {
        Log::error('Failed to fetch articles by category', [
            'category_id' => $category_id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => 'Failed to fetch articles by category',
            'message' => env('APP_DEBUG') ? $e->getMessage() : 'Internal server error'
        ], 500);
    }
}}
