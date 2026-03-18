<?php

namespace App\Services;

use App\Models\Restaurant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebasePushService
{
    public function notifyRestaurantOrder(int $restaurantId, string $title, string $body, array $data = []): void
    {
        $credentials = $this->loadServiceAccountCredentials();
        $projectId = config('services.firebase.project_id') ?: ($credentials['project_id'] ?? null);

        if (! $credentials || ! $projectId) {
            return;
        }

        $restaurant = Restaurant::with('user:id,mobile_push_token')->find($restaurantId);

        if (! $restaurant || ! $restaurant->user || ! $restaurant->user->mobile_push_token) {
            return;
        }

        $accessToken = $this->getAccessToken($credentials, (string) $projectId);

        if (! $accessToken) {
            return;
        }

        try {
            Http::withToken($accessToken)
                ->asJson()
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token' => $restaurant->user->mobile_push_token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => $this->normalizeDataPayload($data),
                        'android' => [
                            'priority' => 'HIGH',
                            'notification' => [
                                'sound' => 'default',
                            ],
                        ],
                        'webpush' => [
                            'notification' => [
                                'title' => $title,
                                'body' => $body,
                                'requireInteraction' => true,
                                'icon' => '/logo.png',
                                'badge' => '/logo.png',
                            ],
                            'fcm_options' => [
                                'link' => '/',
                            ],
                        ],
                    ],
                ])->throw();
        } catch (\Throwable $exception) {
            Log::warning('FCM v1 notification failed', [
                'restaurant_id' => $restaurantId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function loadServiceAccountCredentials(): ?array
    {
        $credentialsPath = config('services.firebase.credentials');

        if (! $credentialsPath) {
            return null;
        }

        $resolvedPath = $this->resolvePath((string) $credentialsPath);

        if (! is_file($resolvedPath)) {
            return null;
        }

        $contents = file_get_contents($resolvedPath);
        $decoded = json_decode($contents ?: '', true);

        if (! is_array($decoded)) {
            return null;
        }

        if (empty($decoded['client_email']) || empty($decoded['private_key'])) {
            return null;
        }

        return $decoded;
    }

    private function getAccessToken(array $credentials, string $projectId): ?string
    {
        return Cache::remember(
            "firebase_fcm_access_token_{$projectId}",
            now()->addMinutes(50),
            function () use ($credentials): ?string {
                $jwt = $this->buildJwtAssertion($credentials);

                if (! $jwt) {
                    return null;
                }

                $tokenUri = $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token';

                $response = Http::asForm()->post($tokenUri, [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]);

                if (! $response->successful()) {
                    return null;
                }

                return $response->json('access_token');
            }
        );
    }

    private function buildJwtAssertion(array $credentials): ?string
    {
        $now = time();
        $tokenUri = $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token';

        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $payload = [
            'iss' => $credentials['client_email'],
            'sub' => $credentials['client_email'],
            'aud' => $tokenUri,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES) ?: ''),
            $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES) ?: ''),
        ];

        $signingInput = implode('.', $segments);
        $signature = '';

        $privateKey = $credentials['private_key'];
        $signed = openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (! $signed) {
            return null;
        }

        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function normalizeDataPayload(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            $normalized[(string) $key] = is_scalar($value) || $value === null
                ? (string) ($value ?? '')
                : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

        return $normalized;
    }

    private function resolvePath(string $path): string
    {
        if (preg_match('/^[A-Za-z]:[\\\\\/]/', $path) || str_starts_with($path, '/')) {
            return $path;
        }

        return base_path($path);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
