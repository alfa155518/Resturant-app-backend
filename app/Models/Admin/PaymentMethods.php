<?php

namespace App\Models\Admin;

use App\Models\Admin\RestaurantInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PaymentMethods extends Model
{
    protected $fillable = [
        'name',
        'enabled'
    ];

    protected $hidden = [
        'restaurant_infos_id',
    ];

    public static $rules = [
        'payment_methods' => 'required|array',
        'payment_methods.*.id' => 'required|integer|exists:payment_methods,id',
        'payment_methods.*.name' => 'required|string|max:255',
        'payment_methods.*.enabled' => 'required|boolean',
    ];
    public function restaurantInfo()
    {
        return $this->belongsTo(RestaurantInfo::class);
    }
    public static function clearPaymentMethodsCache()
    {
        Cache::forget('paymentMethods');
    }
}
