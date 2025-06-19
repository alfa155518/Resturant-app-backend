<?php


use App\Http\Controllers\Admin\RestaurantInfoController;
use App\Http\Controllers\Auth\ChangePersonalUserDataController;
use App\Http\Controllers\Auth\ChangeUserPasswordController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\CartItemsController;
use App\Http\Controllers\CheckoutsController;
use App\Http\Controllers\FavoriteProductsController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ReservationCheckoutsController;
use App\Http\Controllers\ReservationsController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\IsAuthorized;
use Illuminate\Support\Facades\Route;



// User Routes
Route::post('v1/signup/user', [UserController::class, 'signup']);
Route::post('v1/login/user', [UserController::class, 'login']);
Route::get('v1/auth/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('v1/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
Route::post('v1/auth/forget-password', [MailController::class, 'forgetPassword']);
Route::post('v1/auth/reset-password', [MailController::class, 'resetPassword']);
Route::get('v1/restaurant/info', [RestaurantInfoController::class, 'getRestaurantInfo']);


// User Auth Routes
Route::middleware(IsAuthorized::class)->group(function () {
    Route::post('v1/auth/change-password', [ChangeUserPasswordController::class, 'changeUserPassword']);
    Route::patch('v1/auth/change-personalData', [ChangePersonalUserDataController::class, 'changePersonalData']);
    Route::delete('v1/logout/user', [UserController::class, 'logout']);

    Route::post('v1/auth/enable-2fa', [TwoFactorController::class, 'enable']);
    Route::post('v1/auth/verify-2fa', [TwoFactorController::class, 'verify']);
    Route::delete('v1/auth/disable-2fa', [TwoFactorController::class, 'disable']);
});


// Menu Routes
Route::get('v1/menu', [MenuController::class, 'menu']);
Route::get('v1/menu/{id}', [MenuController::class, 'dish']);


// Team Routes
Route::get('v1/team', [TeamController::class, 'allTeamMember']);
Route::get('v1/team/{id}', [TeamController::class, 'teamMember']);


// Tables Routes
Route::get('v1/tables', [TableController::class, 'tables']);
Route::get('v1/tables/{id}', [TableController::class, 'singleTable']);

Route::middleware(IsAuthorized::class)->group(function () {
    Route::post('v1/reservations/{id}', [ReservationsController::class, 'ReserveTable']);
    Route::get('v1/reservations', [ReservationsController::class, 'getUserReservations']);
    Route::delete('v1/reservations/{id}', [ReservationsController::class, 'cancelReservation']);
});



// Reviews Routes
Route::get('v1/reviews', [ReviewsController::class, 'getAllReviews']);

// Cart Routes
Route::middleware(IsAuthorized::class)->group(function () {
    Route::post('v1/cart', [CartItemsController::class, 'addProductToCart']);
    Route::get('v1/cart', [CartItemsController::class, 'getCartItems']);
    Route::patch('v1/cart/{id}', [CartItemsController::class, 'addOrMinusQuantity']);
    Route::delete('v1/cart/{id}', [CartItemsController::class, 'removeProductFromCart']);
});

// Payment Routes
Route::middleware(IsAuthorized::class)->group(function () {
    Route::post('v1/payment', [StripeController::class, 'payment']);
    Route::get('v1/payment/verify', [StripeController::class, 'verifyPayment']);
});

// Checkout Product Routes
Route::middleware(IsAuthorized::class)->group(function () {
    Route::get('v1/checkouts', [CheckoutsController::class, 'userCheckouts']);
    Route::get('v1/checkouts/products', [CheckoutsController::class, 'userCheckoutProducts']);
});

// Reservation Checkout Routes
Route::middleware(IsAuthorized::class)->group(function () {
    Route::post('v1/checkouts/reservation', [ReservationCheckoutsController::class, 'createCheckoutSession']);
    Route::get('v1/checkouts/reservation/verify', [ReservationCheckoutsController::class, 'verifyPayment']);
});

// Favorite Products Routes
Route::middleware(IsAuthorized::class)->group(function () {
    Route::post('v1/favoriteProducts', [FavoriteProductsController::class, 'addFavoriteProduct']);
    Route::get('v1/favoriteProducts', [FavoriteProductsController::class, 'getUserFavorites']);
    Route::delete('v1/favoriteProducts/{id}', [FavoriteProductsController::class, 'removeFavoriteProduct']);
});


