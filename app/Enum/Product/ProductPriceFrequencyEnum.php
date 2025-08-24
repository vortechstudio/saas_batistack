<?php

namespace App\Enum\Product;

use App\Enum\EnumTrait;

enum ProductPriceFrequencyEnum: string
{
    use EnumTrait;
    case MONTHLY = 'monthly';
    case ANNUAL = 'annual';
    case UNIQUE = 'unique';

    public function label(): string
    {
        return match ($this) {
            self::MONTHLY => 'Monthly',
            self::ANNUAL => 'Annual',
            self::UNIQUE => 'Unique',
        };
    }

    public function stripeLabel(): string
    {
        return match ($this) {
            self::MONTHLY => 'month',
            self::ANNUAL => 'year',
            self::UNIQUE => 'one_time',
        };
    }
}
