<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\User;
use App\Models\Employee;
use App\Models\AdminAction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminController extends Controller
{
    // View all users
    public function viewUsers()
    {
        $users = User::with('staff')->paginate(10);
        return response()->json($users);
    }

    // View all employees
    public function viewEmployees()
    {
        $employees = Employee::with('staff')->paginate(10);
        return response()->json($employees);
    }

    // Add an employee
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

        $staff = Staff::create([
            'role_id' => 3, // Employee role
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'is_locked' => false,
        ]);

        $employee = Employee::create([
            'staff_id' => $staff->staff_id,
            'department' => $validated['department'],
            'position' => $validated['position'],
            'hire_date' => $validated['hire_date'],
        ]);

        // Log admin action
        AdminAction::create([
            'admin_id' => auth()->user()->staff_id,
            'action_type' => 'add_employee',
            'description' => 'Added employee: ' . $staff->username,
        ]);

        return response()->json($employee, 201);
    }

    // Update an employee
    public function updateEmployee(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $validated = $request->validate([
            'department' => 'string',
            'position' => 'string',
            'hire_date' => 'date',
        ]);

        $employee->update($validated);
        return response()->json($employee);
    }

    // Delete an employee
    public function deleteEmployee($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        // Log admin action
        AdminAction::create([
            'admin_id' => auth()->user()->staff_id,
            'action_type' => 'delete_employee',
            'description' => 'Deleted employee: ' . $employee->staff->username,
        ]);

        return response()->json(null, 204);
    }

    // View admin actions log
    public function viewAdminActions()
    {
        $actions = AdminAction::with('admin')->paginate(10);
        return response()->json($actions);
    }
}
