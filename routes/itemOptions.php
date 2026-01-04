<?php 
use App\Http\Controllers\ItemOptionController;
use App\Http\Middleware\AdminMiddleware;

Route::middleware(['auth:sanctum', AdminMiddleware::class])->group(function () {
    Route::get('/item-options/{itemOption}', [ItemOptionController::class, 'show']);
    Route::post('/item-options', [ItemOptionController::class, 'store']);
    Route::put('/item-options/{itemOption}', [ItemOptionController::class, 'update']);
    Route::delete('/item-options/{itemOption}', [ItemOptionController::class, 'destroy']);
});
