<?php

return [
    'enabled'          => (bool) env('WHATSAPP_ENABLED', false),
    'access_token'     => env('WHATSAPP_ACCESS_TOKEN'),      // Meta Cloud API permanent token
    'phone_number_id'  => env('WHATSAPP_PHONE_NUMBER_ID'),
    'api_version'      => env('WHATSAPP_API_VERSION', 'v19.0'),
    'api_url'          => env('WHATSAPP_API_URL', 'https://graph.facebook.com'),
    'language'         => env('WHATSAPP_LANGUAGE', 'en'),

    // Meta pre-approved template names mapped to CRM scenarios
    'templates' => [
        'welcome'            => env('WA_TPL_WELCOME', 'noorhan_welcome'),
        'follow_up'          => env('WA_TPL_FOLLOW_UP', 'noorhan_follow_up'),
        'quotation_reminder' => env('WA_TPL_QTN_REMINDER', 'noorhan_quotation_reminder'),
        'payment_reminder'   => env('WA_TPL_PAY_REMINDER', 'noorhan_payment_reminder'),
        'service_reminder'   => env('WA_TPL_SERVICE_REM', 'noorhan_service_reminder'),
        'cross_sell'         => env('WA_TPL_CROSS_SELL', 'noorhan_cross_sell'),
        'dormant_reactivation' => env('WA_TPL_DORMANT', 'noorhan_we_miss_you'),
    ],
];