<?php

namespace App\Http\Controllers\Api;

use App\Models\Staff;
use App\Models\User;
use App\Models\Admin;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    // Register a regular user (role_id = 4)
    public function registerUser(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:staffs'],
            'email' => ['required', 'email', 'unique:staffs'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            // Create the staff member
            $staff = Staff::create([
                'role_id' => 4, // User role
                'username' => $request->username,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'is_locked' => false // Account is unlocked by default
            ]);

            // Create the user profile
            $user = User::create([
                'staff_id' => $staff->staff_id,
                'preferences' => '{}', // Default empty preferences
                'profile_picture' => null // Default profile image
            ]);

            // Generate a token for the newly registered user
            $token = $staff->createToken('auth-token')->plainTextToken;

            return response()->json([
                'staff' => $staff,
                'user_profile' => $user,
                'token' => $token
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'User registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // Register an admin (role_id = 2)
    public function registerAdmin(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:staffs'],
            'email' => ['required', 'email', 'unique:staffs'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'access_level' => ['required', 'string'], // Admin-specific attribute
        ]);

        try {
            // Create the staff member
            $staff = Staff::create([
                'role_id' => 2, // Admin role
                'username' => $request->username,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'is_locked' => false // Account is unlocked by default
            ]);

            // Create the admin profile
            $admin = Admin::create([
                'staff_id' => $staff->staff_id,
                'access_level' => $request->access_level,
            ]);

            // Generate a token for the newly registered admin
            $token = $staff->createToken('auth-token')->plainTextToken;

            return response()->json([
                'staff' => $staff,
                'admin_profile' => $admin,
                'token' => $token
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Admin registration failed: ' . $e->getMessage()
            ], 500);
        }
    }



    // Login function (common for all roles)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $staff = Staff::where('email', $request->email)->first();

        if (!$staff || !Hash::check($request->password, $staff->password_hash)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Revoke existing tokens if they exist
        $staff->tokens()->delete();

        // Create a new token
        $token = $staff->createToken('auth-token')->plainTextToken;

        // Fetch the user profile using the staff_id
        $user = User::where('staff_id', $staff->staff_id)->first();

        if (!$user) {
            return response()->json(['error' => 'User profile not found'], 404);
        }

        return response()->json([
            'staff' => $staff,
            'user_profile' => $user, // Include the user profile in the response
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
