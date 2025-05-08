<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller
{

  use AuthorizesRequests, ValidatesRequests;
  public static function notFound($data)
  {
    return response()->json([
      'status' => 'error',
      'message' => "$data not found"
    ], 404);
  }

  public static function unauthorized($message = 'Unauthorized access')
  {
    return response()->json([
      'status' => 'error',
      'message' => $message
    ], 401);
  }

  public static function validationFailed($errors)
  {
    return response()->json([
      'status' => 'error',
      'message' => $errors,
    ], 422);
  }
  public static function serverError()
  {
    return response()->json([
      'status' => 'error',
      'message' => "Server Error",
    ], 500);
  }

}

