<?php

namespace App\Enum\Customer;

use App\Enum\EnumTrait;

enum CustomerSupportTypeEnum: string
{
    use EnumTrait;

    case STANDARD = 'standard';
    case PREMIUM = 'premium';
    case BUSINESS = 'business';
    case ENTERPRISE = 'enterprise';

    public function label(): string
    {
        return match ($this) {
            self::STANDARD => 'Standard',
            self::PREMIUM => 'Premium',
            self::BUSINESS => 'Business',
            self::ENTERPRISE => 'Enterprise',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::STANDARD => 'info',
            self::PREMIUM => 'success',
            self::BUSINESS => 'warning',
            self::ENTERPRISE => 'error',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::STANDARD => 'bi bi-people',
            self::PREMIUM => 'bi bi-people-fill',
            self::BUSINESS => 'bi bi-people-fill',
            self::ENTERPRISE => 'bi bi-people-fill',
        };
    }
}
