<?php

namespace App\Enum\Helpdesk;

use App\Enum\EnumTrait;

enum IncidentStatusEnum: string
{
    use EnumTrait;

    case INVESTIGATING = 'investigating';
    case IDENTIFIED = 'identified';
    case MONITORING = 'monitoring';
    case RESOLVED = 'resolved';

    public function getLabel(): string
    {
        return match ($this) {
            self::INVESTIGATING => 'En investigation',
            self::IDENTIFIED => 'Identifié',
            self::MONITORING => 'Sous surveillance',
            self::RESOLVED => 'Résolu',
        };
    }
}
