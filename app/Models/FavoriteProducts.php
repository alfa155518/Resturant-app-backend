<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FavoriteProducts extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['user_id', 'product_id', 'added_at'];
    
    /**
     * The cache TTL in minutes
     * 
     * @var int
     */
    protected const CACHE_TTL = 30;
    
    /**
     * Get the user that owns the favorite product
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product that is favorited
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'product_id');
    }
    
    /**
     * Add a product to user's favorites
     *
     * @param int $userId
     * @param int $productId
     * @return array{favorite: self, created: bool}
     */
    public static function addToFavorites(int $userId, int $productId): array
    {
        $favorite = self::firstOrCreate(
            [
                'user_id' => $userId,
                'product_id' => $productId,
            ],
            [
                'added_at' => now(),
            ]
        );
        
        $wasCreated = $favorite->wasRecentlyCreated;
        
        if ($wasCreated) {
            self::invalidateUserCache($userId);
        }
        
        return [
            'favorite' => $favorite,
            'created' => $wasCreated
        ];
    }
    
    /**
     * Remove a product from user's favorites
     *
     * @param int $userId
     * @param int $productId
     * @return bool
     */
    public static function removeFromFavorites(int $userId, int $productId): bool
    {
        $deleted = self::where([
            'user_id' => $userId,
            'product_id' => $productId,
        ])->delete();
        
        if ($deleted) {
            self::invalidateUserCache($userId);
        }
        
        return (bool) $deleted;
    }
    
    /**
     * Get user's favorite products with caching
     *
     * @param int $userId
     * @return Collection
     */
    public static function getUserFavorites(int $userId): Collection
    {
        $cacheKey = self::getUserCacheKey($userId);
        
        return Cache::remember($cacheKey, Carbon::now()->addMinutes(self::CACHE_TTL), function () use ($userId) {
            return self::where('user_id', $userId)
                ->with(['product:id,name,price,image,description,category,rating'])
                ->latest('added_at')
                ->get(['id', 'user_id', 'product_id', 'added_at']);
        });
    }
    
    /**
     * Get the cache key for a user's favorites
     *
     * @param int $userId
     * @return string
     */
    protected static function getUserCacheKey(int $userId): string
    {
        return 'user_favorites_' . $userId;
    }
    
    /**
     * Invalidate a user's favorites cache
     *
     * @param int $userId
     * @return void
     */
    public static function invalidateUserCache(int $userId): void
    {
        Cache::forget(self::getUserCacheKey($userId));
    }
}
