<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password; // Import the Password rule

class ProfileController extends Controller
{
    // Update profile
    public function updateProfile(Request $request)
    {
        $staff = Auth::user(); // Get the authenticated staff member

        $request->validate([
            'username' => [
                'string',
                'max:255',
                'unique:staffs,username,' . $staff->staff_id . ',staff_id', // Specify the primary key column
            ],
            'email' => [
                'email',
                'unique:staffs,email,' . $staff->staff_id . ',staff_id', // Specify the primary key column
            ],
        ]);

        $staff->update([
            'username' => $request->username ?? $staff->username,
            'email' => $request->email ?? $staff->email,
        ]);

        return response()->json($staff);
    }

    // Change password
    public function changePassword(Request $request)
    {
        $staff = Auth::user(); // Get the authenticated staff member

        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()], // Use the Password rule
        ]);

        if (!Hash::check($request->current_password, $staff->password_hash)) {
            return response()->json(['error' => 'Current password is incorrect'], 401);
        }

        $staff->update(['password_hash' => Hash::make($request->password)]);
        return response()->json(['message' => 'Password updated successfully']);
    }
}