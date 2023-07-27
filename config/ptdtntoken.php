<?php

return [
    'base_url' => env('PTDTN_AUTH_URL', 'https://accounts.duniateknologinusantara.com'),
    'redirect_url' => env('PTDTN_AUTH_REDIRECT', 'http://localhost:8001/auth/callback'),
    'client_id' => env('PTDTN_AUTH_CLIENT_ID'),
    'secret' => env('PTDTN_AUTH_SECRET'),
    'redirect_base_url' => env('REDIRECT_BASE_URL', 'http://localhost:3000'),
];
