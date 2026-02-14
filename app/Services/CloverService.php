<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

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
     * @return array
     */
    public function executeCharge($paymentToken, $amount)
    {
        $amountInCents = intval($amount * 100);
        $attempts = $this->getChargeAttempts($paymentToken, $amountInCents);
        $lastFailure = null;

        Log::info('Clover charge request started', [
            'merchant_id' => $this->merchantId,
            'amount' => $amountInCents,
            'source' => $this->maskValue($paymentToken),
            'attempts_count' => count($attempts),
        ]);

        try {
            foreach ($attempts as $index => $attempt) {
                Log::info('Clover charge attempt', [
                    'merchant_id' => $this->merchantId,
                    'attempt' => $index + 1,
                    'url' => $attempt['url'],
                    'payload_keys' => array_keys($attempt['payload']),
                ]);

                $response = Http::withToken($this->token)
                    ->timeout(20)
                    ->post($attempt['url'], $attempt['payload']);

                if ($response->successful()) {
                    $responseData = $response->json();

                    Log::info('Clover charge request succeeded', [
                        'merchant_id' => $this->merchantId,
                        'amount_cents' => $amountInCents,
                        'status_code' => $response->status(),
                        'response_id' => $responseData['id'] ?? null,
                        'attempt' => $index + 1,
                        'url' => $attempt['url'],
                    ]);

                    return [
                        'success' => true,
                        'transaction_id' => $responseData['id'] ?? null,
                        'status_code' => $response->status(),
                        'reason' => null,
                        'error_code' => null,
                        'decline_code' => null,
                        'data' => $responseData,
                    ];
                }

                $responseJson = $response->json();
                if (!is_array($responseJson)) {
                    $responseJson = [];
                }

                $lastFailure = [
                    'status_code' => $response->status(),
                    'reason' => $responseJson['error']['message']
                        ?? $responseJson['message']
                        ?? 'Payment failed',
                    'error_code' => $responseJson['error']['code'] ?? null,
                    'decline_code' => $responseJson['error']['declineCode'] ?? null,
                ];

                Log::warning('Clover charge attempt failed', [
                    'merchant_id' => $this->merchantId,
                    'amount_cents' => $amountInCents,
                    'attempt' => $index + 1,
                    'url' => $attempt['url'],
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                ]);
            }
        } catch (Throwable $exception) {
            Log::error('Clover charge request exception', [
                'merchant_id' => $this->merchantId,
                'amount_cents' => $amountInCents,
                'message' => $exception->getMessage(),
            ]);
        }

        Log::warning('Clover charge request failed after all attempts', [
            'merchant_id' => $this->merchantId,
            'amount_cents' => $amountInCents,
        ]);

        return [
            'success' => false,
            'transaction_id' => null,
            'status_code' => $lastFailure['status_code'] ?? null,
            'reason' => $lastFailure['reason'] ?? 'Payment failed',
            'error_code' => $lastFailure['error_code'] ?? null,
            'decline_code' => $lastFailure['decline_code'] ?? null,
            'data' => null,
        ];
    }

    protected function getChargeAttempts(string $paymentToken, int $amountInCents): array
    {
        $primaryUrl = rtrim($this->baseUrl, '/') . '/v1/charges';
        $merchantChargeUrl = rtrim($this->baseUrl, '/') . "/v3/merchants/{$this->merchantId}/charges";

        return [
            [
                'url' => $primaryUrl,
                'payload' => [
                    'amount' => $amountInCents,
                    'source' => $paymentToken,
                    'currency' => 'usd',
                ],
            ],
            [
                'url' => $merchantChargeUrl,
                'payload' => [
                    'amount' => $amountInCents,
                    'token' => $paymentToken,
                ],
            ],
        ];
    }

    protected function maskValue(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $length = strlen($value);
        if ($length <= 6) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, 3) . str_repeat('*', $length - 6) . substr($value, -3);
    }

    // يمكنك إضافة المزيد من الميثودز حسب الحاجة (getOrder, updateOrder, ...)

    // يمكنك إضافة المزيد من الميثودز حسب الحاجة (getOrder, updateOrder, ...)
}
