<?php

namespace App\Services;

use App\Mail\OrderCreatedMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class OrderNotificationService
{
    protected $smsService;
    protected $webSocketService;

    public function __construct(OrderSmsService $smsService, WebSocketService $webSocketService)
    {
        $this->smsService = $smsService;
        $this->webSocketService = $webSocketService;
    }

    /**
     * إرسال جميع التنبيهات للأوردر الجديد
     */
    public function notifyNewOrder(Order $order): void
    {
        // إرسال البريد الإلكتروني للمطعم
        $this->sendOrderCreatedEmail($order);

        // إرسال رسالة SMS للعميل
        if ($order->phone) {
            $this->smsService->sendOrderDetails($order, $order->phone);
        }

        // إرسال إشعار مباشر عن طريق WebSocket للمطبخ
        $this->webSocketService->sendNewOrder($order);
    }

    /**
     * إرسال بريد تأكيد الأوردر للمطعم
     */
    public function sendOrderCreatedEmail(Order $order): void
    {
        $recipient = config('services.orders.notification_email');

        if (empty($recipient)) {
            return;
        }

        Mail::to($recipient)->send(new OrderCreatedMail($order));
    }

    /**
     * إرسال رسالة SMS بتفاصيل الأوردر للعميل
     */
    public function sendOrderSms(Order $order): bool
    {
        if (!$order->phone) {
            return false;
        }

        return $this->smsService->sendOrderDetails($order, $order->phone);
    }
}
