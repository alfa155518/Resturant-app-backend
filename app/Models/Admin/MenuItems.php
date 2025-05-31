<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MenuItems extends Model
{
    /**
     * Cache key for storing tracked pages
     * 
     * @var string
     */
    public const CACHED_PAGES_KEY = 'menu_cached_pages';

    /**
     * Cache duration for tracked pages (1 day)
     * 
     * @var int
     */
    public const CACHE_DURATION = 1440; // 24 hours in minutes

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menus';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'image_public_id',
        'category',
        'calories',
        'rating',
        'prepTime',
        'dietary',
        'ingredients',
        'stock',
        'available',
        'popular',
        'featured'
    ];

    /**
     * Validation rules for the menu item
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|string|max:100',
        'description' => 'required|string|max:65535',
        'price' => 'required|numeric|between:0,999999.99',
        'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        'category' => 'required|string|max:50',
        'calories' => 'sometimes|integer|min:0|max:65535',
        'rating' => 'sometimes|numeric|between:0,10|max:9.9',
        'prepTime' => 'required|string|max:20',
        'dietary' => 'required|min:1',
        'dietary.*' => 'string',
        'ingredients' => 'required|min:1',
        'ingredients.*' => 'string',
        'stock' => 'required|integer|min:0',
        'available' => 'sometimes|boolean',
        'popular' => 'sometimes|boolean',
        'featured' => 'sometimes|boolean',
    ];

    /**
     * Custom validation messages
     *
     * @var array
     */
    public static $messages = [
        'dietary.required' => 'At least one dietary information is required',
        'dietary.min' => 'At least one dietary information is required',
        'ingredients.required' => 'At least one ingredient is required',
        'ingredients.min' => 'At least one ingredient is required',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'dietary' => 'array',
        'ingredients' => 'array',
        'available' => 'boolean',
        'popular' => 'boolean',
        'featured' => 'boolean',
        'price' => 'float',
        'calories' => 'integer',
        'rating' => 'float',
        'stock' => 'integer'
    ];



    /**
     * Handle image update for a menu item
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Admin\MenuItems $menuItem
     * @param \App\Helpers\handelUploadPhoto $uploadHandler
     * @param array &$updateData
     * @return void
     */
    public static function handleImageUpdate($request, $menuItem, $uploadHandler, &$updateData)
    {
        // Delete old image if exists
        if ($menuItem->image_public_id) {
            $uploadHandler->deletePhoto($menuItem->image_public_id);
        }

        // Upload new image
        $uploadResult = $uploadHandler->uploadPhoto(
            $request->file('image'),
            'menu'
        );

        $updateData = [
            'image' => $uploadResult['avatar'],
            'image_public_id' => $uploadResult['avatar_public_id']
        ];
    }


    // ** Handle image upload for create menu item
    /**
     * Handle image upload for create menu item
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Helpers\handelUploadPhoto $uploadHandler
     * @param array &$validatedData
     * @return void
     */

    public static function handleUploadItemImage($request, $uploadHandler, &$validatedData)
    {
        try {
            $uploadResult = $uploadHandler->uploadPhoto(
                $request->file('image'),
                'menu'
            );
            $validatedData['image'] = $uploadResult['avatar'];
            $validatedData['image_public_id'] = $uploadResult['avatar_public_id'];
        } catch (\Exception $e) {
            Log::error('Failed to upload menu item image: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return self::validationFailed("Failed to upload image, try again");
        }
    }




    // ** Delete image for menu item
    /**
     * Delete image for menu item
     *
     * @param string $imagePublicId
     * @param \App\Helpers\handelUploadPhoto $uploadHandler
     * @param int $id
     * @return void
     */
    public static function deleteItemImage($imagePublicId, $uploadHandler, $id)
    {
        try {
            $uploadHandler->deletePhoto($imagePublicId);
        } catch (\Exception $e) {
            // Log the error but don't fail the request
            Log::error('Failed to delete menu item image: ' . $e->getMessage(), [
                'menu_item_id' => $id,
                'public_id' => $imagePublicId,
                'exception' => $e
            ]);
            return self::validationFailed("Failed to delete image");
        }
    }


    /**
     * Get the cache key for menu items with page parameter
     *
     * @param int $page
     * @return string
     */
    public static function getMenuKey(int $page): string
    {
        return 'menu_items_page_' . $page;
    }

    /**
     * Add a page to the tracked cached pages
     * 
     * @param int $page Page number to track
     * @return void
     */
    public static function addCachedPage(int $page): void
    {
        $cachedPages = Cache::get(self::CACHED_PAGES_KEY, []);

        if (!in_array($page, $cachedPages, true)) {
            $cachedPages[] = $page;
            Cache::put(self::CACHED_PAGES_KEY, $cachedPages, now()->addMinutes(self::CACHE_DURATION));
        }
    }

    /**
     * Get paginated menu items with caching
     *
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public static function getPaginatedMenuItems(int $page = 1, int $perPage = 20): array
    {
        $cacheKey = self::getMenuKey($page);

        // Add this page to tracked pages
        self::addCachedPage($page);

        return Cache::remember($cacheKey, now()->addHour(), function () use ($perPage) {
            $paginator = self::query()->paginate($perPage);

            return [
                'current_page' => $paginator->currentPage(),
                'items' => $paginator->items(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ];
        });
    }

    /**
     * Invalidate cache for specific page or all pages
     *
     * @param int|null $page Page number or null for all pages
     * @return void
     */
    public static function invalidateMenuCache(?int $page = null): void
    {
        if ($page !== null) {
            // Invalidate specific page
            $cacheKey = self::getMenuKey($page);
            Cache::forget($cacheKey);

            // Remove from tracked pages if exists
            $cachedPages = Cache::get(self::CACHED_PAGES_KEY, []);
            if (($key = array_search($page, $cachedPages, true)) !== false) {
                unset($cachedPages[$key]);
                Cache::put(self::CACHED_PAGES_KEY, $cachedPages, now()->addMinutes(self::CACHE_DURATION));
            }
        } else {
            // Invalidate all cached pages
            $cachedPages = Cache::get(self::CACHED_PAGES_KEY, []);

            foreach ($cachedPages as $cachedPage) {
                $cacheKey = self::getMenuKey($cachedPage);
                Cache::forget($cacheKey);
            }

            // Clear the tracked pages array from cache
            Cache::forget(self::CACHED_PAGES_KEY);

            Log::info('Invalidated cached pages: ' . json_encode($cachedPages));
        }
    }
}