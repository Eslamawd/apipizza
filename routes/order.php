<?php 

use App\Http\Controllers\OrderController;
use App\Http\Middleware\VerifyCashierAccess;
use App\Http\Middleware\VerifyKitchenAccess;
use App\Http\Middleware\VerifyRestaurantAccess;
use Illuminate\Support\Facades\Route;

    Route::post('/orders', [OrderController::class, 'store'])->middleware(VerifyRestaurantAccess::class);
    
    Route::delete('/orders/{order}/items/{itemId}', [OrderController::class, 'removeOrderItem'])->middleware(VerifyRestaurantAccess::class);
    Route::patch('/orders/{order}/items/{itemId}/quantity', [OrderController::class, 'updateOrderItemQuantity'])->middleware(VerifyRestaurantAccess::class);

    
    Route::get('/orders/kitchen', [OrderController::class, 'getByKitchen'])->middleware(VerifyKitchenAccess::class);

    
    Route::get('/orders/cashier', [OrderController::class, 'getByCashier'])->middleware(VerifyCashierAccess::class);

    Route::patch('/orders/{id}/kitchen', [OrderController::class, 'updateStatus'])->middleware(VerifyKitchenAccess::class);

    Route::patch('/orders/{id}/cashier', [OrderController::class, 'updateStatus'])->middleware(VerifyCashierAccess::class);

    Route::get('/orders/{order}/user', [OrderController::class, 'show'])->middleware(VerifyRestaurantAccess::class);

    
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/orders/', [OrderController::class, 'index']);
});


    Route::post('/orders/delivry', [OrderController::class, 'store']);