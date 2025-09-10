<?php

return [
    'domain' => env('APP_DOMAIN'),
    'aapanel' => [
        'api_key' => env('AAPANEL_API_KEY'),
        'endpoint' => env('AAPANEL_ENDPOINT'),
    ],
    'nebulo' => [
        'api_key' => env('NEBULO_API_KEY'),
        'endpoint' => env('NEBULO_ENDPOINT'),
    ],

    'ssh' => [
        'host' => env('SSH_HOST'),
        'user' => env('SSH_USER'),
        'private_key_path' => env('SSH_PRIVATE_KEY_PATH', storage_path('ssh/id_rsa')),
        'password' => env('SSH_PASSWORD'),
    ],

    'api_endpoint' => env('SAAS_API_ENDPOINT'),
];
