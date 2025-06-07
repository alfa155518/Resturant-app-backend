<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CheckoutItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'checkout_id',
        'product_name',
        'price',
        'quantity',
        'image',
        'delivery_status',
    ];

    /**
     * Get checkout items for a specific user with caching
     * 
     * @param int $userId
     * @param array $columns
     * @param int $cacheTtl Cache time to live in seconds (default: 60 minutes)
     * @return \Illuminate\Database\Eloquent\Collection
     */



    // Get Checkouts For User
    public static function getCheckoutsByUserId($userId, $columns = ['*'], $cacheTtl = 0)
    {
        $cacheKey = "user_checkout_items_{$userId}_" . md5(json_encode($columns));


        return Cache::remember($cacheKey, $cacheTtl, function () use ($userId, $columns) {
            return self::whereHas('checkout', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->with([
                    'checkout' => function ($query) {
                        $query->select(
                            'id',
                            'amount_total',
                            'currency',
                            'payment_status',
                            'created_at',
                            'payment_date',
                            'payment_method',
                            'delivery_status',
                        );
                    }
                ])
                ->select($columns)
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    public function checkout()
    {
        return $this->belongsTo(Checkouts::class, 'checkout_id');
    }
}
