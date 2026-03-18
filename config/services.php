<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'twilio' => [
    'sid'        => env('TWILIO_SID'),
    'token'      => env('TWILIO_AUTH_TOKEN'),
    'verify_sid' => env('TWILIO_VERIFY_SID'),
    ],
    'websocket' => [
    'url'       => env('WEBSOCKET_URL'),
    'token'     => env('WEBSOCKET_TOKEN'),
    ],
    'clover' => [
    'base_url' => env('CLOVER_BASE_URL'),
    'ecom_base_url' => env('CLOVER_ECOM_BASE_URL'),
    'token' => env('CLOVER_TOKEN'),
    'merchant_id' => env('CLOVER_MERCHANT_ID'),
],

    'orders' => [
        'notification_email' => env('ORDER_NOTIFICATION_EMAIL'),
    ],

    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'credentials' => env('FIREBASE_CREDENTIALS'),
        'server_key' => env('FIREBASE_SERVER_KEY'),
    ],



];
