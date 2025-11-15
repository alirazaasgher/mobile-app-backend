
<?php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000', // Next.js development
        'https://your-nextjs-domain.com', // Production domain
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['X-API-Version', 'X-RateLimit-Remaining'],
    'max_age' => 0,
    'supports_credentials' => false,
];