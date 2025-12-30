<?php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',
        'https://www.mobile42.com',
        'https://mobile42.com',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'X-XSRF-Token',
        'Accept',
        'X-CLIENT-ID',
        'X-TIMESTAMP',
        'X-NONCE',
        'X-SIGNATURE',
        'x-client-id',
        'x-timestamp',
        'x-nonce',
        'x-signature',
    ],
    'exposed_headers' => ['X-API-Version', 'X-RateLimit-Remaining'],
    'max_age' => 3600,
    'supports_credentials' => false,
];
