<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CloverService
{
    protected $baseUrl;
    protected $token;
    protected $merchantId;

    public function __construct($merchantId, $restaurantToken = null)
    {
        $this->baseUrl = config('services.clover.base_url');
        $this->token = $restaurantToken ?? config('services.clover.token');
        $this->merchantId = $merchantId;
    }

    /**
     * Execute charge (payment) on Clover
     * @param string $paymentToken
     * @param float $amount
     * @return array|null
     */
    public function executeCharge($paymentToken, $amount)
    {
        $url = $this->baseUrl . "/v3/merchants/{$this->merchantId}/orders/charge";
        $payload = [
            'token' => $paymentToken,
            'amount' => intval($amount * 100), // Clover uses cents
        ];
        $response = Http::withToken($this->token)
            ->post($url, $payload);
        if ($response->successful()) {
            return $response->json();
        }
        return null;
    }
    // يمكنك إضافة المزيد من الميثودز حسب الحاجة (getOrder, updateOrder, ...)

    // يمكنك إضافة المزيد من الميثودز حسب الحاجة (getOrder, updateOrder, ...)
}
