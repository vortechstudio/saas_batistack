<?php

namespace App\Enum\Customer;

enum CustomerTypeEnum: string
{
    case ADMINISTRATION = 'administration';
    case ASSOCIATION = 'association';
    case ENTREPRISE = 'entreprise';
    case PARTICULIER = 'particulier';
    case AUTRE = 'autre';

    public static function array()
    {
        return collect(self::cases())->map(function ($type) {
            return [
                'value' => $type->value,
                'label' => $type->name,
            ];
        });
    }

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
