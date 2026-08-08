<?php

return [
    'enabled'         => (bool) env('ZOHO_ENABLED', false),
    'client_id'       => env('ZOHO_CLIENT_ID'),
    'client_secret'   => env('ZOHO_CLIENT_SECRET'),
    'organization_id' => env('ZOHO_ORGANIZATION_ID'),
    'redirect_uri'    => env('ZOHO_REDIRECT_URI', config('app.url').'/zoho/callback'),
    'accounts_url'    => env('ZOHO_ACCOUNTS_URL', 'https://accounts.zoho.com'),
    'api_url'         => env('ZOHO_API_URL', 'https://www.zohoapis.com/books/v3'),
    'webhook_secret'  => env('ZOHO_WEBHOOK_SECRET'),

    // Optional Zoho sales-tax IDs per division (applied to line items when set)
    'sales_tax_ids' => [
        'automotive' => env('ZOHO_TAX_AUTOMOTIVE'),
        'swiftec'    => env('ZOHO_TAX_SWIFTEC'),
        'wiperex'    => env('ZOHO_TAX_WIPEREX'),
        'otozaar'    => env('ZOHO_TAX_OTOZAAR'),
    ],
];