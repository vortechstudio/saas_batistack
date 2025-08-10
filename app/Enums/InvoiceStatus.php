<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::PENDING => 'En attente',
            self::PAID => 'Payée',
            self::OVERDUE => 'En retard',
            self::CANCELLED => 'Annulée',
            self::REFUNDED => 'Remboursée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PENDING => 'warning',
            self::PAID => 'success',
            self::OVERDUE => 'danger',
            self::CANCELLED => 'gray',
            self::REFUNDED => 'info',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-o-document',
            self::PENDING => 'heroicon-o-clock',
            self::PAID => 'heroicon-o-check-circle',
            self::OVERDUE => 'heroicon-o-exclamation-triangle',
            self::CANCELLED => 'heroicon-o-x-circle',
            self::REFUNDED => 'heroicon-o-arrow-uturn-left',
        };
    }
}