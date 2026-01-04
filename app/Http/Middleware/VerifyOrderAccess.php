<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use Symfony\Component\HttpFoundation\Response;

class VerifyOrderAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $restaurantParam = $request->route('restaurant');
        $restaurantId = $restaurantParam instanceof Restaurant
            ? $restaurantParam->id
            : $restaurantParam;

        $userId = $request->user()->id;

        $restaurant = Restaurant::where([
            ['id', $restaurantId],
            ['user_id', $userId],
        ])->first();

        if (!$restaurant) {
            return response()->json(['message' => 'Invalid restaurant credentials.'], 403);
        }

        $request->merge([
            'restaurant' => $restaurant,
        ]);

        return $next($request);
    }
}
