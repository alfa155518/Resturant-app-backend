<?php

use App\Http\Controllers\Admin\MenuItemsController;
use App\Http\Controllers\Admin\ReservationsController;
use App\Http\Middleware\IsAdmin;

Route::middleware(IsAdmin::class)->group(function () {
    // menu
    Route::get('v1/admin/menu', [MenuItemsController::class, 'menuItems']);
    Route::patch('v1/admin/menu/{id}', [MenuItemsController::class, 'updateMenuItem']);
    Route::post('v1/admin/menu', [MenuItemsController::class, 'createMenuItem']);
    Route::delete('v1/admin/menu/{id}', [MenuItemsController::class, 'deleteMenuItem']);

    // reservations
    Route::get('v1/admin/reservations', [ReservationsController::class, 'usersReservations']);
    Route::delete('v1/admin/reservations/{id}', [ReservationsController::class, 'deleteReservation']);
    Route::patch('v1/admin/reservations/{id}', [ReservationsController::class, 'updateReservation']);
});