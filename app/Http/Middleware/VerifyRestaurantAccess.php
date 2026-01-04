<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class VerifyRestaurantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $restaurantId = $request->input('restaurant');
        $userId = $request->input('user');
        $token = $request->input('token');

        // تحقق من وجود المعاملات المطلوبة
        if (!$restaurantId || !$userId || !$token) {
            return response()->json(['message' => 'Missing required parameters.'], 400);
        }


        // تحقق من المطعم وصحة التوكن
        $restaurant = Restaurant::where([
            ['id', $restaurantId],
            ['user_id', $userId],
            ['token', $token],
        ])->first();

        if (!$restaurant) {
            return response()->json(['message' => 'Invalid restaurant credentials.'], 403);
        }

        // تحقق من الاشتراك الفعّال للمستخدم


        // تمرير المطعم والاشتراك للطلب لاستخدامهم بعدين
        $request->merge([
            'restaurant' => $restaurant,
        ]);

        return $next($request);
    }
}
