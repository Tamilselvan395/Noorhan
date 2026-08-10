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
    'audit' => [
        'retention_days' => (int) env('NOORHAN_AUDIT_RETENTION', 365),
        'activity_retention_days' => (int) env('NOORHAN_ACTIVITY_RETENTION', 180),

        // Canonical list of audited models (drives UI filters & integrity checks)
        'audited_models' => [
            \App\Models\User::class,
            \App\Models\Lead::class,
            \App\Models\Customer::class,
            \App\Models\Company::class,
            \App\Models\Supplier::class,
            \App\Models\Product::class,
            \App\Models\Quotation::class,
            \App\Models\SalesOrder::class,
            \App\Models\Invoice::class,
            \App\Models\Payment::class,
            \App\Models\SupplierEnquiry::class,
            \App\Models\MarketingCampaign::class,
        ],
    ],
    'scheduler' => [
        'tasks' => [
            ['key' => 'digest',       'label' => 'Notification Digest',            'command' => 'notifications:digest',  'frequency' => 'Daily 08:00'],
            ['key' => 'wa_automations','label' => 'WhatsApp Automations',          'command' => 'whatsapp:automations',  'frequency' => 'Daily 09:00'],
            ['key' => 'wa_campaigns', 'label' => 'Scheduled WhatsApp Campaigns',   'command' => 'whatsapp:campaigns',    'frequency' => 'Every 5 minutes'],
            ['key' => 'qtn_expire',   'label' => 'Expire Quotations',              'command' => 'quotations:expire',     'frequency' => 'Daily 01:00'],
            ['key' => 'zoho_retry',   'label' => 'Retry Failed Zoho Syncs',        'command' => 'zoho:retry-failed',     'frequency' => 'Every 30 minutes'],
            ['key' => 'prune',        'label' => 'Prune System Logs',              'command' => 'system:prune-logs',     'frequency' => 'Monthly (1st, 02:00)'],

            ['key' => 'ai_scores',   'label' => 'AI Score Computation', 'command' => 'ai:compute-scores', 'frequency' => 'Daily 06:00'],
            ['key' => 'ai_briefing', 'label' => 'AI Daily Briefing',    'command' => 'ai:briefing',        'frequency' => 'Daily 07:00'],
        
        ],
    ],
    'ai' => [
        'provider' => env('AI_PROVIDER', 'deterministic'), // deterministic|openai
        'openai' => [
            'key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        ],
        'thresholds' => [
            'churn_high' => 65,
            'churn_medium' => 35,
            'health_good' => 70,
        ],
    ],
    
];