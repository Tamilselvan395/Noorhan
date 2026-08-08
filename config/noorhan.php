<?php

return [
    'name' => env('APP_NAME', 'Noorhan Group CRM'),
    'version' => '1.0.0',
    'divisions' => [
        'automotive' => 'Automotive Spare Parts',
        'swiftec' => 'Swiftec Lubricants',
        'wiperex' => 'Wiperex',
        'otozaar' => 'Otozaar Premium Car Service',
    ],
    'theme' => [
        'default' => 'light',
        'primary_color' => '#2563eb', // Tailwind blue-600
    ],
    'pagination' => [
        'default_per_page' => 15,
    ],
    'auth' => [
        'max_failed_attempts' => (int) env('NOORHAN_MAX_FAILED_ATTEMPTS', 5),
        'lockout_minutes'     => (int) env('NOORHAN_LOCKOUT_MINUTES', 15),
        'rate_limit_max'      => (int) env('NOORHAN_LOGIN_RATE_MAX', 10),
        'password_min_length' => (int) env('NOORHAN_PASSWORD_MIN', 8),
        'alert_new_device'    => (bool) env('NOORHAN_ALERT_NEW_DEVICE', true),
        'two_factor' => [
            'enabled'            => (bool) env('NOORHAN_2FA_ENABLED', false),
            'remember_device_days' => (int) env('NOORHAN_2FA_REMEMBER_DAYS', 7),
        ],
    ],
    'capture' => [
        'async_processing'         => (bool) env('NOORHAN_CAPTURE_ASYNC', true),
        'meta_verify_token'        => env('META_VERIFY_TOKEN'),          // FB/IG webhook handshake
        'whatsapp_verify_token'    => env('WHATSAPP_VERIFY_TOKEN'),      // WhatsApp Cloud API handshake
        'google_ads_shared_secret' => env('GOOGLE_ADS_SHARED_SECRET'),   // Google Ads lead webhook
    ],
    'routing' => [
        'auto_route_on_create'   => (bool) env('NOORHAN_AUTO_ROUTE', true),
        'classifier_threshold'   => (float) env('NOORHAN_CLASSIFIER_THRESHOLD', 0.5),
    ],
    'quotation' => [
        'default_valid_days' => (int) env('NOORHAN_QTN_VALID_DAYS', 15),
        'min_margin' => (float) env('NOORHAN_QTN_MIN_MARGIN', 10.0),        // below → requires approval
        'max_discount' => (float) env('NOORHAN_QTN_MAX_DISCOUNT', 5.0),     // above → requires approval
    ],
    
];