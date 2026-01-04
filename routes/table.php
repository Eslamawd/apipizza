<?php 
use App\Http\Controllers\TableController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\VerifyUserMakeTables;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', AdminMiddleware::class])->group(function () {

    // عرض الطاولات
    Route::get('/tables', [TableController::class, 'index']);
    Route::get('/tables/{table}', [TableController::class, 'show']);

    // العمليات المحمية
    Route::patch('/tables/{table}', [TableController::class, 'update']);
    Route::delete('/tables/{table}', [TableController::class, 'destroy']);
    Route::post('/tables', [TableController::class, 'store']);
});
