<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanSubscripeController;
use App\Http\Controllers\SubscripeController;
use Illuminate\Support\Facades\Route;

Route::get('/restaurants/{id}/qr/{model}', function ($id,$model) {
    $path = storage_path("app/public/{$model}/qr/{$id}.png");

    if (!file_exists($path)) {
        return response()->json(['error' => 'QR not found'], 404);
    }

    return response()->file($path, [
        'Access-Control-Allow-Origin' => '*',
        'Content-Type' => 'image/png',
        'Cache-Control' => 'no-cache',
    ]);
});

Route::middleware(['auth:sanctum'])->group(function () {
      
    Route::post('/payment/banking', [PaymentController::class, 'banking']);
    Route::get('/payment/user', [PaymentController::class, 'getByUser']);
    Route::post('/subscriptions/{id}', [SubscripeController::class, 'store']);

});

      Route::post('/contact', [ContactController::class, 'store']);
      Route::get('/plan-subscripes', [PlanSubscripeController::class, 'index']);





require __DIR__.'/auth.php';
require __DIR__.'/menu.php';
require __DIR__.'/items.php';
require __DIR__.'/itemOptions.php';
require __DIR__.'/table.php';
require __DIR__.'/user.php';
require __DIR__.'/order.php';
require __DIR__.'/withdraw.php';
require __DIR__.'/categories.php';
require __DIR__.'/inventoryItem.php';
require __DIR__.'/restaurant.php';
require __DIR__.'/admin.php';
