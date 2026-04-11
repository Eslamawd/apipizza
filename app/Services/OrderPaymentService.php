<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;

class OrderPaymentService
{
    protected $cloverService;

    public function __construct()
    {
        $merchantId = config('services.clover.merchant_id');
        $this->cloverService = new CloverService($merchantId);
    }

    /**
     * معالجة الدفع للأوردر
     */
    public function processPayment(Order $order, ?string $paymentToken): array
    {
        // لو ما في payment token يبقى الدفع اختياري
        if (!$paymentToken) {
            return [
                'success' => true,
                'status' => 'not_required',
                'message' => 'Order created successfully.',
                'transaction_id' => null,
            ];
        }

        $finalTotal = $order->total_price;
        $chargeResult = $this->cloverService->executeCharge($paymentToken, $finalTotal);

        if (!$chargeResult['success']) {
            return $this->handleFailedPayment($order, $paymentToken, $chargeResult);
        }

        return $this->handleSuccessfulPayment($order, $paymentToken, $chargeResult);
    }

    /**
     * معالجة الدفع الفاشل
     */
    private function handleFailedPayment(Order $order, string $paymentToken, array $chargeResult): array
    {
        $reason = $chargeResult['reason'] ?? 'Payment failed';
        $errorCode = $chargeResult['error_code'] ?? null;
        $declineCode = $chargeResult['decline_code'] ?? null;

        // حفظ سجل الدفع الفاشل
        Payment::create([
            'order_id' => $order->id,
            'transaction_id' => null,
            'payment_token' => $paymentToken,
            'status' => 'failed',
            'reason' => $reason,
            'error_code' => $errorCode,
            'decline_code' => $declineCode,
            'amount' => $order->total_price,
        ]);

        $userFriendlyMessage = $this->getPaymentErrorMessage($declineCode, $reason);

        return [
            'success' => false,
            'status' => 'failed',
            'message' => $userFriendlyMessage,
            'reason' => $reason,
            'error_code' => $errorCode,
            'decline_code' => $declineCode,
            'transaction_id' => null,
        ];
    }

    /**
     * معالجة الدفع الناجح
     */
    private function handleSuccessfulPayment(Order $order, string $paymentToken, array $chargeResult): array
    {
        $transactionId = $chargeResult['transaction_id'] ?? null;

        $order->update([
            'status' => 'pending',
            'payment_status' => 'completed',
        ]);

        // حفظ سجل الدفع الناجح
        Payment::create([
            'order_id' => $order->id,
            'transaction_id' => $transactionId,
            'payment_token' => $paymentToken,
            'status' => 'success',
            'reason' => null,
            'error_code' => null,
            'decline_code' => null,
            'amount' => $order->total_price,
        ]);

        return [
            'success' => true,
            'status' => 'success',
            'message' => 'Payment completed successfully.',
            'transaction_id' => $transactionId,
        ];
    }

    /**
     * رسائل الأخطاء الودية للعميل
     */
    private function getPaymentErrorMessage(?string $declineCode = null, string $reason = ''): string
    {
        $messages = [
            'insufficient_funds' => 'Your card has insufficient funds. Please try another payment method.',
            'lost_card' => 'Your card has been reported as lost. Please use another card.',
            'stolen_card' => 'Your card has been reported as stolen. Please use another card.',
            'expired_card' => 'Your card has expired. Please use another card.',
            'incorrect_cvc' => 'The CVC code you entered is incorrect. Please try again.',
            'processing_error' => 'A payment processing error occurred. Please try again.',
            'card_not_supported' => 'This card type is not supported. Please use another card.',
        ];

        return $messages[$declineCode] ?? $reason ?? 'Payment failed. Please try again.';
    }
}
