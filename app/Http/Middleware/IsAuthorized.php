<?php

namespace App\Http\Middleware;

use App\Http\Controllers\UserController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class IsAuthorized
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
                'message' => 'You must be Logged In'
            ], 401);
        }

        Log::info(message: $bearerToken);
        $token = str_replace('Bearer ', '', $bearerToken);

        // Find token in database
        $tokenModel = PersonalAccessToken::findToken($token);

        //check if token in database
        if (!$tokenModel) {
            $userController = app()->make(UserController::class);
            return $userController->unauthorized("You must be Logged In");
        }

        return $next($request);
    }
}
