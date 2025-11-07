<?php

return [
    'public_key_base64' => env('LICENSING_PUBLIC_KEY_BASE64', ''),
    'public_key_id' => env('LICENSING_PUBLIC_KEY_ID', 'vendor-key-1'),
    'cache_ttl_seconds' => env('LICENSING_CACHE_TTL', 300),
    'grace_period_seconds' => env('LICENSING_GRACE_PERIOD', 0),
];

