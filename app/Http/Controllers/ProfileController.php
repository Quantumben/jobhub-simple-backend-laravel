<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function update(
        UpdateProfileRequest $request
    ): JsonResponse {

        $user =
            $request->user();

        $user->update(
            $request->validated()
        );

        return response()->json([
            'message' => 'Profile updated successfully.',

            'user' => $user->fresh(),
        ]);
    }

    public function updatePassword(
        UpdatePasswordRequest $request
    ): JsonResponse {

        $user =
            $request->user();

        /*
    |--------------------------------------------------------------------------
    | Verify Current Password
    |--------------------------------------------------------------------------
    */

        if (
            ! Hash::check(
                $request->current_password,
                $user->password
            )
        ) {

            throw ValidationException::withMessages([
                'current_password' => [
                    'Your current password is incorrect.',
                ],

            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Change Password
    |--------------------------------------------------------------------------
    */

        $user->update([

            'password' => Hash::make(
                $request->password
            ),

        ]);

        return response()->json([

            'message' => 'Password changed successfully.',

        ]);
    }
}
