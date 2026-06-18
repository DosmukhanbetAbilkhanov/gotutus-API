<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mobizon' => [
        'api_key' => env('MOBIZON_API_KEY'),
        'api_url' => env('MOBIZON_API_URL', 'https://api.mobizon.kz/service'),
        'sender_name' => env('MOBIZON_SENDER_NAME'),
        'admin_email' => env('MOBIZON_ADMIN_EMAIL'),
        // system:health-check alerts when the balance drops below this (account currency, e.g. KZT).
        'low_balance_threshold' => env('MOBIZON_LOW_BALANCE_THRESHOLD', 500),
    ],

    'fcm' => [
        'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),
    ],

    'health' => [
        // Where system:health-check sends alerts when Reverb/FCM/queue break.
        'alert_email' => env('HEALTH_ALERT_EMAIL', env('MOBIZON_ADMIN_EMAIL', 'administrator@tanys.app')),
    ],

];
