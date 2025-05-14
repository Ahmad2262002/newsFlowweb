<?php

namespace App\Http\Controllers\Api;

use App\Models\Staff;
use App\Models\User;
use App\Models\Admin;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    // Register a regular user (role_id = 4)
    public function registerUser(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:staffs'],
        ]);
    
        try {
            $email = $request->email;
            $domain = Str::afterLast($email, '@');
            
            $isGmail = in_array(strtolower($domain), ['gmail.com', 'googlemail.com']);
            if (!$isGmail) {
                return response()->json(['error' => 'Please register with Google'], 400);
            }
    
            $otp = mt_rand(100000, 999999);
            
            // Store only OTP (not registration data)
            cache()->put('otp_'.$email, $otp, now()->addMinutes(10));
            
            // Send email with better error handling
            try {
                Mail::to($email)->send(new OtpMail($otp));
                return response()->json(['message' => 'OTP sent'], 200);
            } catch (\Exception $mailException) {
                \Log::error('Mail sending failed: ' . $mailException->getMessage());
                return response()->json([
                    'error' => 'Failed to send OTP',
                    'details' => $mailException->getMessage()
                ], 500);
            }
            
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Registration failed',
                'details' => $e->getMessage()
            ], 500);
        }
    }
public function verifyOtpAndRegister(Request $request)
{
    $request->validate([
        'email' => ['required', 'email', 'unique:staffs'],
        'otp' => ['required', 'numeric'],
        'username' => ['required', 'string', 'max:255', 'unique:staffs'],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    try {
        $email = $request->email;
        $otp = $request->otp;
        
        $storedOtp = cache()->get('otp_'.$email);
        
        if (!$storedOtp) {
            return response()->json(['error' => 'OTP expired'], 400);
        }

        if ($storedOtp != $otp) {
            return response()->json(['error' => 'Invalid OTP'], 400);
        }

        // Create user
        $staff = Staff::create([
            'role_id' => 4,
            'username' => $request->username,
            'email' => $email,
            'password_hash' => Hash::make($request->password),
            'is_locked' => false
        ]);

        User::create([
            'staff_id' => $staff->staff_id,
            'preferences' => '{}',
            'profile_picture' => null
        ]);

        cache()->forget('otp_'.$email);

        $token = $staff->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'user' => $staff,
            'token' => $token
        ], 201);

    } catch (\Exception $e) {
        return response()->json(['error' => 'Registration failed'], 500);
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

    // Add this method to your AuthController
public function deleteAccount(Request $request)
{
    try {
        $staff = $request->user(); // Get the authenticated user
        
        // Get the associated user record
        $user = User::where('staff_id', $staff->staff_id)->first();
        
        if (!$user) {
            return response()->json(['error' => 'User profile not found'], 404);
        }
        
        // Start transaction to ensure data consistency
        \DB::beginTransaction();
        
        try {
            // Delete all related data
            \DB::table('likes')->where('user_id', $user->user_id)->delete();
            \DB::table('comments')->where('user_id', $user->user_id)->delete();
            \DB::table('shares')->where('user_id', $user->user_id)->delete();
            \DB::table('feedbacks')->where('user_id', $user->user_id)->delete();
            
            // Delete the user profile
            $user->delete();
            
            // Delete the staff account
            $staff->delete();
            
            // Commit the transaction
            \DB::commit();
            
            return response()->json(['message' => 'Account and all related data deleted successfully']);
            
        } catch (\Exception $e) {
            // Rollback transaction on error
            \DB::rollBack();
            \Log::error('Account deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Account deletion failed'], 500);
        }
        
    } catch (\Exception $e) {
        \Log::error('Account deletion error: ' . $e->getMessage());
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
    public function sendResetLink(Request $request)
{
    $request->validate(['email' => 'required|email']);

    $staff = Staff::where('email', $request->email)->first();
    if (!$staff) {
        return response()->json(['message' => 'If registered, you will receive an email']);
    }

    $token = Str::random(60);
    Cache::put('password_reset_' . $staff->email, $token, now()->addHour());

    // For local testing, use a deep link that opens your Flutter app
    $resetUrl = "newsflow://reset-password?token=$token&email=" . urlencode($staff->email);

    // For web testing (optional), use:
    // $resetUrl = "http://localhost:8000/reset-password?token=$token&email=" . urlencode($staff->email);

    Mail::to($staff->email)->send(new PasswordResetMail($resetUrl));

    return response()->json(['message' => 'Reset link sent']);
}
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $cachedToken = Cache::get('password_reset_'.$request->email);

        if (!$cachedToken || $cachedToken !== $request->token) {
            return response()->json(['error' => 'Invalid or expired token'], 400);
        }

        $staff = Staff::where('email', $request->email)->firstOrFail();
        $staff->update(['password_hash' => Hash::make($request->password)]);

        // Clear the token
        Cache::forget('password_reset_'.$request->email);

        return response()->json(['message' => 'Password reset successfully']);
    }
}