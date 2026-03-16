<?php

$allowedOrigins = explode(',', env('CORS_ALLOWED_ORIGINS', config('app.frontend_url', 'http://localhost:3000')));
$allowedOrigins = array_values(array_filter(array_map('trim', $allowedOrigins)));

$allowedOriginPatterns = explode(',', env('CORS_ALLOWED_ORIGIN_PATTERNS', '^https?://localhost(:\\d+)?$,^https?://127\\.0\\.0\\.1(:\\d+)?$,^exp://.*$'));
$allowedOriginPatterns = array_values(array_filter(array_map('trim', $allowedOriginPatterns)));

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie' ,'register', 'login', 'payment/callback', 'storage/*' ],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => $allowedOriginPatterns,

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
