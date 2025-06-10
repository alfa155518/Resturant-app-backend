<?php

namespace App\Models\Admin;

use Cache;
use Illuminate\Database\Eloquent\Model;
use Log;


class RestaurantInfo extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'website',
        'logo',
        'logo_public_id',
        'message',
        'description',
        'timezone',
        'is_active',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    // protected $hidden = [
    //     'created_at',
    //     'updated_at',
    //     'logo_public_id',
    // ];

    /**
     * Get the validation rules for the model.
     * 
     * @var array<string, string>
     */
    public static $rules = [
        'name' => 'required|string|max:100',
        'address' => 'required|string|max:500',
        'phone' => 'required|string|max:20',
        'email' => 'required|email|max:255',
        'website' => 'required|url|max:255',
        'logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        'message' => 'required|string|max:255',
        'description' => 'required|string|max:255',
        'timezone' => 'required|timezone',
        'is_active' => 'required|boolean',
    ];


    /**
     * Handle uploading a team member's image
     *
     * @param \Illuminate\Http\Request $request
     * @return array|array[]
     */
    protected static function handleUploadLogo($request, $uploadHandler, $info)
    {
        try {

            // Delete old logo if exists
            if ($info->logo_public_id) {
                $uploadHandler->deletePhoto($info->logo_public_id);
            }

            // Upload new logo
            $uploadResult = $uploadHandler->uploadPhoto(
                $request->file('logo'),
                'settings'
            );
            return [
                'logo' => $uploadResult['avatar'],
                'logo_public_id' => $uploadResult['avatar_public_id']
            ];
        } catch (\Exception $e) {
            Log::error('Failed to upload restaurant logo image: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return self::validationFailed("Failed to upload logo, try again");
        }
    }


    /**
     * Clear cache
     * 
     * @return void
     */
    protected static function clearCache($cacheKey)
    {
        Cache::forget($cacheKey);
    }

}
