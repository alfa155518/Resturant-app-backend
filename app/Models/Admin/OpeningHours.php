<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class OpeningHours extends Model
{
    protected $table = 'opening_hours';

    protected $fillable = [
        'day',
        'open',
        'close',
        'closed',
    ];

    protected $casts = [
        'closed' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
    ];

    public static function forgetOpeningHoursCache()
    {
        return Cache::forget('openingHours');
    }
}
