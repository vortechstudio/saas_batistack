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
        'port' => env('SSH_PORT', 22),
        'private_key_path' => env('SSH_PRIVATE_KEY_PATH', storage_path('ssh/id_rsa')),
        'password' => env('SSH_PASSWORD'),
        'known_hosts_file' => env('SSH_KNOWN_HOSTS_FILE', storage_path('ssh/known_hosts')),
        'verify_host_key_dns' => env('SSH_VERIFY_HOST_KEY_DNS', false),
    ],

    'api_endpoint' => env('SAAS_API_ENDPOINT'),
];
