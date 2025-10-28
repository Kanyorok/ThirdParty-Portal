<?php

return [
    'paths' => ['*', 'api/*', 'sanctum/csrf-cookie', 'third-party-auth/*', 'third-parties/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://localhost:8000',
        'http://127.0.0.1:8000',
        'http://172.16.2.16:3001',
        'http://192.168.10.109:3001',
        'http://172.17.40.52',
        'http://172.17.40.52:3001',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => true,
];

// <?php

// return [

//     'paths' => ['api/*', 'sanctum/csrf-cookie'],
//     'allowed_methods' => ['*'],
//     'allowed_origins' => ['http://localhost:3000'],
//     'allowed_origins_patterns' => [],
//     'allowed_headers' => ['*'],
//     'exposed_headers' => [],
//     'max_age' => 0,
//     'supports_credentials' => true,
// ];
