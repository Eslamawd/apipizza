<?php

namespace App\Http\Middleware;

use App\Models\Cashier;
use Closure;
use Illuminate\Http\Request;
use App\Models\Kitchen;
use App\Models\Restaurant;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class VerifyCashierAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $cashierId    = $request->input('cashier');
        $restaurantId = $request->input('restaurant');
        $userId       = $request->input('user');
        $token        = $request->input('token');

        // ✅ 1. التحقق من المعاملات المطلوبة
        if (!$cashierId || !$userId || !$restaurantId || !$token) {
            return response()->json(['message' => 'Missing required parameters.'], 400);
        }

        // ✅ 2. التحقق من المطبخ + أنه تابع للمطعم + التوكن صحيح
        $cashier = Cashier::where([
            ['id', $cashierId],
            ['restaurant_id', $restaurantId],   // مهم جدًا ✅
            ['token', $token],
        ])->first();

        if (!$cashier) {
            return response()->json(['message' => 'Invalid cashier credentials.'], 403);
        }

        // ✅ 3. التحقق من المطعم وأنه فعلاً يخص هذا المستخدم
        $restaurant = Restaurant::where([
            ['id', $cashier->restaurant_id], // نتأكد أنه نفس المطعم الخاص بالكاشير
            ['user_id', $userId],
        ])->first();

        if (!$restaurant) {
            return response()->json(['message' => 'Invalid restaurant credentials.'], 403);
        }

        // ✅ 4. التحقق من الاشتراك

     

        // ✅ 5. تمرير القيم للطلب (request) لاستخدامها بعد كده في الـ Controller
        $request->merge([
            'cashier'      => $cashier,
            'restaurant'   => $restaurant
        ]);

        return $next($request);
    }
}
