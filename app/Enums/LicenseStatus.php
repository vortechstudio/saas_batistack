<?php

namespace App\Enums;

enum LicenseStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case SUSPENDED = 'suspended';
    case CANCELLED = 'cancelled';
    case SAVED = 'saved';
    case PENDING = 'pending';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::EXPIRED => 'Expirée',
            self::SUSPENDED => 'Suspendue',
            self::CANCELLED => 'Annulée',
            self::SAVED => 'Sauvé',
            self::PENDING => 'En attente',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ACTIVE => 'green',
            self::EXPIRED => 'red',
            self::SUSPENDED, self::PENDING => 'amber',
            self::CANCELLED => 'gray',
            self::SAVED => 'blue',
        };
    }
}
