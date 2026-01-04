<?php 

use App\Http\Controllers\RestaurantController;
use App\Http\Middleware\VerifyRestaurantAccess;
use Illuminate\Support\Facades\Route;

    Route::get('/restaurants/{restaurant}/user', [RestaurantController::class, 'getByUser'])->middleware([VerifyRestaurantAccess::class]);
 
