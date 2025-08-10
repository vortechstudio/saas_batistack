<?php

namespace App\Enums;

enum BackupType: string
{
    case FULL = 'full';
    case INCREMENTAL = 'incremental';
    case DIFFERENTIAL = 'differential';

    public function label(): string
    {
        return match ($this) {
            self::FULL => 'Complète',
            self::INCREMENTAL => 'Incrémentale',
            self::DIFFERENTIAL => 'Différentielle',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::FULL => 'Sauvegarde complète de toutes les données',
            self::INCREMENTAL => 'Sauvegarde des modifications depuis la dernière sauvegarde',
            self::DIFFERENTIAL => 'Sauvegarde des modifications depuis la dernière sauvegarde complète',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::FULL => 'heroicon-o-server-stack',
            self::INCREMENTAL => 'heroicon-o-plus-circle',
            self::DIFFERENTIAL => 'heroicon-o-arrow-up-circle',
        };
    }
}