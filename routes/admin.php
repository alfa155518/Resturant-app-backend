<?php

use App\Http\Controllers\Admin\MenuItemsController;
use App\Http\Controllers\Admin\OrdersController;
use App\Http\Controllers\Admin\ReservationsController;
use App\Http\Controllers\Admin\RestaurantInfoController;
use App\Http\Controllers\Admin\ReviewsController;
use App\Http\Controllers\Admin\TeamController;
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

    // reviews
    Route::get('v1/admin/reviews', [ReviewsController::class, 'reviews']);
    Route::delete('v1/admin/reviews/{id}', [ReviewsController::class, 'deleteReview']);
    Route::patch('v1/admin/reviews/{id}', [ReviewsController::class, 'updateReview']);

    // orders
    Route::get('v1/admin/orders', [OrdersController::class, 'allOrders']);
    Route::patch('v1/admin/orders/{id}', [OrdersController::class, 'updateOrder']);
    Route::delete('v1/admin/orders/{id}', [OrdersController::class, 'deleteOrder']);

    // team
    Route::get('v1/admin/team', [TeamController::class, 'getMembers']);
    Route::post('v1/admin/team', [TeamController::class, 'createMember']);
    Route::patch('v1/admin/team/{id}', [TeamController::class, 'updateMember']);
    Route::delete('v1/admin/team/{id}', [TeamController::class, 'deleteMember']);

    // restaurant info
    Route::get('v1/admin/restaurant/settings', [RestaurantInfoController::class, 'getRestaurantInfo']);
    Route::patch('v1/admin/restaurant/settings/{id}', [RestaurantInfoController::class, 'updateInfo']);
    Route::post('v1/admin/restaurant/settings', [RestaurantInfoController::class, 'createInfo']);
});