<?php

namespace App\Services;

use Twilio\Rest\Client;
use App\Models\Order;

class OrderSmsService
{
    protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    /**
     * إرسال رسالة SMS بتفاصيل الأوردر للعميل
     */
    public function sendOrderDetails(Order $order, string $phone): bool
    {
        try {
            // إضافة رمز الدول الأمريكي (+1) إذا لم يكن موجود
            $phoneNumber = $this->formatPhoneNumber($phone);

            // بناء رسالة الأوردر
            $message = $this->buildOrderMessage($order);

            // إرسال الرسالة عن طريق Twilio
            $this->twilio->messages->create(
                $phoneNumber,
                [
                    'from' => config('services.twilio.phone_number'),
                    'body' => $message,
                ]
            );

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send order SMS', [
                'order_id' => $order->id,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * تنسيق رقم التليفون بإضافة رمز الدول الأمريكي
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // إزالة أي أحرف غير رقمية
        $cleanPhone = preg_replace('/\D/', '', $phone);

        // إذا كان الرقم يبدأ بـ 1 (رمز أمريكا)
        if (str_starts_with($cleanPhone, '1')) {
            return '+' . $cleanPhone;
        }

        // إضافة رمز أمريكا إذا لم يكن موجود
        if (strlen($cleanPhone) === 10) {
            return '+1' . $cleanPhone;
        }

        // إذا كان الرقم يحتوي على رمز دولة آخر
        if (!str_starts_with($cleanPhone, '1')) {
            return '+1' . $cleanPhone;
        }

        return '+' . $cleanPhone;
    }

    /**
     * بناء رسالة الأوردر بتفاصيل كاملة
     */
    protected function buildOrderMessage(Order $order): string
    {
        // تحميل الأيتمز البتاعة الأوردر
        $items = $order->orderItems()->with('item')->get();

        // بناء قائمة الأشياء المطلوبة
        $itemsList = "";
        foreach ($items as $orderItem) {
            $itemsList .= "• {$orderItem->item->name} × {$orderItem->quantity} - \${$orderItem->subtotal}\n";
        }

        // حساب الأسعار
        $subtotal = (float) $order->subtotal_price;
        $discount = (float) $order->discount_amount;
        $deliveryFee = $order->order_type === 'delivery' ? 5 : 0;
        $tax = (float) $order->total_price - $subtotal - $deliveryFee + $discount; // الضريبة = الإجمالي - الـ subtotal - رسوم الدليفري + الخصم
        $total = (float) $order->total_price;

        // بناء الرسالة
        $message = "🍕 Order Confirmation\n";
        $message .= "================\n\n";
        $message .= "Items Ordered:\n";
        $message .= $itemsList;
        $message .= "\n================\n";
        $message .= "Subtotal: \${$subtotal}\n";

        if ($discount > 0) {
            $message .= "Discount: -\${$discount}\n";
        }

        $message .= "Sales Tax (9.5%): \${$tax}\n";

        if ($deliveryFee > 0) {
            $message .= "Delivery Fee: \${$deliveryFee}\n";
        }

        $message .= "================\n";
        $message .= "Total: \${$total}\n";
        $message .= "================\n\n";
        $message .= "Thank you for your order! 🙏";

        return $message;
    }
}
