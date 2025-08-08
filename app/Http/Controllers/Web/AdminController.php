<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\User;
use App\Models\Employee;
use App\Models\AdminAction;
use App\Models\Article;
use App\Models\Category;
use App\Models\ActionLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function dashboard()
    {
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
    }

    public function actionLogs()
    {
        $actions = AdminAction::with(['admin', 'targetStaff'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.action-logs', compact('actions'));
    }

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

            return redirect()->back()
                ->with('success', "Created $fixedCount missing employee records");
        } catch (\Exception $e) {
            Log::error('Failed to fix employee records', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Failed to fix employee records');
        }
    }

    public function actionLogsData(Request $request)
{
    $dateFrom = $request->input('date_from');
    $dateTo = $request->input('date_to');
    
    $logs = AdminAction::with(['admin.staff'])
        ->when($dateFrom && $dateTo, function($query) use ($dateFrom, $dateTo) {
            return $query->whereBetween('action_date', [$dateFrom, $dateTo]);
        })
        ->orderBy('action_date', 'desc')
        ->paginate(10);

    return response()->json([
        'data' => $logs->items(),
        'links' => $logs->links()->toHtml(),
        'total' => $logs->total(),
        'current_page' => $logs->currentPage()
    ]);
}
}