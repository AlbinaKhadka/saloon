<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'user',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:8000',
        'http://127.0.0.1:3000',
        'https://lumina-salon-weld.vercel.app',
        'https://lumina-salon-nepal.vercel.app',
        'https://saloon-rx72.onrender.com',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
