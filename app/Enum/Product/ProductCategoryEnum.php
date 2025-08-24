<?php

namespace App\Enum\Product;

use App\Enum\EnumTrait;

enum ProductCategoryEnum: string
{
    use EnumTrait;

    case LICENSE = 'license';
    case MODULE = 'module';
    case OPTION = 'option';
    case SUPPORT = 'support';

    public function label(): string
    {
        return match ($this) {
            self::LICENSE => 'License',
            self::MODULE => 'Module',
            self::OPTION => 'Option',
            self::SUPPORT => 'Support',
        };
    }
}
