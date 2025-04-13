<?php



use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// User Routes
Route::post('v1/signup/user',[UserController::class,'signup']);
Route::post('v1/login/user',[UserController::class,'login']);
Route::get('v1/auth/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('v1/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
Route::post('v1/auth/forget-password', [MailController::class, 'forgetPassword']);
Route::post('v1/auth/reset-password', [MailController::class, 'resetPassword']);


// Menu Routes
Route::get('v1/menu', [MenuController::class, 'menu']);
Route::get('v1/menu/{id}', [MenuController::class, 'dish']);


// Team Routes
Route::get('v1/team', [TeamController::class, 'allTeamMember']);
Route::get('v1/team/{id}', [TeamController::class, 'teamMember']);


// Tables Routes
Route::get('v1/tables', [TableController::class, 'tables']);
Route::get('v1/tables/{id}', [TableController::class, 'singleTable']);

// Reviews Routes
Route::get('v1/reviews', [ReviewsController::class, 'getAllReviews']);
