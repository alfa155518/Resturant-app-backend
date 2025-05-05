<?php

namespace App\Traits;
use App\Models\CartItems;


trait UserCartItems
{
    public function getUserCartItems($userId)
    {
      $cartItems = CartItems::where('user_id', $userId)
      ->select('id', 'user_id', 'menu_id', 'quantity', 'price', 'total', 'attributes', 'created_at')
      ->with([
          'user:id,name,email,phone',
          'product:id,name,description,price,image'
      ])
      ->get();
      return $cartItems;
    }
}