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
Route::post('/login', [AuthController::class, 'login']); // Login for admin and employees
// fetsh the share
Route::get('/user/articles/{id}/shares', [ArticleController::class, 'shares']);


// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Logout route (common for all roles)
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile management routes (common for all roles)
    Route::prefix('/profile')->group(function () {
        Route::put('/', [ProfileController::class, 'updateProfile']); // Edit profile
        Route::put('/password', [ProfileController::class, 'changePassword']); // Change password
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

        // Admin dashboard
        Route::get('/admin/dashboard', function () {
            return response()->json(['message' => 'Welcome to the Admin Dashboard']);
        });
    });

    // Employee-only routes
    Route::middleware('employee')->group(function () {

        // Article management
        Route::apiResource('/employee/articles', ArticleController::class)->except(['destroy']);
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
        // Like an article
        Route::post('/user/articles/likes', [LikeController::class, 'store']);

        // Unlike an article
        Route::delete('/user/articles/likes/{id}', [LikeController::class, 'destroy']);
        
        // Add a comment
        Route::post('/user/articles/comments', [CommentController::class, 'store']);

        // Delete a comment
        Route::delete('/user/articles/comments/{id}', [CommentController::class, 'destroy']);
        // Share an article
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
});
