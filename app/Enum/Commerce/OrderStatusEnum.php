<?php

namespace App\Enum\Commerce;

use App\Enum\EnumTrait;

enum OrderStatusEnum: string
{
    use EnumTrait;
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::CONFIRMED => 'Confirmée',
            self::PROCESSING => 'En cours de traitement',
            self::DELIVERED => 'Livrée',
            self::CANCELLED => 'Annulée',
            self::REFUNDED => 'Remboursée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'info',
            self::PROCESSING => 'primary',
            self::DELIVERED => 'success',
            self::CANCELLED => 'error',
            self::REFUNDED => 'warning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'o-clock',
            self::CONFIRMED => 'o-check',
            self::PROCESSING => 'o-cog',
            self::DELIVERED => 'o-check-circle',
            self::CANCELLED => 'o-x-circle',
            self::REFUNDED => 'o-arrow-left',
        };
    }
}
