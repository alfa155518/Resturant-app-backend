<?php

namespace App\Models\Admin;

use App\Models\Admin\RestaurantInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PaymentMethods extends Model
{
    protected $fillable = [
        'restaurant_infos_id',
        'credit_card',
        'debit_card',
        'cash',
        'paypal',
        'apple_pay',
        'google_pay',
    ];

    protected $casts = [
        'credit_card' => 'boolean',
        'debit_card' => 'boolean',
        'cash' => 'boolean',
        'paypal' => 'boolean',
        'apple_pay' => 'boolean',
        'google_pay' => 'boolean',
    ];

    protected $hidden = [
        'restaurant_infos_id',
    ];

    public static $rules = [
        'credit_card' => 'required|boolean',
        'debit_card' => 'required|boolean',
        'cash' => 'required|boolean',
        'paypal' => 'required|boolean',
        'apple_pay' => 'required|boolean',
        'google_pay' => 'required|boolean',
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
