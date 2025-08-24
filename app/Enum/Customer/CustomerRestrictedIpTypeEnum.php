<?php

namespace App\Enum\Customer;

use App\Enum\EnumTrait;

enum CustomerRestrictedIpTypeEnum: string
{
    use EnumTrait;

    case ALLOW = 'allow';
    case DENY = 'deny';

    public function label(): string
    {
        return match ($this) {
            self::ALLOW => 'Autoriser',
            self::DENY => 'Refuser',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ALLOW => 'success',
            self::DENY => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ALLOW => 'check',
            self::DENY => 'times',
        };
    }
}
