<?php


namespace App\Helpers;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;

class handelUploadPhoto
{
  public function uploadPhoto($photo, $path = "users")
  {
    $uploadResult = Cloudinary::upload($photo->getRealPath(), [
      'folder' => "laravel-restaurant/$path",
      'public_id' => 'user_' . time() . '_' . uniqid(),
      'transformation' => [
        'width' => 400,
        'height' => 400,
        'crop' => 'fill',
        'quality' => 'auto'
      ]
    ]);
    $avatarUrl = $uploadResult->getSecurePath();
    $avatarPublicId = $uploadResult->getPublicId();

    return [
      'avatar' => $avatarUrl,
      'avatar_public_id' => $avatarPublicId
    ];
  }

  public function deletePhoto($publicId)
  {
    try {
      if ($publicId) {
        Cloudinary::destroy($publicId);
        return true;
      }
    } catch (\Exception $e) {
      // Log the error for debugging
      Log::error("Cloudinary delete failed for public ID: {$publicId}", ['error' => $e->getMessage()]);
    }
    return false;
  }

}