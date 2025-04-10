<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\UploadedFile;

class ProfileController extends Controller
{
    // Update profile
    public function updateProfile(Request $request)
    {
        DB::beginTransaction();

        try {
            $staff = Auth::user();
            
            // Get or create user record
            $user = $staff->user ?? User::create([
                'staff_id' => $staff->staff_id,
                'preferences' => json_encode([]), // empty JSON object as default
                'profile_picture' => null
            ]);

            $request->validate([
                'username' => [
                    'sometimes',
                    'string',
                    'max:255',
                    'unique:staffs,username,' . $staff->staff_id . ',staff_id',
                ],
                'email' => [
                    'sometimes',
                    'email',
                    'max:255',
                    'unique:staffs,email,' . $staff->staff_id . ',staff_id',
                ],
                'profile_picture' => [
                    'sometimes',
                    function ($attribute, $value, $fail) {
                        if (is_string($value)) {
                            // Remove storage/app/public prefix if present
                            $path = str_replace('storage/app/public/', '', $value);
                            $path = str_replace('storage/', '', $path);
                            
                            if (!Storage::disk('public')->exists($path)) {
                                $fail('The specified image path does not exist.');
                            }
                            
                            $allowed = ['jpeg', 'png', 'jpg', 'gif'];
                            $extension = pathinfo($path, PATHINFO_EXTENSION);
                            if (!in_array(strtolower($extension), $allowed)) {
                                $fail('The profile picture must be a file of type: jpeg, png, jpg, gif.');
                            }
                        } elseif (!$value instanceof UploadedFile) {
                            $fail('The profile picture must be either a file upload or a valid image path.');
                        }
                    }
                ],
            ]);

            // Update staff attributes
            $staff->update($request->only(['username', 'email']));

            // Handle profile picture
            if ($request->has('profile_picture')) {
                if ($request->hasFile('profile_picture')) {
                    $this->handleFileUpload($user, $request->file('profile_picture'));
                } elseif (is_string($request->profile_picture)) {
                    $this->handlePathString($user, $request->profile_picture);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Profile updated successfully',
                'data' => [
                    'staff' => $staff,
                    'user' => $user->fresh()
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Profile update failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    protected function handleFileUpload(User $user, UploadedFile $file)
    {
        // Delete old picture if exists
        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        // Store new picture with unique filename
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('profile_pictures', $filename, 'public');
        
        $user->update(['profile_picture' => $path]);
    }

    protected function handlePathString(User $user, string $path)
    {
        // Normalize path by removing storage/app/public prefix if present
        $path = str_replace('storage/app/public/', '', $path);
        $path = str_replace('storage/', '', $path);
        $path = ltrim($path, '/');

        // Verify path exists in storage
        if (!Storage::disk('public')->exists($path)) {
            throw new \Exception('The specified image path does not exist.');
        }

        // Verify path is within allowed directory
        $allowedDirectory = 'profile_pictures/';
        if (!str_starts_with($path, $allowedDirectory)) {
            throw new \Exception('Image must be in the profile_pictures directory.');
        }

        // Update path if different
        if ($user->profile_picture !== $path) {
            $user->update(['profile_picture' => $path]);
        }
    }

    // Change password
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $staff = Auth::user();

        if (!Hash::check($request->current_password, $staff->password_hash)) {
            return response()->json(['error' => 'Current password is incorrect'], 401);
        }

        $staff->update([
            'password_hash' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'Password updated successfully'
        ]);
    }
}