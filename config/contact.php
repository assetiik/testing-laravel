<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site owner notification settings
    |--------------------------------------------------------------------------
    */
    'owner_email' => env('CONTACT_OWNER_EMAIL', 'owner@example.com'),
    'owner_name' => env('CONTACT_OWNER_NAME', 'Site Owner'),

    /*
    |--------------------------------------------------------------------------
    | File-based rate limiting (per IP)
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'max_attempts' => (int) env('CONTACT_RATE_LIMIT_MAX', 5),
        'decay_seconds' => (int) env('CONTACT_RATE_LIMIT_DECAY', 3600),
        'storage_path' => storage_path('app/private/rate-limits'),
    ],

    /*
    |--------------------------------------------------------------------------
    | File storage paths for contacts & metrics
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'contacts' => storage_path('app/private/contacts/contacts.json'),
        'metrics' => storage_path('app/private/metrics/metrics.json'),
    ],

];
