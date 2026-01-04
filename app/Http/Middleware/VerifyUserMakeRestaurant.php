<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyUserMakeRestaurant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $subscription = $user->activeSubscription;

        if (!$subscription || !$subscription->planSub) {
            return response()->json([
                'message' => 'No active subscription found.',
                 'message_ar' => 'لا يوجد اشتراك نشط.',
            ], 403);
        }

        $maxRestaurants = $subscription->planSub->max_restaurants;

        $currentRestaurants = $user->restaurants()->count();

        if ($currentRestaurants >= $maxRestaurants) {
            return response()->json([
                'message' => 'You have reached the maximum number of restaurants allowed for your plan.',
                 'message_ar' => 'لقد وصلت إلى الحد الأقصى المسموح به من الأصناف حسب اشتراكك.',
            ], 403);
        }

        return $next($request);
    }
}
