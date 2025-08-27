<?php

return [
    'default' => env('PAYMENT_PROVIDER', 'midtrans'),
    
    'providers' => [
        'midtrans' => [
            'server_key' => env('MIDTRANS_SERVER_KEY'),
            'client_key' => env('MIDTRANS_CLIENT_KEY'),
            'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
            'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
            'supported_methods' => [
                'qris' => 'QRIS',
                'gopay' => 'GoPay', 
                'ovo' => 'OVO',
                'dana' => 'DANA',
                'va_bca' => 'BCA Virtual Account',
                'va_bni' => 'BNI Virtual Account',
                'va_bri' => 'BRI Virtual Account',
                'va_mandiri' => 'Mandiri Virtual Account',
            ]
        ],
        
        'xendit' => [
            'secret_key' => env('XENDIT_SECRET_KEY'),
            'public_key' => env('XENDIT_PUBLIC_KEY'),
            'callback_token' => env('XENDIT_CALLBACK_TOKEN'),
            'is_production' => env('XENDIT_IS_PRODUCTION', false),
        ],
        
        'tripay' => [
            'api_key' => env('TRIPAY_API_KEY'),
            'private_key' => env('TRIPAY_PRIVATE_KEY'),
            'merchant_code' => env('TRIPAY_MERCHANT_CODE'),
            'is_production' => env('TRIPAY_IS_PRODUCTION', false),
        ],
    ],

    // Game Provider APIs for real fulfillment
    'game_providers' => [
        'mobile_legends' => [
            'api_url' => env('ML_API_URL'),
            'api_key' => env('ML_API_KEY'),
            'merchant_id' => env('ML_MERCHANT_ID'),
        ],
        'free_fire' => [
            'api_url' => env('FF_API_URL'),
            'api_key' => env('FF_API_KEY'),
            'merchant_id' => env('FF_MERCHANT_ID'),
        ],
    ],
];