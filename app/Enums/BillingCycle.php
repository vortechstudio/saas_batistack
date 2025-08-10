<?php

namespace App\Enums;

enum BillingCycle: string
{
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
    case ONE_TIME = 'one_time';

    public function label(): string
    {
        return match($this) {
            self::MONTHLY => 'Mensuel',
            self::YEARLY => 'Annuel',
            self::ONE_TIME => 'Unique',
        };
    }

    public function months(): int
    {
        return match($this) {
            self::MONTHLY => 1,
            self::YEARLY => 12,
            self::ONE_TIME => 0,
        };
    }
}
