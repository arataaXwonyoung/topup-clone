<?php

return [
    'default' => env('PAYMENT_PROVIDER', 'midtrans'),
    
    'providers' => [
        'midtrans' => [
            'server_key' => env('MIDTRANS_SERVER_KEY'),
            'client_key' => env('MIDTRANS_CLIENT_KEY'),
            'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
            'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
        ],
        
        'xendit' => [
            'secret_key' => env('XENDIT_SECRET_KEY'),
            'public_key' => env('XENDIT_PUBLIC_KEY'),
            'callback_token' => env('XENDIT_CALLBACK_TOKEN'),
        ],
        
        'tripay' => [
            'api_key' => env('TRIPAY_API_KEY'),
            'private_key' => env('TRIPAY_PRIVATE_KEY'),
            'merchant_code' => env('TRIPAY_MERCHANT_CODE'),
        ],
    ],
];