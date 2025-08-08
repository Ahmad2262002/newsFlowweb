<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AdminActionLogger;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\Employee;
use App\Models\AdminAction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\User;
use App\Models\Article;
use App\Models\Category;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('staff')->paginate(10);
        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('admin.employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:staffs'],
            'email' => ['required', 'email', 'unique:staffs'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'position' => ['required', 'string'],
            'hire_date' => ['required', 'date'],
        ]);

        $staff = Staff::create([
            'role_id' => 3,
            'username' => $request->username,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'is_locked' => false,
        ]);

        $employee = Employee::create([
            'staff_id' => $staff->staff_id,
            'department' => $request->department,
            'position' => $request->position,
            'hire_date' => $request->hire_date,
        ]);

        User::firstOrCreate(
            ['staff_id' => $staff->staff_id],
            ['preferences' => '{}', 'profile_picture' => null]
        );

        AdminActionLogger::log('add_employee', 'Added new employee: ' . $staff->username, 
            auth()->user()->staff_id);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee added successfully');
    }

    public function show($id)
    {
        $employee = Employee::with('staff')->findOrFail($id);
        return view('admin.employees.show', compact('employee'));
    }

    public function edit($id)
    {
        $employee = Employee::with('staff')->findOrFail($id);
        return view('admin.employees.edit', compact('employee'));
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $staff = $employee->staff;

        $request->validate([
            'username' => ['string', 'max:255', 'unique:staffs,username,' . $staff->staff_id . ',staff_id'],
            'email' => ['email', 'unique:staffs,email,' . $staff->staff_id . ',staff_id'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'department' => ['string'],
            'position' => ['string'],
            'hire_date' => ['date'],
        ]);

        $staffData = [
            'username' => $request->username ?? $staff->username,
            'email' => $request->email ?? $staff->email,
        ];

        if ($request->password) {
            $staffData['password_hash'] = Hash::make($request->password);
        }

        $staff->update($staffData);

        $employee->update([
            'department' => $request->department ?? $employee->department,
            'position' => $request->position ?? $employee->position,
            'hire_date' => $request->hire_date ?? $employee->hire_date,
        ]);

        AdminActionLogger::log('update_employee', 'Updated employee: ' . $staff->username, 
            auth()->user()->staff_id);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee updated successfully');
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $staff = $employee->staff;
    
        AdminActionLogger::log('delete_employee', 'Deleted employee: ' . $staff->username, 
            auth()->user()->staff_id);
    
        AdminAction::where('target_staff_id', $staff->staff_id)->update(['target_staff_id' => null]);
    
        $employee->forceDelete();
        $staff->forceDelete();
    
        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee deleted successfully');
    }

    public function dashboard()
    {
        $employeeId = auth()->user()->employee->employee_id;
        
        $articles = Article::where('employee_id', $employeeId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $publishedCount = Article::where('employee_id', $employeeId)
            ->where('status', 1)
            ->count();

        $draftCount = Article::where('employee_id', $employeeId)
            ->where('status', 0)
            ->count();

        return view('employee.dashboard', [
            'articles' => $articles,
            'publishedCount' => $publishedCount,
            'draftCount' => $draftCount
        ]);
    }

   // Example for EmployeeController
public function data(Request $request)
{
    $search = $request->input('search');
    
    $employees = Employee::with(['staff', 'staff.role'])
        ->when($search, function($query, $search) {
            return $query->whereHas('staff', function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        })
        ->orderBy('hire_date', 'desc')
        ->paginate(10);

    return response()->json([
        'data' => $employees->items(),
        'links' => $employees->links()->toHtml(),
        'total' => $employees->total(),
        'current_page' => $employees->currentPage(),
        'stats' => [
            'employees' => Employee::count(),
            'articles' => Article::count(),
            'categories' => Category::count()
        ]
    ]);
}
}