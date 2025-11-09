<?php

namespace App\Enum;

trait EnumTrait
{
    public static function array()
    {
        return collect(self::cases())->map(function ($type) {
            return [
                'value' => $type->value,
                'label' => method_exists($type, 'label') ? $type->label() : $type->name,
            ];
        });
    }

    public static function options()
    {
        return collect(self::cases())->mapWithKeys(function ($type) {
            $label = method_exists($type, 'label') ? $type->label() : $type->name;
            return [$type->value => $label];
        });
    }
}
