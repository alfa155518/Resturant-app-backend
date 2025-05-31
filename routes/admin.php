<?php

use App\Http\Controllers\Admin\MenuItemsController;
use App\Http\Middleware\IsAdmin;

Route::middleware(IsAdmin::class)->group(function () {
    Route::get('v1/admin/menu', [MenuItemsController::class, 'menuItems']);
    Route::patch('v1/admin/menu/{id}', [MenuItemsController::class, 'updateMenuItem']);
    Route::post('v1/admin/menu', [MenuItemsController::class, 'createMenuItem']);
    Route::delete('v1/admin/menu/{id}', [MenuItemsController::class, 'deleteMenuItem']);
});