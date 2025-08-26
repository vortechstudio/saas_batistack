<?php

namespace App\Enum\Commerce;

use App\Enum\EnumTrait;

enum OrderPaymentStatusEnum: string
{
    use EnumTrait;
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::PROCESSING => 'En cours',
            self::COMPLETED => 'Complété',
            self::FAILED => 'Échoué',
            self::CANCELLED => 'Annulé',
            self::REFUNDED => 'Remboursé',
            self::PARTIALLY_REFUNDED => 'Remboursé partiellement',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
            self::CANCELLED => 'danger',
            self::REFUNDED => 'success',
            self::PARTIALLY_REFUNDED => 'info',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'o-clock',
            self::PROCESSING => 'o-arrow-path',
            self::COMPLETED => 'o-check-circle',
            self::FAILED => 'o-x-circle',
            self::CANCELLED => 'o-x-mark',
            self::REFUNDED => 'o-arrow-left',
            self::PARTIALLY_REFUNDED => 'o-arrow-left-circle',
        };
    }

    public function isSuccessful(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isFailed(): bool
    {
        return in_array($this, [self::FAILED, self::CANCELLED]);
    }

    public function isRefunded(): bool
    {
        return in_array($this, [self::REFUNDED, self::PARTIALLY_REFUNDED]);
    }
}
