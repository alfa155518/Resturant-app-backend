<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Checkouts extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_email',
        'customer_name',
        'session_id',
        'payment_intent_id',
        'amount_total',
        'currency',
        'payment_status',
        'payment_method',
        'payment_date',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CheckoutItem::class, 'checkout_id');
    }
}
