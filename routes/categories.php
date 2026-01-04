<?php 

use App\Http\Controllers\CategoryController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', AdminMiddleware::class])->group(function () {

    // المسارات العادية بدون شرط
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::patch('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // فقط الـ store عليه middleware التحقق
    Route::post('/categories', [CategoryController::class, 'store']);
});
