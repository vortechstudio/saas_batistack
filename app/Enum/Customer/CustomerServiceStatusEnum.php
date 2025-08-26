<?php

namespace App\Enum\Customer;

use App\Enum\EnumTrait;

enum CustomerServiceStatusEnum: string
{
    use EnumTrait;

    case EXPIRED = 'expired';
    case OK = 'ok';
    case PENDING = 'pending';
    case UNPAID = 'unpaid';

    public function label(): string
    {
        return match ($this) {
            self::EXPIRED => 'Expiré',
            self::OK => 'Actif',
            self::PENDING => 'En attente',
            self::UNPAID => 'Non payé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EXPIRED => 'danger',
            self::OK => 'success',
            self::PENDING => 'warning',
            self::UNPAID => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::EXPIRED => 'o-x-circle',
            self::OK => 'o-check-circle',
            self::PENDING => 'o-stop',
            self::UNPAID => 'o-exclamation-circle',
        };
    }
}
