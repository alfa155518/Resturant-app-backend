<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;


class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->header('Authorization');

        // Extract token from Bearer string
        if (!$bearerToken || !str_starts_with($bearerToken, 'Bearer ')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication required'
            ], 401);
        }

        $token = str_replace('Bearer ', '', $bearerToken);
        $tokenModel = PersonalAccessToken::findToken($token);

        if (!$tokenModel) {
            return response()->json([
                'status' => 'error',
                'message' => 'You Must Be Logged In'
            ], 401);
        }

        $user = $tokenModel->tokenable;

        // Check if user exists and has admin role
        if (!$user || $user->role !== 'admin' && $user->role !== 'super-admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        return $next($request);
    }
}
