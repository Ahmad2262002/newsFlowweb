<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
<<<<<<< HEAD
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;

use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ShareController;
use App\Http\Controllers\Api\FeedbackController;
=======
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ProfileController;
>>>>>>> 1be931d626dd7cf88e724812dbc324d208ca59ae

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

<<<<<<< HEAD
// Authentication Routes
// routes/api.php
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});
// Protected User Route
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Article Routes
Route::prefix('articles')->group(function () {
    Route::get('/', [ArticleController::class, 'index']);
    Route::post('/', [ArticleController::class, 'store']);
    Route::get('/{id}', [ArticleController::class, 'show']);
    Route::put('/{id}', [ArticleController::class, 'update']);
    Route::delete('/{id}', [ArticleController::class, 'destroy']);
});

Route::post('/likes', [LikeController::class, 'store']);
Route::delete('/likes/{id}', [LikeController::class, 'destroy']);

Route::post('/comments', [CommentController::class, 'store']);
Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

Route::post('/shares', [ShareController::class, 'store']);

Route::post('/feedbacks', [FeedbackController::class, 'store']);
=======
// Public routes (no authentication required)
Route::post('/register-user', [AuthController::class, 'registerUser']); // Register a user
Route::post('/register-admin', [AuthController::class, 'registerAdmin']); // Register an admin
Route::post('/login', [AuthController::class, 'login']); // Login for admin and employees

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
        // View articles
        Route::get('/user/articles', [ArticleController::class, 'index']); // List all articles
        Route::get('/user/articles/{id}', [ArticleController::class, 'show']); // View a single article

        // User dashboard
        Route::get('/user/dashboard', function () {
            return response()->json(['message' => 'Welcome to the User Dashboard']);
        });
    });
});
>>>>>>> 1be931d626dd7cf88e724812dbc324d208ca59ae
