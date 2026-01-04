<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Kitchen;
use App\Models\Restaurant;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class VerifyKitchenAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $kitchenId    = $request->input('kitchen');
        $restaurantId = $request->input('restaurant');
        $userId       = $request->input('user');
        $token        = $request->input('token');

        // ✅ 1. التحقق من المعاملات المطلوبة
        if (!$kitchenId || !$userId || !$restaurantId || !$token) {
            return response()->json(['message' => 'Missing required parameters.'], 400);
        }

        // ✅ 2. التحقق من المطبخ + أنه تابع للمطعم + التوكن صحيح
        $kitchen = Kitchen::where([
            ['id', $kitchenId],
            ['restaurant_id', $restaurantId],   // مهم جدًا ✅
            ['token', $token],
        ])->first();

        if (!$kitchen) {
            return response()->json(['message' => 'Invalid kitchen credentials.'], 403);
        }

        // ✅ 3. التحقق من المطعم وأنه فعلاً يخص هذا المستخدم
        $restaurant = Restaurant::where([
            ['id', $kitchen->restaurant_id], // نتأكد أنه نفس المطعم الخاص بالمطبخ
            ['user_id', $userId],
        ])->first();

        if (!$restaurant) {
            return response()->json(['message' => 'Invalid restaurant credentials.'], 403);
        }

        

        // ✅ 5. تمرير القيم للطلب (request) لاستخدامها بعد كده في الـ Controller
        $request->merge([
            'kitchen'      => $kitchen,
            'restaurant'   => $restaurant,
        ]);

        return $next($request);
    }
}
