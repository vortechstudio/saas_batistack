<?php

namespace App\Enum\Helpdesk;

use App\Enum\EnumTrait;

enum TicketPriorityEnum: string
{
    use EnumTrait;

    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public function getLabel(): string
    {
        return match ($this) {
            self::LOW => 'Basse',
            self::MEDIUM => 'Moyenne',
            self::HIGH => 'Haute',
            self::CRITICAL => 'Critique',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::LOW => 'gray',
            self::MEDIUM => 'info',
            self::HIGH => 'warning',
            self::CRITICAL => 'danger',
        };
    }
}
