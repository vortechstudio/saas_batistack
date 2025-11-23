<?php

namespace App\Enum\Helpdesk;

use App\Enum\EnumTrait;

enum TicketStatusEnum: string
{
    use EnumTrait;
    case OPEN = 'open';
    case PENDING = 'pending'; // En attente de réponse client ou support
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    public function getLabel(): string
    {
        return match ($this) {
            self::OPEN => 'Ouvert',
            self::PENDING => 'En attente',
            self::RESOLVED => 'Résolu',
            self::CLOSED => 'Fermé',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::OPEN => 'success',
            self::PENDING => 'warning',
            self::RESOLVED => 'info',
            self::CLOSED => 'gray',
        };
    }
}
