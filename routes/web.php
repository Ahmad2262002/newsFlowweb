<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/send-otp', function(Request $request) {
    $request->validate([
        'email' => 'required|email',
        'otp' => 'required|numeric'
    ]);
    
    try {
        $email = $request->input('email');
        $otp = $request->input('otp');
        
        Mail::to($email)->send(new OtpMail($otp));
        
        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully to ' . $email
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'config' => config('mail')
        ], 500);
    }
});