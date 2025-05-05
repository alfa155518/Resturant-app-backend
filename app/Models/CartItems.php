<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItems extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'menu_id',
        'quantity',
        'price',
        'attributes'
    ];
    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];
    protected $hidden = [
        'deleted_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }


    /**
     * Calculate the total price for this cart item.
     *
     * @return float
     */
    public function getTotal(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get the total as an attribute.
     *
     * @return float
     */
    public function getTotalAttribute(): float
    {
        return $this->getTotal();
    }
}
