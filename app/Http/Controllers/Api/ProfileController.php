<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class ProfileController extends Controller
{
    public function updateProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $staff = Auth::user();

            // Validate incoming data
            $validatedData = $request->validate([
                'username' => 'sometimes|string|max:255|unique:staffs,username,' . $staff->staff_id . ',staff_id',
                'email' => 'sometimes|email|max:255|unique:staffs,email,' . $staff->staff_id . ',staff_id',
                'profile_picture' => 'sometimes|string', // base64 image string
            ]);

            // Update username and email if provided
            $staff->update($request->only(['username', 'email']));

            // Handle the base64 profile picture
            if ($request->filled('profile_picture')) {
                $base64Image = $request->profile_picture;
                $this->handleBase64Image($base64Image, $staff);
            }

            return response()->json([
                'message' => 'Profile updated successfully',
                'data' => [
                    'staff' => $staff,
                    'user' => $staff->user->makeVisible(['profile_picture'] ?? null),
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Profile update failed', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Profile update failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    protected function handleBase64Image(string $base64Image, $staff): void
{
    // Extract base64 string
    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
        $extension = strtolower($type[1]); // jpg, png, gif, etc.
        $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
    } else {
        throw new \Exception('Invalid base64 image string format.');
    }

    $imageData = base64_decode($base64Image);

    if ($imageData === false) {
        throw new \Exception('Base64 decode failed.');
    }

    // Save file
    $filename = uniqid() . '.' . $extension;
    $path = storage_path('app/public/profile_pictures/' . $filename);
    file_put_contents($path, $imageData);

    // Link to user
    $user = $staff->user ?? User::create([
        'staff_id' => $staff->staff_id,
        'preferences' => [],
        'profile_picture' => null,
    ]);

    $user->update(['profile_picture' => 'profile_pictures/' . $filename]);

    Log::info('Uploaded new profile picture:', ['path' => $path]);
}


    public function changePassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $staff = Auth::user();

        if (!\Hash::check($request->current_password, $staff->password_hash)) {
            return response()->json(['error' => 'Current password is incorrect'], 401);
        }

        $staff->update(['password_hash' => \Hash::make($request->password)]);

        Log::info('Password updated for staff', ['staff_id' => $staff->staff_id]);

        return response()->json(['message' => 'Password updated successfully']);
    }
}
