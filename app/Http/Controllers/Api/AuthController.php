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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Password;


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

            // Log successful password reset
            Log::info("Password reset completed for: {$request->email}");
            
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
                Log::error('Mail sending failed: ' . $mailException->getMessage());
                return response()->json([
                    'error' => 'Failed to send OTP',
                    'details' => $mailException->getMessage()
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
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
            if ($request->wantsJson()) {
                return response()->json([
                    'staff' => $staff,
                    'admin_profile' => $admin,
                    'token' => $token
                ], 201);
            }
            // Web response
            return redirect()->route('login')->with('success', 'Registration successful! Please log in.');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Admin registration failed: ' . $e->getMessage()
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
        if ($request->wantsJson()) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    // For web login
    auth()->login($staff, $request->remember);

    // Check if the request wants JSON (API)
    if ($request->wantsJson()) {
        $staff->tokens()->delete();
        $token = $staff->createToken('auth-token')->plainTextToken;

        // Fetch the user if the staff is a regular user (role_id = 4)
        $user = null;
        if ($staff->role_id == 4) {
            $user = User::where('staff_id', $staff->staff_id)->first();
        }
        
        return response()->json([
            'message' => 'Login successful',
            'staff' => $staff,
            'token' => $token,
            'role' => $staff->role_id,
            'user_type' => $this->getUserType($staff->role_id),
            'user_id' => $user ? $user->user_id : null // Add this line
        ]);
    }

     // Directly redirect to specific dashboards
    if ($staff->role_id == 1 || $staff->role_id == 2) {
        return redirect()->route('admin.dashboard');
        
    } elseif ($staff->role_id == 3) {
        return redirect()->route('employee.dashboard');
    }

    return redirect()->route('dashboard');
}

protected function getUserType($roleId)
{
    switch ($roleId) {
        case 1: return 'super_admin';
        case 2: return 'admin';
        case 3: return 'employee';
        default: return 'user';
    }
}

   public function logout(Request $request)
{
    // For API requests (using Sanctum tokens)
    if ($request->wantsJson() || $request->is('api/*')) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
    
    // For web requests (session-based)
    auth()->guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    return redirect('/');
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
        DB::beginTransaction();
        
        try {
            // Delete all related data
            DB::table('likes')->where('user_id', $user->user_id)->delete();
            DB::table('comments')->where('user_id', $user->user_id)->delete();
            DB::table('shares')->where('user_id', $user->user_id)->delete();
            DB::table('feedbacks')->where('user_id', $user->user_id)->delete();
            
            // Delete the user profile
            $user->delete();
            
            // Delete the staff account
            $staff->delete();
            
            // Commit the transaction
            DB::commit();
            
            return response()->json(['message' => 'Account and all related data deleted successfully']);
            
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            Log::error('Account deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Account deletion failed'], 500);
        }
        
    } catch (\Exception $e) {
        Log::error('Account deletion error: ' . $e->getMessage());
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
     public function sendResetLink(Request $request)
{
    $request->validate(['email' => 'required|email']);

    $staff = Staff::where('email', $request->email)->first();
    
    // Always return the same response to prevent email enumeration
    $response = ['message' => 'If registered, you will receive an email'];
    
    if ($staff) {
        $token = Str::random(60);
        Cache::put('password_reset_' . $staff->email, $token, now()->addHour());

        try {
            $resetUrl = url(route('password.reset', [
        'token' => $token, 
        'email' => $staff->email
    ]));
            Mail::to($staff->email)->send(new PasswordResetMail($resetUrl));
            
            Log::info("Password reset requested for: {$request->email}", [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'reset_url' => $resetUrl // Log the generated URL
            ]);
            
            $response = ['message' => 'Reset link sent'];
        } catch (\Exception $e) {
            Log::error('Password reset email failed: ' . $e->getMessage());
        }
    }

    return $request->wantsJson() 
        ? response()->json($response)
        : back()->with('status', __($response['message']));
}

    // protected function generateSecureResetUrl($email, $token)
    // {
    //     // For production - use your actual domain
    //     // $domain = 'newsflow.bond';
    //     $domain = 'localhost:8000'; // Use your actual domain or localhost for testing

        
    //     // For mobile app deep links
    //     // $appUrl = "newsflow://reset-password?token=$token&email=" . urlencode($email);
    //     $appUrl = "localhost:8000://reset-password?token=$token&email=" . urlencode($email);    

        
    //     // For web fallback
    //     $webUrl = "https://$domain/reset-password?token=$token&email=" . urlencode($email);
        
    //     // Validate the URL format
    //     if (!filter_var($webUrl, FILTER_VALIDATE_URL)) {
    //         throw new \Exception('Invalid URL generated');
    //     }
        
    //     // Ensure the URL points to your domain
    //     $parsed = parse_url($webUrl);
    //     if ($parsed['host'] !== $domain) {
    //         throw new \Exception('Invalid domain in reset URL');
    //     }

    //     return $webUrl; // or $appUrl depending on your needs
    // }

    protected function generateSecureResetUrl($email, $token)
{
    // Get the frontend URLs from config
    $frontendUrls = explode(',', config('app.frontend_url'));
    
    // Use the first URL as default, or fallback to localhost
    $baseUrl = !empty($frontendUrls[0]) ? trim($frontendUrls[0]) : 'http://localhost:8000';
    
    // For mobile apps, we want to use the IP address if available
    $isMobileRequest = request()->header('User-Agent') && 
                      (str_contains(request()->header('User-Agent'), 'Dart') || 
                      str_contains(request()->header('User-Agent'), 'Flutter'));
    
    if ($isMobileRequest && count($frontendUrls) > 1) {
        $baseUrl = trim($frontendUrls[1]); // Use the IP address for mobile
    }
    
    // Build the reset URL
    $resetUrl = "{$baseUrl}/reset-password/{$token}?email=".urlencode($email);
    
    // Validate the URL
    if (!filter_var($resetUrl, FILTER_VALIDATE_URL)) {
        throw new \Exception('Invalid reset URL generated: '.$resetUrl);
    }
    
    return $resetUrl;
}

    public function resetPassword(Request $request)
{
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    // Get the cached token
    $cachedToken = Cache::get('password_reset_'.$request->email);

    // Verify the token
    if (!$cachedToken || !hash_equals($cachedToken, $request->token)) {
        return back()->withInput($request->only('email'))
                   ->withErrors(['token' => 'Invalid or expired token']);
    }

    try {
        DB::transaction(function () use ($request) {
            $staff = Staff::where('email', $request->email)->firstOrFail();
            $staff->update([
                'password_hash' => Hash::make($request->password),
                'remember_token' => null,
            ]);

            // Clear the cached token
            Cache::forget('password_reset_'.$request->email);
        });

        return redirect()->route('login')->with('status', 'Password reset successfully');
    } catch (\Exception $e) {
        Log::error('Password reset failed: ' . $e->getMessage());
        return back()->withInput($request->only('email'))
                   ->withErrors(['error' => 'Password reset failed']);
    }
}


    public function sendResetLinkApi(Request $request)
{
    $request->validate(['email' => 'required|email']);

    $staff = Staff::where('email', $request->email)->first();
    
    if (!$staff) {
        return response()->json(['message' => 'If registered, you will receive an email']);
    }

    $token = Str::random(60);
    Cache::put('password_reset_' . $staff->email, $token, now()->addHour());

    // Generate API reset URL (points to your frontend)
    $resetUrl = $this->generateApiResetUrl($staff->email, $token);

    try {
        Mail::to($staff->email)->send(new PasswordResetMail($resetUrl));
        return response()->json(['message' => 'Reset link sent']);
    } catch (\Exception $e) {
        return response()->json(['message' => 'If registered, you will receive an email']);
    }
}

protected function generateApiResetUrl($email, $token)
{
    // Point to your frontend reset page with the token
    $frontendUrl = config('app.frontend_url');
    return "{$frontendUrl}/reset-password?token={$token}&email=".urlencode($email);
}

public function resetPasswordApi(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:staffs,email',
        'token' => 'required|string',
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $cachedToken = Cache::get('password_reset_'.$request->email);

    if (!$cachedToken || !hash_equals($cachedToken, $request->token)) {
        return response()->json(['error' => 'Invalid or expired token'], 400);
    }

    // ... rest of your reset logic ...
}

    public function showLoginForm()
{
    if (request()->wantsJson()) {
        return response()->json(['message' => 'Login form']);
    }
    return view('auth.login', [
        'title' => 'Login',
        // Add any other data your view might need
    ]);
}

    public function showRegistrationForm()
{
    return view('auth.register');
}

/**
 * Show the password reset request form.
 */
public function showForgotPasswordForm()
{
    return view('auth.forgot-password');
}

/**
 * Show the password reset form.
 */
public function showResetPasswordForm(Request $request, $token)
{
    // Verify the token first
    $cachedToken = Cache::get('password_reset_'.$request->email);
    
    if (!$cachedToken || !hash_equals($cachedToken, $token)) {
        abort(403, 'Invalid or expired token');
    }

    // For API requests
    if ($request->expectsJson()) {
        return response()->json([
            'token' => $token,
            'email' => $request->email
        ]);
    }

    // For web requests
    return view('auth.reset-password', [
        'token' => $token,
        'email' => $request->email
    ]);
}
}