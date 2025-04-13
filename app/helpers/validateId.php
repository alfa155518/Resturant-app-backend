<?php

namespace App\Helpers;
class ValidateId
{
  public static function validateNumeric($value, $fieldName = 'ID')
  {
    if (!is_numeric($value) || $value <= 0) {
      return response()->json([
        'status' => 'error',
        'message' => "Invalid {$fieldName} format. Must be numeric."
      ], 400);
    }
    return null;
  }
}