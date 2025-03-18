<?php

// app/Http/Controllers/Api/EmployeeController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminActionLogger;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\Employee;
use App\Models\AdminAction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class EmployeeController extends Controller
{
    // Add an employee (admin only)
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
            'role_id' => 3, // Employee role
            'username' => $request->username,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'is_locked' => false,
        ]);

        // Create employee profile
        $employee = Employee::create([
            'staff_id' => $staff->staff_id,
            'department' => $request->department,
            'position' => $request->position,
            'hire_date' => $request->hire_date,
        ]);

        AdminActionLogger::log('add_employee', 'Added new employee: ' . $staff->username, $staff->staff_id);

        return response()->json([
            'staff' => $staff,
            'employee_profile' => $employee,
        ], 201);
    }

    // Edit an employee (admin only)
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $staff = $employee->staff;

        $request->validate([
            'username' => ['string', 'max:255', 'unique:staffs,username,' . $staff->staff_id . ',staff_id'],
            'email' => ['email', 'unique:staffs,email,' . $staff->staff_id . ',staff_id'],
            'position' => ['string'],
            'hire_date' => ['date'],
        ]);

        $staff->update([
            'username' => $request->username ?? $staff->username,
            'email' => $request->email ?? $staff->email,
        ]);

        $employee->update([
            'position' => $request->position ?? $employee->position,
            'hire_date' => $request->hire_date ?? $employee->hire_date,
        ]);

        AdminActionLogger::log('update_employee', 'Updated employee: ' . $staff->username, $staff->staff_id);

        return response()->json([
            'employee_profile' => $employee,
        ]);
    }

    // Delete an employee (admin only)
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $staff = $employee->staff;
    
        // Log the admin action before deleting the employee
        AdminActionLogger::log('delete_employee', 'Deleted employee: ' . $staff->username, $staff->staff_id);
    
        // Set target_staff_id to null in related admin actions
        AdminAction::where('target_staff_id', $staff->staff_id)->update(['target_staff_id' => null]);
    
        // Force delete the employee and related staff record
        $employee->forceDelete();
        $staff->forceDelete();
    
        return response()->json([
            'message' => 'Employee deleted successfully',
        ]);
    }
}
