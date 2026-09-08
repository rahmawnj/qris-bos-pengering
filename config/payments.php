<?php

return [
    'qris' => [
        'default_provider' => env('QRIS_DEFAULT_PROVIDER', 'xendit'),
        'client_expiry_minutes' => (int) env('QRIS_CLIENT_EXPIRY_MINUTES', 1),
        'supported_providers' => ['xendit', 'midtrans', 'midtrans_partner'],
    ],
];
