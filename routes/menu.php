<?php 

use App\Http\Controllers\MenuController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/menus', [MenuController::class, 'index']);
Route::middleware(['auth:sanctum', AdminMiddleware::class])->group(function () {

    // المسارات العادية بدون شرط
    Route::get('/menus/{menu}', [MenuController::class, 'show']);
    Route::patch('/menus/{menu}', [MenuController::class, 'update']);
    Route::delete('/menus/{menu}', [MenuController::class, 'destroy']);

    // فقط الـ store عليه middleware التحقق
    Route::post('/menus', [MenuController::class, 'store']);
});
