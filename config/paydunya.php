<?php

return [
    'master_key' => env('PAYDUNYA_MASTER_KEY'),
    'private_key' => env('PAYDUNYA_PRIVATE_KEY'),
    'token' => env('PAYDUNYA_TOKEN'),
    'public_key' => env('PAYDUNYA_PUBLIC_KEY'),
    'mode' => env('PAYDUNYA_MODE', 'sandbox'), // Utilisez "live" pour la production
];
