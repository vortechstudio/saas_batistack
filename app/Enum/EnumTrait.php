<?php

namespace App\Enum;

trait EnumTrait
{
    public static function array()
    {
        return collect(self::cases())->map(function ($type) {
            return [
                'value' => $type->value,
                'label' => $type->name,
            ];
        });
    }
}
