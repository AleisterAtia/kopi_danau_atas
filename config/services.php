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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    // Google AI Studio (Gemini) — auto-translates admin content ID -> EN.
    // No key = feature is off; content simply falls back to Indonesian.
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash-lite'),
    ],

    // Cloudflare Turnstile — captcha on the public registration form.
    // Both keys empty = feature is off; the form submits without a challenge.
    'turnstile' => [
        'sitekey' => env('TURNSTILE_SITE_KEY'),
        'secret' => env('TURNSTILE_SECRET_KEY'),
    ],

    // Fonnte — unofficial WhatsApp gateway, alerts admin phones the moment a
    // booking is paid (see MidtransService::notifyAdminsOfPaymentViaWhatsapp).
    // No token = feature is off.
    'fonnte' => [
        'token' => env('FONNTE_TOKEN'),
    ],

    // USD/IDR display-only rate for English-locale price conversion (see
    // App\Support\Currency). Auto-refreshed daily by exchange-rate:refresh;
    // this fallback is only used before the first successful fetch or if the
    // rate API is down.
    'exchange_rate' => [
        'fallback' => env('EXCHANGE_RATE_FALLBACK', 16000),
    ],

];
