<?php

namespace App\Helpers;
class SecurityHeaders {
  public static function secureHeaders($response) {
    return $response
      ->header('X-Frame-Options', 'SAMEORIGIN')
      ->header('X-XSS-Protection', '1; mode=block')
      ->header('X-Content-Type-Options', 'nosniff')
      ->header('Content-Security-Policy', "default-src 'self'")
      ->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
  }
}