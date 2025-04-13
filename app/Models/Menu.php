<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'menu_id', 'name', 'description', 'price', 'image', 'category', 'popular', 
        'rating', 'prepTime', 'calories', 'dietary', 'ingredients', 'stock'
    ];

    protected $casts = [
        'dietary' => 'array',
        'ingredients' => 'array',
        'popular' => 'boolean',
        'price' => 'float',
        'rating' => 'float',
    ];
}
