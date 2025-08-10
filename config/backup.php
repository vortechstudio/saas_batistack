<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration des sauvegardes
    |--------------------------------------------------------------------------
    |
    | Configuration pour le système de sauvegarde automatique
    |
    */

    'default_storage' => env('BACKUP_DEFAULT_STORAGE', 'local'),

    'storage_drivers' => [
        'local' => [
            'disk' => 'local',
            'path' => 'backups',
        ],
        's3' => [
            'disk' => 's3',
            'path' => 'backups',
        ],
        'ftp' => [
            'disk' => 'ftp',
            'path' => 'backups',
        ],
    ],

    'database' => [
        'connection' => env('DB_CONNECTION', 'mysql'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'forge'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
    ],

    'mysqldump_path' => env('MYSQLDUMP_PATH', 'mysqldump'),

    'retention' => [
        'full' => env('BACKUP_RETENTION_FULL', 30), // jours
        'incremental' => env('BACKUP_RETENTION_INCREMENTAL', 7), // jours
        'differential' => env('BACKUP_RETENTION_DIFFERENTIAL', 14), // jours
    ],

    'compression' => env('BACKUP_COMPRESSION', true),

    'tables_to_backup' => [
        'users',
        'customers',
        'licenses',
        'products',
        'modules',
        'options',
        'license_modules',
        'license_options',
        'product_modules',
        'product_options',
        'activity_log',
        'notifications',
        'personal_access_tokens',
        'password_reset_tokens',
        'two_factor_authentication_codes',
        'permissions',
        'roles',
        'role_has_permissions',
        'model_has_permissions',
        'model_has_roles',
    ],

    'exclude_tables' => [
        'cache',
        'cache_locks',
        'failed_jobs',
        'job_batches',
        'jobs',
        'sessions',
        'telescope_entries',
        'telescope_entries_tags',
        'telescope_monitoring',
    ],

    'notifications' => [
        'enabled' => env('BACKUP_NOTIFICATIONS_ENABLED', true),
        'channels' => ['mail', 'slack'],
        'mail' => [
            'to' => env('BACKUP_NOTIFICATION_EMAIL', env('MAIL_FROM_ADDRESS')),
        ],
        'slack' => [
            'webhook_url' => env('BACKUP_SLACK_WEBHOOK_URL'),
        ],
    ],
];