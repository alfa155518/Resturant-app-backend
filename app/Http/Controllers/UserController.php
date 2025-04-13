<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
class UserController extends Controller
{

    // Signup 
    public function signup(Request $request)
    {
        try {
            // Validate incoming request first
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users|max:255',
                'phone' => 'required|string|unique:users|min:11|max:15|regex:/^[0-9]+$/',
                'password' => 'required|string|min:8',
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            // Then sanitize the validated data
            $sanitizedData = [
                'name' => trim(strip_tags($validated['name'])),
                'email' => filter_var(trim($validated['email']), FILTER_SANITIZE_EMAIL),
                'phone' => preg_replace('/[^0-9+]/', '', $validated['phone']),
                'password' => trim($validated['password']),
            ];

            // Handle avatar upload and user creation via model
            if ($request->hasFile('avatar')) {
                $result = User::createUser($sanitizedData, $request->file('avatar'));
                // Return success response
                return response()->json([
                    'message' => 'User created successfully',
                    'user' => $result['user'],
                ], 201);
            }
            return response()->json(['message' => 'No avatar provided'], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create Account',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Login
    public function login(Request $request)
    {
        try {
            $userData = User::login($request);
            $user = $userData['user'];

            // Check if user has existing tokens
            if ($user->tokens->count() > 0) {
                // Delete existing tokens
                $user->tokens()->delete();
            }
            ;
            // Create a new token
            $token = $user->createToken('auth_token')->plainTextToken;
            $userResponse = $user->makeHidden(['email_verified_at', 'avatar_public_id', 'address', 'tokens']);
            return response()->json([
                'message' => 'User Login successfully',
                'user' => $userResponse,
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            // Check if the exception has a specific code
            $code = $e->getCode();
            if ($code == 404 || $code == 401) {
                return response()->json([
                    'message' => $e->getMessage()
                ], $code);
            }
            return response()->json([
                'message' => 'Failed to Login',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}