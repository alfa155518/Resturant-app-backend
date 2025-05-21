<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReservationCheckouts extends Model
{
    protected $fillable = [
        'user_id',
        'customer_email',
        'customer_name',
        'session_id',
        'payment_intent_id',
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

    /**
     * Relationship with User model
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservations::class);
    }


}
