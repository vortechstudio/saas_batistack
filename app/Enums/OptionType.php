<?php

namespace App\Enums;

enum OptionType: string
{
    case FEATURE = 'feature';
    case SUPPORT = 'support';
    case STORAGE = 'storage';

    public function label(): string
    {
        return match($this) {
            self::FEATURE => 'Fonctionnalité',
            self::SUPPORT => 'Support',
            self::STORAGE => 'Stockage',
        };
    }
}