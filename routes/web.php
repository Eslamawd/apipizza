<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PaymentController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});




    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
Route::post('/payment/callback', [PaymentController::class, 'callback'])
     ->withoutMiddleware([VerifyCsrfToken::class]);

Route::get('/orders/{order}/invoice.pdf', [\App\Http\Controllers\OrderController::class, 'downloadInvoicePdf'])
    ->middleware('signed')
    ->name('orders.pdf.invoice');
