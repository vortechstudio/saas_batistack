<?php

namespace App\Enum\Helpdesk;

use App\Enum\EnumTrait;

enum TicketCategoryEnum: string
{
    use EnumTrait;

    case TECHNICAL = 'technical';
    case COMMERCIAL = 'commercial';

    public function getLabel(): string
    {
        return match($this) {
            self::TECHNICAL => 'Support Technique',
            self::COMMERCIAL => 'Support Commercial',
        };
    }
}
