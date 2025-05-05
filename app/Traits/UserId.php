<?php

namespace App\Traits;

use App\Http\Controllers\UserController;
use Laravel\Sanctum\PersonalAccessToken;

trait UserId
{
  public function getUserId($request)
  {
    $bearerToken = $request->header('Authorization');

    $token = str_replace('Bearer ', '', $bearerToken);

    // Find token in database
    $tokenModel = PersonalAccessToken::findToken($token);

    // Get user from token
    $userId = $tokenModel->tokenable->id;

    if (!isset($userId)) {
      return new UserController()->unauthorized();
    }

    return $userId;

  }
}