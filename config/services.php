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

    'digital_signature' => [
        'url' => env('PERURI_BASE_URL'),
        'key' => env('PERURI_API_KEY'),
        'system_id' => env('PERURI_SYSTEM_ID'),
    ],

    'ocr' => [
        'api_key' => env('OCR_API_KEY'),
    ],

    'tesseract' => [
        'path' => env('TESSERACT_PATH', '/usr/bin/tesseract'),
        'lang' => env('TESSERACT_LANG', 'ind'),
    ],

];
