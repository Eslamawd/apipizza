<?php

namespace App\Services;

use App\Models\Order;

class OrderPricingService
{
    private const TAX_RATE = 0.095; // 9.5%
    private const DELIVERY_FEE = 5.00;
    private const DELIVERY_RADIUS_MILES = 8; // maximum delivery radius

    /**
     * حساب الأسعار الكاملة للأوردر
     */
    public function calculatePricing(Order $order, array $itemTotals): array
    {
        $subtotalAfterDiscount = $itemTotals['subtotalAfterDiscount'];
        $subtotalBeforeDiscount = $itemTotals['subtotalBeforeDiscount'];
        $discountTotal = $itemTotals['discountTotal'];

        // حساب رسوم التوصيل والضرائب
        $deliveryFee = 0;
        $deliveryDistance = null;
        $deliveryError = null;

        if ($order->order_type === 'delivery') {
            // Try to compute distance between restaurant and customer if coordinates are present
            $restaurant = $order->restaurant()->first();
            $restLat = $restaurant->latitude ?? null;
            $restLng = $restaurant->longitude ?? null;
            $custLat = $order->latitude ?? null;
            $custLng = $order->longitude ?? null;

            if ($restLat !== null && $restLng !== null && $custLat !== null && $custLng !== null) {
                $deliveryDistance = $this->haversineMiles((float) $restLat, (float) $restLng, (float) $custLat, (float) $custLng);

                if ($deliveryDistance > self::DELIVERY_RADIUS_MILES) {
                    $deliveryError = "Delivery Unavailable: distances over " . self::DELIVERY_RADIUS_MILES . " miles";
                    $deliveryFee = 0;
                } elseif ($deliveryDistance <= 4) {
                    $deliveryFee = self::DELIVERY_FEE;
                } else {
                    $deliveryFee = round($deliveryDistance + 1.5, 2);
                }
            } else {
                // Fallback to default flat fee when coordinates unavailable
                $deliveryFee = self::DELIVERY_FEE;
            }
        }
        $subtotalWithFees = $subtotalAfterDiscount + $deliveryFee;
        $tax = $subtotalWithFees * self::TAX_RATE;

        // الإجمالي
        $tips = (float) ($order->tips ?? 0);
        $finalTotal = $subtotalWithFees + $tax + $tips;

        // نسبة الخصم الفعلية
        $effectiveDiscountPercentage = $subtotalBeforeDiscount > 0
            ? ($discountTotal / $subtotalBeforeDiscount) * 100
            : 0;

        return [
            'subtotal_price' => $subtotalBeforeDiscount,
            'discount_amount' => $discountTotal,
            'discount_percentage' => $effectiveDiscountPercentage,
            'delivery_fee' => $deliveryFee,
            'delivery_distance' => $deliveryDistance,
            'delivery_error' => $deliveryError,
            'tax' => $tax,
            'tips' => $tips,
            'total_price' => $finalTotal,
        ];
    }

    /**
     * Calculate great-circle distance (miles) between two coords using Haversine formula
     */
    private function haversineMiles(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 3958.8; // miles
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * تحديث الأسعار في الأوردر
     */
    public function updateOrderPrices(Order $order, array $pricing): void
    {
        $order->update([
            'subtotal_price' => $pricing['subtotal_price'],
            'discount_amount' => $pricing['discount_amount'],
            'discount_percentage' => $pricing['discount_percentage'],
            'delivery_fee' => $pricing['delivery_fee'] ?? null,
            'delivery_distance' => $pricing['delivery_distance'] ?? null,
            'tax' => $pricing['tax'] ?? null,
            'tips' => $pricing['tips'] ?? null,
            'total_price' => $pricing['total_price'],
        ]);
    }

    /**
     * الحصول على breakdown الأسعار
     */
    public function getPricingBreakdown(Order $order, array $pricing): array
    {
        return [
            'subtotal' => $pricing['subtotal_price'],
            'discount' => $pricing['discount_amount'],
            'discount_percentage' => $pricing['discount_percentage'],
            'delivery_fee' => $pricing['delivery_fee'],
            'tax' => $pricing['tax'],
            'tips' => $pricing['tips'],
            'total' => $pricing['total_price'],
        ];
    }
}
