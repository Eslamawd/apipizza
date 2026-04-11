<?php

namespace App\Services;

use App\Models\Order;

class OrderPricingService
{
    private const TAX_RATE = 0.095; // 9.5%
    private const DELIVERY_FEE = 5.00;

    /**
     * حساب الأسعار الكاملة للأوردر
     */
    public function calculatePricing(Order $order, array $itemTotals): array
    {
        $subtotalAfterDiscount = $itemTotals['subtotalAfterDiscount'];
        $subtotalBeforeDiscount = $itemTotals['subtotalBeforeDiscount'];
        $discountTotal = $itemTotals['discountTotal'];

        // حساب رسوم التوصيل والضرائب
        $deliveryFee = $order->order_type === 'delivery' ? self::DELIVERY_FEE : 0;
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
            'tax' => $tax,
            'tips' => $tips,
            'total_price' => $finalTotal,
        ];
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
