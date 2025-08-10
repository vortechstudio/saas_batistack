<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::PROCESSING => 'En cours',
            self::SUCCEEDED => 'Réussi',
            self::FAILED => 'Échoué',
            self::CANCELLED => 'Annulé',
            self::REFUNDED => 'Remboursé',
            self::PARTIALLY_REFUNDED => 'Partiellement remboursé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::SUCCEEDED => 'success',
            self::FAILED => 'danger',
            self::CANCELLED => 'gray',
            self::REFUNDED => 'info',
            self::PARTIALLY_REFUNDED => 'warning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::PROCESSING => 'heroicon-o-arrow-path',
            self::SUCCEEDED => 'heroicon-o-check-circle',
            self::FAILED => 'heroicon-o-x-circle',
            self::CANCELLED => 'heroicon-o-stop',
            self::REFUNDED => 'heroicon-o-arrow-uturn-left',
            self::PARTIALLY_REFUNDED => 'heroicon-o-arrow-uturn-left',
        };
    }
}