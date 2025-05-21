<?php

use App\Http\Controllers\PDFController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('downloadUserReservationCheckout-pdf/{reservation_checkout_id}', [PDFController::class, 'downloadReservationCheckoutPdf'])->name('reservation_checkout_download.pdf');
