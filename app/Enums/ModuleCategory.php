<?php

namespace App\Enums;

enum ModuleCategory: string
{
    case CORE = 'core';
    case ADVANCED = 'advanced';
    case PREMIUM = 'premium';

    public function label(): string
    {
        return match($this) {
            self::CORE => 'Essentiel',
            self::ADVANCED => 'Avancé',
            self::PREMIUM => 'Premium',
        };
    }
}