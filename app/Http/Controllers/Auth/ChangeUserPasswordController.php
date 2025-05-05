<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\SecurityHeaders;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\PersonalAccessToken;

class ChangeUserPasswordController extends Controller
{
    public function changeUserPassword(Request $request)
    {
        $bearerToken = $request->header('Authorization');
        
        $token = str_replace('Bearer ', '', $bearerToken);
        
        // Find token in database
        $tokenModel = PersonalAccessToken::findToken($token);
        
        // Get user from token
        $user = $tokenModel->tokenable;
        
        if (!$user) {
            return self::notFound('User');
        }
        
        // Validate request data
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'password_confirmation' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect'
            ], 401);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        $response = response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully',
        ], 200);

        return SecurityHeaders::secureHeaders($response);
    }
}
