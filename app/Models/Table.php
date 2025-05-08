<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Table extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'table_number',
        'capacity',
        'image',
        'description',
        'features',
        'details',
        'is_available',
        'is_reservable',
        'min_guests',
        'max_guests',
        'status'
    ];

    protected $casts = [
        'features' => 'array',
        'is_available' => 'boolean',
        'is_reservable' => 'boolean'
    ];

    public function reservations()
    {
        return $this->hasMany(Reservations::class, 'table_id');
    }
}
