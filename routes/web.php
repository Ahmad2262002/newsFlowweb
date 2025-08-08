<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Models\User;
use App\Models\Employee;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication Routes
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // Registration
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'registerAdmin'])->name('register.post');

    // Password Reset
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

    // OTP Verification
    Route::get('/verify-otp', [AuthController::class, 'showVerifyOtpForm'])->name('verify.otp');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('verify.otp.post');
});

// OTP Test Route (can be removed in production)
Route::get('/send-otp', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'otp' => 'required|numeric'
    ]);

    try {
        $email = $request->input('email');
        $otp = $request->input('otp');

        Mail::to($email)->send(new OtpMail($otp));

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully to ' . $email
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'config' => config('mail')
        ], 500);
    }
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard Routes
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();

        if ($user->role_id == 1 || $user->role_id == 2) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role_id == 3) {
            return redirect()->route('employee.dashboard');
        }

        return redirect('/');
    })->name('dashboard');
    Route::get('/employee/check-session', function () {
        return response()->json(['authenticated' => true]);
    })->name('employee.checkSession');

    // Admin Routes
    Route::prefix('admin')->middleware(['admin'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        // Action Logs
        Route::get('/action-logs', [AdminController::class, 'actionLogs'])->name('admin.action-logs.index');

        // Employee Management
        Route::resource('employees', EmployeeController::class)
            ->names([
                'index' => 'admin.employees.index',
                'create' => 'admin.employees.create',
                'store' => 'admin.employees.store',
                'show' => 'admin.employees.show',
                'edit' => 'admin.employees.edit',
                'update' => 'admin.employees.update',
                'destroy' => 'admin.employees.destroy'
            ]);

        // Article Management
        Route::resource('articles', ArticleController::class)
            ->names([
                'index' => 'admin.articles.index',
                'create' => 'admin.articles.create',
                'store' => 'admin.articles.store',
                'show' => 'admin.articles.show',
                'edit' => 'admin.articles.edit',
                'update' => 'admin.articles.update',
                // 'destroy' => 'admin.articles.destroy'
            ])
            ->except(['show']); // Exclude the default show route


        // Add custom show route that uses showAdminArticle
        Route::get('articles/{article}', [ArticleController::class, 'showAdminArticle'])
            ->name('admin.articles.show');

        Route::post('/articles/{id}/publish', [ArticleController::class, 'publish'])->name('admin.articles.publish');
        Route::post('/articles/{id}/unpublish', [ArticleController::class, 'unpublish'])->name('admin.articles.unpublish');

        // Category Management
        Route::resource('categories', CategoryController::class)
            ->names([
                'index' => 'admin.categories.index',
                'create' => 'admin.categories.create',
                'store' => 'admin.categories.store',
                'show' => 'admin.categories.show',
                'edit' => 'admin.categories.edit',
                'update' => 'admin.categories.update',
                'destroy' => 'admin.categories.destroy'
            ]);
    });


    // Employee Routes
    Route::prefix('employee')->middleware(['employee'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [EmployeeController::class, 'dashboard'])->name('employee.dashboard');

        // Article Management
        Route::resource('articles', ArticleController::class)
            ->names([
                'index' => 'employee.articles.index',
                'create' => 'employee.articles.create',
                'store' => 'employee.articles.store',
                'show' => 'employee.articles.show',
                'edit' => 'employee.articles.edit',
                'update' => 'employee.articles.update',
                'destroy' => 'employee.articles.destroy'
            ]);

        // Categories (read-only)
        Route::get('/categories', [CategoryController::class, 'index'])->name('employee.categories.index');
        Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('employee.categories.show');
    });
});

// Admin data routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    // Employees data
    Route::get('/employees/data', [EmployeeController::class, 'data'])->name('admin.employees.data');

    // Articles data
    Route::get('/articles/data', [ArticleController::class, 'data'])->name('admin.articles.data');

    // Categories data
    Route::get('/categories/data', [CategoryController::class, 'data'])->name('admin.categories.data');

    // Action logs data
    Route::get('/action-logs/data', [AdminController::class, 'actionLogsData'])->name('admin.action-logs.data');
});

// API Routes
Route::prefix('api')->group(function () {
    // Public routes
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{id}', [ArticleController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        // Admin-only routes
        Route::middleware('admin')->group(function () {
            Route::apiResource('/admin/employees', EmployeeController::class);
            Route::apiResource('/admin/articles', ArticleController::class);
            Route::apiResource('/admin/categories', CategoryController::class);
        });

        // Employee-only routes
        Route::middleware('employee')->group(function () {
            Route::get('/employee/articles/my-articles', [ArticleController::class, 'EmployeeArticles']);
            Route::apiResource('/employee/articles', ArticleController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
            Route::get('/employee/categories', [CategoryController::class, 'index']);
            Route::get('/employee/categories/{id}', [CategoryController::class, 'show']);
        });

        // User-only routes
        Route::middleware('user')->group(function () {
            Route::get('/user/articles/categories/{id}', [ArticleController::class, 'getArticlesByCategory']);
            Route::get('/user/categories', [CategoryController::class, 'index']);
        });
    });
});
