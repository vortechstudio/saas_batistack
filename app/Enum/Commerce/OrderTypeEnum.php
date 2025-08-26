<?php

namespace App\Enum\Commerce;

use App\Enum\EnumTrait;

enum OrderTypeEnum: string
{
    use EnumTrait;
    case PURCHASE = 'purchase';
    case SUBSCRIPTION = 'subscription';
    case RENEWAL = 'renewal';
    case UPGRADE = 'upgrade';

    public function label(): string
    {
        return match ($this) {
            self::PURCHASE => 'Purchase',
            self::SUBSCRIPTION => 'Subscription',
            self::RENEWAL => 'Renewal',
            self::UPGRADE => 'Upgrade',
        };
    }
}
