<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case DATABASE = 'database';
    case EMAIL = 'email';
    case SMS = 'sms';
    case PUSH = 'push';
    case SLACK = 'slack';

    public function label(): string
    {
        return match($this) {
            self::DATABASE => 'Base de données',
            self::EMAIL => 'Email',
            self::SMS => 'SMS',
            self::PUSH => 'Notification push',
            self::SLACK => 'Slack',
        };
    }
}
