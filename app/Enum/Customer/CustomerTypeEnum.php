<?php

namespace App\Enum\Customer;

use App\Enum\EnumTrait;

enum CustomerTypeEnum: string
{
    use EnumTrait;

    case ADMINISTRATION = 'administration';
    case ASSOCIATION = 'association';
    case ENTREPRISE = 'entreprise';
    case PARTICULIER = 'particulier';
    case AUTRE = 'autre';


    public function label(): string
    {
        return match ($this) {
            self::ADMINISTRATION => 'Administration',
            self::ASSOCIATION => 'Association',
            self::ENTREPRISE => 'Entreprise',
            self::PARTICULIER => 'Particulier',
            self::AUTRE => 'Autre',
        };
    }
}
