<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;


class GoogleController extends Controller
{
    public function redirectToGoogle() 
    {
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        // Remove the dd() call that's causing issues
        return response()->json(['url' => $url]);
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            $user = User::where('email', $googleUser->getEmail())->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'avatar' => $googleUser->getAvatar(),
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar() ?? $user->avatar,
                ]);
            }
    
            if ($user->tokens->count() > 0) {
                $user->tokens()->delete();
            }
            
            $token = $user->createToken('google-auth')->plainTextToken;
            
            $userResponse = $user->makeHidden(['email_verified_at', 'avatar_public_id', 'address', 'tokens']);
            
            // Store user data and token in cookies
            $userData = json_encode($userResponse);
            
            // Create redirect response with cookies
            return redirect()->to('http://localhost:3000')
            ->cookie('userToken', $token, 525600, null, null, false, false) // 1 year (525600 minutes), HTTP only false to allow JS access
            ->cookie('user', $userData, 525600, null, null, false, false);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }
}