<?php

namespace App\Http\Controllers\Web;

use App\Models\Staff;
use App\Models\User;
use App\Models\Admin;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;
use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function showResetPasswordForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function showVerifyOtpForm()
    {
        return view('auth.verify-otp');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $staff = Staff::where('email', $request->email)->first();

        if (!$staff || !Hash::check($request->password, $staff->password_hash)) {
            return back()->withErrors(['email' => 'Invalid credentials']);
        }

        auth()->login($staff, $request->remember);

        if ($staff->role_id == 1 || $staff->role_id == 2) {
            return redirect()->route('admin.dashboard');
        } elseif ($staff->role_id == 3) {
            return redirect()->route('employee.dashboard');
        }

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }

    public function registerAdmin(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:staffs'],
            'email' => ['required', 'email', 'unique:staffs'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'access_level' => ['required', 'string'],
        ]);

        try {
            $staff = Staff::create([
                'role_id' => 2,
                'username' => $request->username,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'is_locked' => false
            ]);

            Admin::create([
                'staff_id' => $staff->staff_id,
                'access_level' => $request->access_level,
            ]);

            return redirect()->route('login')->with('success', 'Registration successful! Please log in.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $staff = Staff::where('email', $request->email)->first();
        
        if (!$staff) {
            return back()->with('status', 'If registered, you will receive an email');
        }

        $token = Str::random(60);
        Cache::put('password_reset_' . $staff->email, $token, now()->addHour());

        $resetUrl = url('/reset-password?token='.$token.'&email='.urlencode($staff->email));

        try {
            Mail::to($staff->email)->send(new PasswordResetMail($resetUrl));
            return back()->with('status', 'Reset link sent');
        } catch (\Exception $e) {
            Log::error('Password reset email failed: ' . $e->getMessage());
            return back()->with('status', 'If registered, you will receive an email');
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:staffs,email',
            'token' => 'required|string',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $cachedToken = Cache::get('password_reset_'.$request->email);

        if (!$cachedToken || !hash_equals($cachedToken, $request->token)) {
            return back()->withErrors(['token' => 'Invalid or expired token']);
        }

        try {
            DB::transaction(function () use ($request) {
                $staff = Staff::where('email', $request->email)->firstOrFail();
                $staff->update([
                    'password_hash' => Hash::make($request->password),
                    'remember_token' => null,
                ]);

                Cache::forget('password_reset_'.$request->email);
            });

            return redirect()->route('login')->with('status', 'Password reset successfully');
        } catch (\Exception $e) {
            Log::error('Password reset failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Password reset failed']);
        }
    }

    public function verifyOtp(Request $request)
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
                return back()->withErrors(['otp' => 'OTP expired']);
            }

            if ($storedOtp != $otp) {
                return back()->withErrors(['otp' => 'Invalid OTP']);
            }

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

            auth()->login($staff);

            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Registration failed']);
        }
    }
}