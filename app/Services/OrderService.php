<?php

namespace App\Services;

use App\Models\Order;

class OrderService
{
    protected $itemProcessService;
    protected $pricingService;
    protected $paymentService;

    public function __construct(
        OrderItemProcessService $itemProcessService,
        OrderPricingService $pricingService,
        OrderPaymentService $paymentService
    ) {
        $this->itemProcessService = $itemProcessService;
        $this->pricingService = $pricingService;
        $this->paymentService = $paymentService;
    }

    /**
     * إنشاء أوردر جديد من الـ request data
     */
    public function createOrderFromRequest(array $data): Order
    {
        return Order::create([
            'restaurant_id' => $data['restaurant_id'],
            'table_id' => $data['table_id'] ?? null,
            'user_id' => auth()->id() ?: null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'customer_name' => $data['customer_name'] ?? null,
            'tip_percentage' => $data['tip_percentage'] ?? null,
            'tips' => $data['tips'] ?? null,
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'scheduled_time' => $data['scheduled_time'] ?? null,
            'scheduled_for' => $data['scheduled_for'] ?? null,
            'subtotal_price' => 0,
            'discount_amount' => 0,
            'discount_percentage' => 0,
            'total_price' => 0,
            'status' => 'pending',
            'order_type' => $data['order_type'],
        ]);
    }

    /**
     * معالجة الأوردر (items, pricing, payment)
     */
    public function processOrder(Order $order, array $itemsData, ?string $paymentToken): array
    {
        try {
            // 1. إضافة الـ items وحساب الأسعار الأولية
            $itemTotals = $this->itemProcessService->addItemsToOrder($order, $itemsData);

            // 2. حساب الأسعار الكاملة
            $pricing = $this->pricingService->calculatePricing($order, $itemTotals);
            $this->pricingService->updateOrderPrices($order, $pricing);

            // 3. معالجة الدفع
            $paymentResult = $this->paymentService->processPayment($order, $paymentToken);

            // إذا فشل الدفع، احذف الأوردر
            if (!$paymentResult['success']) {
                $order->delete();
                return [
                    'success' => false,
                    'payment_result' => $paymentResult,
                ];
            }

            // إعادة تحميل الأوردر مع العلاقات
            $order = Order::with([
                'table:id,name',
                'restaurant:id,name',
                'orderItems.item',
                'orderItems.options'
            ])->find($order->id);

            return [
                'success' => true,
                'order' => $order,
                'payment_result' => $paymentResult,
                'pricing' => $pricing,
            ];
        } catch (\Exception $e) {
            // في حالة خطأ، احذف الأوردر
            $order->delete();
            throw $e;
        }
    }

    /**
     * الحصول على الأوردر مع كل البيانات
     */
    public function getOrderWithRelations(Order $order): Order
    {
        return Order::with([
            'table:id,name',
            'restaurant:id,name',
            'orderItems.item',
            'orderItems.options'
        ])->find($order->id);
    }

    /**
     * تنسيق الرد على الـ API
     */
    public function formatResponse(Order $order, array $paymentResult): array
    {
        return [
            'payment_status' => $paymentResult['status'],
            'message' => $paymentResult['message'],
            'transaction_id' => $paymentResult['transaction_id'] ?? null,
            'order' => $order,
        ];
    }

    /**
     * تنسيق رد الخطأ
     */
    public function formatErrorResponse(array $paymentResult): array
    {
        return [
            'payment_status' => $paymentResult['status'],
            'message' => $paymentResult['message'],
            'reason' => $paymentResult['reason'],
            'error_code' => $paymentResult['error_code'] ?? null,
            'decline_code' => $paymentResult['decline_code'] ?? null,
        ];
    }
}
