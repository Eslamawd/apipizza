<?php

namespace App\Services;

use App\Mail\OrderCreatedMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class OrderNotificationService
{
    public function sendOrderCreatedEmail(Order $order): void
    {
        $recipient = config('services.orders.notification_email');

        if (empty($recipient)) {
            return;
        }

        Mail::to($recipient)->send(new OrderCreatedMail($order));
    }
}
