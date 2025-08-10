<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration de synchronisation externe
    |--------------------------------------------------------------------------
    |
    | Configuration pour la synchronisation avec les systèmes externes
    |
    */

    'systems' => [
        'crm' => [
            'name' => 'CRM System',
            'enabled' => env('SYNC_CRM_ENABLED', false),
            'base_url' => env('SYNC_CRM_BASE_URL'),
            'api_key' => env('SYNC_CRM_API_KEY'),
            'timeout' => env('SYNC_CRM_TIMEOUT', 30),
            'retry_attempts' => env('SYNC_CRM_RETRY_ATTEMPTS', 3),
            'retry_delay' => env('SYNC_CRM_RETRY_DELAY', 5), // secondes
            'endpoints' => [
                'customers' => [
                    'create' => '/api/customers',
                    'update' => '/api/customers/{id}',
                    'delete' => '/api/customers/{id}',
                    'sync' => '/api/customers/sync',
                ],
            ],
        ],

        'erp' => [
            'name' => 'ERP System',
            'enabled' => env('SYNC_ERP_ENABLED', false),
            'base_url' => env('SYNC_ERP_BASE_URL'),
            'api_key' => env('SYNC_ERP_API_KEY'),
            'timeout' => env('SYNC_ERP_TIMEOUT', 30),
            'retry_attempts' => env('SYNC_ERP_RETRY_ATTEMPTS', 3),
            'retry_delay' => env('SYNC_ERP_RETRY_DELAY', 5),
            'endpoints' => [
                'licenses' => [
                    'create' => '/api/licenses',
                    'update' => '/api/licenses/{id}',
                    'delete' => '/api/licenses/{id}',
                    'sync' => '/api/licenses/sync',
                ],
                'products' => [
                    'create' => '/api/products',
                    'update' => '/api/products/{id}',
                    'delete' => '/api/products/{id}',
                    'sync' => '/api/products/sync',
                ],
            ],
        ],

        'accounting' => [
            'name' => 'Accounting System',
            'enabled' => env('SYNC_ACCOUNTING_ENABLED', false),
            'base_url' => env('SYNC_ACCOUNTING_BASE_URL'),
            'api_key' => env('SYNC_ACCOUNTING_API_KEY'),
            'timeout' => env('SYNC_ACCOUNTING_TIMEOUT', 30),
            'retry_attempts' => env('SYNC_ACCOUNTING_RETRY_ATTEMPTS', 3),
            'retry_delay' => env('SYNC_ACCOUNTING_RETRY_DELAY', 5),
            'endpoints' => [
                'customers' => [
                    'create' => '/api/customers',
                    'update' => '/api/customers/{id}',
                    'delete' => '/api/customers/{id}',
                    'sync' => '/api/customers/sync',
                ],
                'products' => [
                    'create' => '/api/products',
                    'update' => '/api/products/{id}',
                    'delete' => '/api/products/{id}',
                    'sync' => '/api/products/sync',
                ],
            ],
        ],

        'analytics' => [
            'name' => 'Analytics System',
            'enabled' => env('SYNC_ANALYTICS_ENABLED', false),
            'base_url' => env('SYNC_ANALYTICS_BASE_URL'),
            'api_key' => env('SYNC_ANALYTICS_API_KEY'),
            'timeout' => env('SYNC_ANALYTICS_TIMEOUT', 30),
            'retry_attempts' => env('SYNC_ANALYTICS_RETRY_ATTEMPTS', 3),
            'retry_delay' => env('SYNC_ANALYTICS_RETRY_DELAY', 5),
            'endpoints' => [
                'users' => [
                    'create' => '/api/users',
                    'update' => '/api/users/{id}',
                    'delete' => '/api/users/{id}',
                    'sync' => '/api/users/sync',
                ],
                'customers' => [
                    'create' => '/api/customers',
                    'update' => '/api/customers/{id}',
                    'delete' => '/api/customers/{id}',
                    'sync' => '/api/customers/sync',
                ],
                'licenses' => [
                    'create' => '/api/licenses',
                    'update' => '/api/licenses/{id}',
                    'delete' => '/api/licenses/{id}',
                    'sync' => '/api/licenses/sync',
                ],
            ],
        ],
    ],

    'default_timeout' => env('SYNC_DEFAULT_TIMEOUT', 30),
    'default_retry_attempts' => env('SYNC_DEFAULT_RETRY_ATTEMPTS', 3),
    'default_retry_delay' => env('SYNC_DEFAULT_RETRY_DELAY', 5),

    'queue' => [
        'connection' => env('SYNC_QUEUE_CONNECTION', 'database'),
        'queue' => env('SYNC_QUEUE_NAME', 'sync'),
    ],

    'notifications' => [
        'enabled' => env('SYNC_NOTIFICATIONS_ENABLED', true),
        'channels' => ['mail', 'slack'],
        'mail' => [
            'to' => env('SYNC_NOTIFICATION_EMAIL', env('MAIL_FROM_ADDRESS')),
        ],
        'slack' => [
            'webhook_url' => env('SYNC_SLACK_WEBHOOK_URL'),
        ],
    ],

    'logging' => [
        'enabled' => env('SYNC_LOGGING_ENABLED', true),
        'level' => env('SYNC_LOG_LEVEL', 'info'),
        'retention_days' => env('SYNC_LOG_RETENTION_DAYS', 30),
    ],
];