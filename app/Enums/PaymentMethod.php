<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CARD = 'card';
    case SEPA_DEBIT = 'sepa_debit';
    case BANK_TRANSFER = 'bank_transfer';
    case PAYPAL = 'paypal';
    case APPLE_PAY = 'apple_pay';
    case GOOGLE_PAY = 'google_pay';

    public function label(): string
    {
        return match ($this) {
            self::CARD => 'Carte bancaire',
            self::SEPA_DEBIT => 'Prélèvement SEPA',
            self::BANK_TRANSFER => 'Virement bancaire',
            self::PAYPAL => 'PayPal',
            self::APPLE_PAY => 'Apple Pay',
            self::GOOGLE_PAY => 'Google Pay',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CARD => 'heroicon-o-credit-card',
            self::SEPA_DEBIT => 'heroicon-o-building-library',
            self::BANK_TRANSFER => 'heroicon-o-banknotes',
            self::PAYPAL => 'heroicon-o-globe-alt',
            self::APPLE_PAY => 'heroicon-o-device-phone-mobile',
            self::GOOGLE_PAY => 'heroicon-o-device-phone-mobile',
        };
    }
}