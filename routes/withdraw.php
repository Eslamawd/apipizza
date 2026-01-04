<?php


use App\Http\Controllers\WithdrawPaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/withdraw', [WithdrawPaymentController::class, 'store']);
    Route::get('/withdraw', [WithdrawPaymentController::class, 'index']);
});

