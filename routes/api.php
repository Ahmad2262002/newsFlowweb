<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ShareController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\InteractionCountController;
use Illuminate\Support\Facades\Mail;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::post('/register-user', [AuthController::class, 'registerUser']); // Register a user
Route::post('/register-admin', [AuthController::class, 'registerAdmin']); // Register an admin
Route::post('/verify-otp', [AuthController::class, 'verifyOtpAndRegister']); // Add this line
Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::post('/login', [AuthController::class, 'login']); // Login for admin, users  and employees
// fetsh the share
Route::get('/user/articles/{id}/shares', [ArticleController::class, 'shares']);


// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Logout route (common for all roles)
    Route::post('/logout', [AuthController::class, 'logout']);

    // Account deletion route (common for all roles)
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);

    // Profile management routes (common for all roles)
    Route::prefix('/profile')->group(function () {
        Route::put('/', [ProfileController::class, 'updateProfile'])->middleware('auth:sanctum');
        Route::put('/password', [ProfileController::class, 'changePassword']); // Change password
    });

    // TEST ROUTE - Notification testing
    Route::get('/test-notification', function() {
        $article = App\Models\Article::first();
        $user = App\Models\User::with('staff')->first();
        
        if (!$article || !$user) {
            return response()->json([
                'error' => 'Test data not found',
                'message' => 'Please ensure you have at least one article and user in the database'
            ], 404);
        }
        
        try {
            Mail::to($user->staff->email)
                ->queue(new App\Mail\NewArticleNotification($article, $user));
            
            return response()->json([
                'message' => "Notification queued for {$user->staff->email}",
                'article_id' => $article->article_id,
                'user_id' => $user->user_id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Notification failed',
                'message' => $e->getMessage()
            ], 500);
        }
    });

    // Admin-only routes
    Route::middleware('admin')->group(function () {
        // Employee management
        Route::apiResource('/admin/employees', EmployeeController::class)->except(['index', 'show']);
        //update employee
        Route::put('/admin/employees/{id}', [EmployeeController::class, 'update']);
        //delete employee
        Route::delete('/admin/employees/{id}', [EmployeeController::class, 'destroy']);

        // Article management
        Route::get('/admin/articles', [ArticleController::class, 'index']); // View all articles
        Route::get('/admin/articles/{id}', [ArticleController::class, 'show']); // View a single article
        Route::post('/admin/articles/{id}/publish', [ArticleController::class, 'publish']); // Publish article
        Route::post('/admin/articles/{id}/unpublish', [ArticleController::class, 'unpublish']); // Unpublish article

        // Categories management
        Route::apiResource('/admin/categories', \App\Http\Controllers\Api\CategoryController::class);

        

        // Admin dashboard
        Route::get('/admin/dashboard', function () {
            return response()->json(['message' => 'Welcome to the Admin Dashboard']);
        });
    });

    // Employee-only routes
    Route::middleware('employee')->group(function () {

        // Article management
        Route::apiResource('/employee/articles', controller: ArticleController::class)->except(['destroy']);
        //update article
        Route::put('/employee/articles/{id}', [ArticleController::class, 'update']);
        //delete article
        Route::delete('/employee/articles/{id}', [ArticleController::class, 'destroy']); // Delete article



        // Employee dashboard
        Route::get('/employee/dashboard', function () {
            return response()->json(['message' => 'Welcome to the Employee Dashboard']);
        });
    });

    // User-only routes
    Route::middleware('user')->group(function () {
        Route::get('/user/articles/categories/{id}', [ArticleController::class, 'getArticlesByCategory']);


        // View categories (read-only)
        Route::get('/user/categories', [\App\Http\Controllers\Api\CategoryController::class, 'index']);
        Route::get('/user/categories/{id}', [\App\Http\Controllers\Api\CategoryController::class, 'show']);
        // Like an article
        Route::post('/user/articles/likes', [LikeController::class, 'store']);

        // Unlike an article
        Route::delete('/user/articles/likes/{id}', [LikeController::class, 'destroy']);

        Route::get('/user/articles/liked-articles', [LikeController::class, 'getLikedArticles']);
        
        Route::get('/user/articles/{article}/comments', [CommentController::class, 'index']); // RESTful style
        Route::post('/user/articles/{article}/comments', [CommentController::class, 'store']); // POST comment
        Route::delete('/user/articles/comments/{comment}', [CommentController::class, 'destroy']); // DELETE comment

        Route::get('/user/articles/{article}/counts', [InteractionCountController::class, 'getCounts']);


        Route::post('/user/articles/{id}/shares', [ShareController::class, 'store']);

        // Submit feedback
        Route::post('/user/articles/{id}/feedbacks', [FeedbackController::class, 'store']);
        // View articles
        Route::get('/user/articles', [ArticleController::class, 'index']); // List all articles
        Route::get('/user/articles/{id}', [ArticleController::class, 'show']); // View a single article

        // User dashboard
        Route::get('/user/dashboard', function () {
            return response()->json(['message' => 'Welcome to the User Dashboard']);
        });
    });

    

    Route::get('/send-otp', function() {
        try {
            Mail::to('test@example.com')->send(new \App\Mail\OtpMail(123456));
            return 'Email sent successfully!';
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'mail_config' => config('mail')
            ], 500);
        }
    });
});
