<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\User;
use App\Models\Employee;
use App\Models\AdminAction;
use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Share;
use App\Models\Feedback;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class AdminController extends Controller
{
    // View all users
    public function viewUsers()
    {
        $users = User::with(['staff', 'employee'])->paginate(10);
        return response()->json($users);
    }

    // View all employees
    public function viewEmployees()
    {
        $employees = Employee::with('staff.user')->paginate(10);
        return response()->json($employees);
    }

    // Add an employee (now creates all necessary records)
    public function addEmployee(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:staffs',
            'email' => 'required|email|unique:staffs',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'department' => 'required|string',
            'position' => 'required|string',
            'hire_date' => 'required|date',
        ]);

        try {
            // Create staff record
            $staff = Staff::create([
                'role_id' => 3, // Employee role
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password_hash' => Hash::make($validated['password']),
                'is_locked' => false,
            ]);

            // Create employee record
            $employee = Employee::create([
                'staff_id' => $staff->staff_id,
                'department' => $validated['department'],
                'position' => $validated['position'],
                'hire_date' => $validated['hire_date'],
            ]);

            // Create user record
            $user = User::firstOrCreate(
                ['staff_id' => $staff->staff_id],
                ['preferences' => '{}']
            );

            // Log admin action
            AdminAction::create([
                'admin_id' => auth()->user()->staff_id,
                'action_type' => 'add_employee',
                'description' => 'Added employee: ' . $staff->username,
                'target_staff_id' => $staff->staff_id
            ]);

            return response()->json([
                'success' => true,
                'employee' => $employee->load('staff'),
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to add employee', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update an employee (enhanced with error handling)
    public function updateEmployee(Request $request, $id)
    {
        try {
            $employee = Employee::findOrFail($id);

            $validated = $request->validate([
                'department' => 'sometimes|string',
                'position' => 'sometimes|string',
                'hire_date' => 'sometimes|date',
            ]);

            $employee->update($validated);

            // Log admin action
            AdminAction::create([
                'admin_id' => auth()->user()->staff_id,
                'action_type' => 'update_employee',
                'description' => 'Updated employee: ' . $employee->staff->username,
                'target_staff_id' => $employee->staff_id
            ]);

            return response()->json([
                'success' => true,
                'employee' => $employee->load('staff')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update employee', [
                'employee_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete an employee and all related records
    // This method now handles all dependent records before deletion
    // including articles, admin actions, and user records
    // It also logs the deletion action
    // and handles any exceptions that may occur
    // This is a critical operation, so it uses transactions to ensure data integrity


public function deleteEmployee($id)
{
    DB::beginTransaction();

    try {
        // 1. Load the employee with staff relationship
        $employee = Employee::with('staff')->findOrFail($id);
        
        // 2. First delete all articles and their related records
        $articles = Article::where('employee_id', $employee->employee_id)->get();
        
        foreach ($articles as $article) {
            // Delete all related records for each article
            Comment::where('article_id', $article->article_id)->delete();
            Like::where('article_id', $article->article_id)->delete();
            Share::where('article_id', $article->article_id)->delete();
            Feedback::where('article_id', $article->article_id)->delete();
            
            // Detach categories
            DB::table('article_category')->where('article_id', $article->article_id)->delete();
            
            // Delete article photo if exists
            if ($article->article_photo) {
                Storage::disk('public')->delete($article->article_photo);
            }
            
            // Finally delete the article
            $article->delete();
        }
        
        // 3. Delete admin actions targeting this staff member
        AdminAction::where('target_staff_id', $employee->staff_id)->delete();
        
        // 4. Delete the user record
        User::where('staff_id', $employee->staff_id)->delete();
        
        // 5. Log the deletion action
        AdminAction::create([
            'admin_id' => auth()->id(),
            'action_type' => 'delete_employee',
            'description' => 'Deleted employee: ' . $employee->staff->username,
            'target_staff_id' => $employee->staff_id,
            'action_date' => now()
        ]);
        
        // 6. Delete the employee record
        $employee->delete();
        
        // 7. Finally delete the staff record
        $employee->staff->delete();
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Employee and all related data deleted successfully'
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        Log::error('Employee deletion failed', [
            'employee_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Deletion failed',
            'error' => $e->getMessage(),
            'details' => 'Make sure no articles or other relations exist for this employee'
        ], 500);
    }
}

    // View admin actions log
    public function viewAdminActions()
    {
        $actions = AdminAction::with(['admin', 'targetStaff'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($actions);
    }

    // Dashboard with all necessary data
    public function dashboard()
    {
        try {
            $employees = Employee::with('staff.user')
                ->orderBy('hire_date', 'desc')
                ->paginate(5, ['*'], 'employees');

            $articles = Article::with(['employee.staff', 'categories'])
                ->orderBy('created_at', 'desc')
                ->paginate(5, ['*'], 'articles');

            $categories = Category::withCount('articles')
                ->orderBy('name')
                ->paginate(5, ['*'], 'categories');

            $actions = AdminAction::with(['admin', 'targetStaff'])
                ->orderBy('created_at', 'desc')
                ->paginate(5, ['*'], 'actions');

            return view('admin.dashboard', compact('employees', 'articles', 'categories', 'actions'));
        } catch (\Exception $e) {
            Log::error('Admin dashboard error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to load dashboard');
        }
    }

    // Additional method to fix missing employee records
    public function fixMissingEmployeeRecords()
    {
        try {
            $fixedCount = 0;
            $staffWithRole3 = Staff::where('role_id', 3)
                ->whereDoesntHave('employee')
                ->get();

            foreach ($staffWithRole3 as $staff) {
                Employee::firstOrCreate(
                    ['staff_id' => $staff->staff_id],
                    [
                        'position' => 'Employee',
                        'department' => 'General',
                        'hire_date' => now()
                    ]
                );
                $fixedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Created $fixedCount missing employee records",
                'fixed_count' => $fixedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fix employee records', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fix employee records',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function actionLogs()
{
    // Load actions with the proper relationships
    $actions = AdminAction::with([
            'admin.staff', // Load admin through staff relationship
            'targetStaff'  // Load target staff
        ])
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    // For JSON response (API requests)
    if (request()->wantsJson()) {
        // Transform the data to include the admin username directly
        $transformedActions = $actions->getCollection()->map(function ($action) {
            return [
                'action_id' => $action->action_id,
                'action_type' => $action->action_type,
                'description' => $action->description,
                'created_at' => $action->created_at,
                'admin_username' => $action->admin->staff->username ?? 'System',
                'target_username' => $action->targetStaff->username ?? null,
                // Include the full admin object if needed elsewhere
                'admin' => $action->admin,
                'target_staff' => $action->targetStaff
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformedActions,
            'pagination' => [
                'total' => $actions->total(),
                'per_page' => $actions->perPage(),
                'current_page' => $actions->currentPage(),
                'last_page' => $actions->lastPage(),
                'from' => $actions->firstItem(),
                'to' => $actions->lastItem()
            ]
        ]);
    }

    // For web view response
    return view('admin.action-logs', [
        'actions' => $actions,
        'adminUsernames' => $actions->mapWithKeys(function ($action) {
            return [
                $action->action_id => $action->admin->staff->username ?? 'System'
            ];
        })
    ]);
}
public function adminForceDeleteArticle($id)
{
    DB::beginTransaction();
    
    try {
        $article = Article::findOrFail($id);
        $admin = auth()->user();

        // Verify admin role
        if (!in_array($admin->role_id, [1, 2])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins can force delete articles'
            ], 403);
        }

        // 1. Delete all related records for the article
        Comment::where('article_id', $article->article_id)->delete();
        Like::where('article_id', $article->article_id)->delete();
        Share::where('article_id', $article->article_id)->delete();
        Feedback::where('article_id', $article->article_id)->delete();
        
        // 2. Detach categories
        DB::table('article_category')->where('article_id', $article->article_id)->delete();
        
        // 3. Delete article photo if exists
        if ($article->article_photo) {
            Storage::disk('public')->delete($article->article_photo);
        }

        // 4. Finally delete the article
        $article->delete();

        // 5. Log admin action (using staff_id instead of admin_id)
        try {
            AdminAction::create([
                'admin_id' => $admin->staff_id,
                'action_type' => 'admin_force_delete_article',
                'description' => 'Admin force deleted article: ' . $article->title,
                'target_staff_id' => $article->employee->staff_id,
                'action_date' => now()
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to log admin action', [
                'error' => $e->getMessage(),
                'admin_id' => $admin->staff_id,
                'article_id' => $id
            ]);
            // Continue even if logging fails
        }

        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Article and all related data force deleted by admin successfully'
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Admin force delete failed', [
            'article_id' => $id,
            'admin_id' => auth()->id(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Article force deletion failed',
            'error' => $e->getMessage(),
            'details' => 'Check database constraints and relationships'
        ], 500);
    }
}

public function destroy($id)
{
    DB::beginTransaction();
    
    try {
        $article = Article::findOrFail($id);
        $user = auth()->user();

        // Authorization logic
        if ($user->role_id == 3) { // Employee
            if ($user->employee->employee_id !== $article->employee_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You can only delete your own articles'
                ], 403);
            }
        } elseif ($user->role_id != 1 && $user->role_id != 2) { // Not admin or editor
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Delete article photo if exists
        if ($article->article_photo) {
            Storage::disk('public')->delete($article->article_photo);
        }

        $article->categories()->detach();
        $article->delete();

        // Log admin action if deleted by admin
        if ($user->role_id == 1) {
            AdminAction::create([
                'admin_id' => $user->staff_id,
                'action_type' => 'delete_article',
                'description' => 'Deleted article: ' . $article->title,
                'target_staff_id' => $article->employee->staff_id,
                'action_date' => now()
            ]);
        }

        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Article deleted successfully'
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Article deletion failed',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
