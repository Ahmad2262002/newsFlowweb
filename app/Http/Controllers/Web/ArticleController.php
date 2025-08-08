<?php

namespace App\Http\Controllers\Web;

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
        $articles = Article::with(['employee.staff', 'categories'])
            ->when($request->status, fn($query) => $query->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.articles.index', compact('articles'));
    }

    // Get all published articles (for public view)
    public function index(Request $request)
    {
        $articles = Article::with(['employee', 'categories'])
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('articles.index', compact('articles'));
    }

    // Show create form
    public function create()
    {
        $categories = Category::all();
        return view('admin.articles.create', compact('categories'));
    }

    // Store a new article
    public function store(Request $request)
    {
        $user = auth()->user();

        // Ensure user has employee record
        if (!$user->employee) {
            Employee::create([
                'staff_id' => $user->staff_id,
                'position' => 'Content Creator',
                'hire_date' => now(),
                'department' => 'Editorial'
            ]);
        }

        $validated = $this->validateArticleRequest($request);
        $validated['employee_id'] = $user->employee->employee_id;

        if ($request->filled('article_photo')) {
            $validated['article_photo'] = $this->handleBase64Image($request->article_photo);
        }

        $article = Article::create($validated);

        if ($request->has('category_id')) {
            $article->categories()->attach($request->category_id);
        }

        if ($article->status == 1) {
            $this->sendNotifications($article);
        }

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article created successfully');
    }

    // Show edit form
    public function edit($id)
    {
        $article = Article::with('categories')->findOrFail($id);
        $categories = Category::all();
        return view('admin.articles.edit', compact('article', 'categories'));
    }

    // Update an article
    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);
        $validated = $this->validateArticleRequest($request, true);

        if ($request->filled('article_photo')) {
            if ($article->article_photo) {
                Storage::disk('public')->delete($article->article_photo);
            }
            $validated['article_photo'] = $this->handleBase64Image($request->article_photo);
        }

        $article->update($validated);

        if ($request->has('category_id')) {
            $article->categories()->sync([$request->category_id]);
        }

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article updated successfully');
    }

    // Delete an article
    public function destroy($id)
    {
        $article = Article::findOrFail($id);

        if ($article->article_photo) {
            Storage::disk('public')->delete($article->article_photo);
        }

        $article->categories()->detach();
        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article deleted successfully');
    }

    // Publish an article
    public function publish($id)
    {
        $article = Article::findOrFail($id);
        $article->update(['status' => 1]);

        $this->sendNotifications($article);

        return redirect()->back()
            ->with('success', 'Article published successfully');
    }

    // Unpublish an article
    public function unpublish($id)
    {
        $article = Article::findOrFail($id);
        $article->update(['status' => 0]);

        return redirect()->back()
            ->with('success', 'Article unpublished successfully');
    }

    // Show single article (admin view)
    public function adminShow($id)
    {
        $article = Article::with(['employee.staff', 'categories'])->findOrFail($id);
        return view('admin.articles.show', compact('article'));
    }

    // Show single published article (public view)
    public function show($id)
    {
        $article = Article::with(['employee', 'categories'])
            ->where('status', 1)
            ->findOrFail($id);

        return view('articles.show', compact('article'));
    }

    // Get articles by category
    public function getArticlesByCategory($category_id)
    {
        $category = Category::withCount('articles')->findOrFail($category_id);

        $articles = Article::whereHas('categories', function ($query) use ($category_id) {
                $query->where('categories.category_id', $category_id);
            })
            ->with(['employee'])
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('articles.category', compact('articles', 'category'));
    }

    // Get employee articles
    public function getEmployeeArticles(Request $request)
    {
        $employeeId = auth()->user()->employee->employee_id;

        $articles = Article::with(['categories'])
            ->where('employee_id', $employeeId)
            ->when($request->status, function($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('employee.articles.index', compact('articles'));
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
        return $path;
    }

    // Send notifications for published articles
    protected function sendNotifications($article)
    {
        $users = User::with('staff')
            ->whereHas('staff', function ($query) {
                $query->whereNotNull('email');
            })
            ->when(config('app.env') === 'production', function ($query) {
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
                    'article_id' => $article->article_id
                ]);
            }
        }
    }

    public function data(Request $request)
{
    $articles = Article::with(['employee', 'employee.staff'])->paginate(10);
    return response()->json($articles);
}
}