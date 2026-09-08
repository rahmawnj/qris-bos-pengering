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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'xendit' => [
        'api_key' => env('XENDIT_API_KEY'),
        'callback_token' => env('XENDIT_CALLBACK_TOKEN'),
        'fee_rate' => (float) env('XENDIT_QRIS_FEE_RATE', 0.007),
    ],

    'midtrans' => [
        'environment' => env('MIDTRANS_ENVIRONMENT', 'sandbox'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'callback_url' => env('MIDTRANS_CALLBACK_URL'),
        'fee_rate' => (float) env('MIDTRANS_QRIS_FEE_RATE', 0.007),
    ],

    'midtrans_partner' => [
        'environment' => env('MIDTRANS_PARTNER_ENVIRONMENT', 'sandbox'),
        'server_key' => env('MIDTRANS_PARTNER_SERVER_KEY'),
        'partner_id' => env('MIDTRANS_PARTNER_ID'),
        'merchant_id' => env('MIDTRANS_PARTNER_MERCHANT_ID'),
    ],


];
