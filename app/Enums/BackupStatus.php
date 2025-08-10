<?php

namespace App\Enums;

enum BackupStatus: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::RUNNING => 'En cours',
            self::COMPLETED => 'Terminée',
            self::FAILED => 'Échouée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::RUNNING => 'warning',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::RUNNING => 'heroicon-o-arrow-path',
            self::COMPLETED => 'heroicon-o-check-circle',
            self::FAILED => 'heroicon-o-x-circle',
        };
    }
}