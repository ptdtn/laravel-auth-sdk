<?php

return [
    'base_url' => env('PTDTN_AUTH_URL', 'https://accounts.duniateknologinusantara.com'),
    'clients' => [
        [
            'redirect_url' => env('PTDTN_AUTH_REDIRECT', '/auth/callback'),
            'client_id' => env('PTDTN_AUTH_CLIENT_ID'),
            'secret' => env('PTDTN_AUTH_SECRET'),
            'redirect_base_url' => env('REDIRECT_BASE_URL', 'http://localhost:3000'),
        ],
        // You can add more client here
    ],
];
