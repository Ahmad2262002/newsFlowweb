<?php

// app/Http/Controllers/API/AuthController.php path
namespace App\Http\Controllers\Api; 

use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:staffs'],
            'email' => ['required', 'email', 'unique:staffs'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
    
        try {
            $staff = Staff::create([
                'role_id' => 4, // Auto-assign role_id for the users by default
                'username' => $request->username,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'is_locked' => false // Account is unlocked by default

            ]);

            // Create Public User Profile
            $user = User::create([
                'staff_id' => $staff->staff_id,
                'preferences' => '{}', // Default empty 
            ]);

            $token = $staff->createToken('auth-token')->plainTextToken;

            return response()->json([
                'staff' => $staff,
                'user_profile' => $user,
                'token' => $token
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

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

        // Revoke existing tokens if exists
        $staff->tokens()->delete();

        $token = $staff->createToken('auth-token')->plainTextToken;

        return response()->json([
            'staff' => $staff,
            'user_profile' => $staff->publicProfile,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}