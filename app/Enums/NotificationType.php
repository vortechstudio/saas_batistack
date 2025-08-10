<?php

namespace App\Enums;

enum NotificationType: string
{
    case LICENSE_EXPIRING = 'license_expiring';
    case LICENSE_EXPIRED = 'license_expired';
    case PAYMENT_OVERDUE = 'payment_overdue';
    case CUSTOMER_INACTIVE = 'customer_inactive';
    case SYSTEM_ALERT = 'system_alert';
    case SECURITY_ALERT = 'security_alert';
    case MAINTENANCE = 'maintenance';
    case NEW_CUSTOMER = 'new_customer';
    case REVENUE_MILESTONE = 'revenue_milestone';

    public function label(): string
    {
        return match($this) {
            self::LICENSE_EXPIRING => 'Licence bientôt expirée',
            self::LICENSE_EXPIRED => 'Licence expirée',
            self::PAYMENT_OVERDUE => 'Paiement en retard',
            self::CUSTOMER_INACTIVE => 'Client inactif',
            self::SYSTEM_ALERT => 'Alerte système',
            self::SECURITY_ALERT => 'Alerte sécurité',
            self::MAINTENANCE => 'Maintenance',
            self::NEW_CUSTOMER => 'Nouveau client',
            self::REVENUE_MILESTONE => 'Objectif de revenus',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::LICENSE_EXPIRING => 'heroicon-o-clock',
            self::LICENSE_EXPIRED => 'heroicon-o-x-circle',
            self::PAYMENT_OVERDUE => 'heroicon-o-currency-euro',
            self::CUSTOMER_INACTIVE => 'heroicon-o-user-minus',
            self::SYSTEM_ALERT => 'heroicon-o-exclamation-triangle',
            self::SECURITY_ALERT => 'heroicon-o-shield-exclamation',
            self::MAINTENANCE => 'heroicon-o-wrench-screwdriver',
            self::NEW_CUSTOMER => 'heroicon-o-user-plus',
            self::REVENUE_MILESTONE => 'heroicon-o-chart-bar',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::LICENSE_EXPIRING => 'warning',
            self::LICENSE_EXPIRED => 'danger',
            self::PAYMENT_OVERDUE => 'danger',
            self::CUSTOMER_INACTIVE => 'gray',
            self::SYSTEM_ALERT => 'warning',
            self::SECURITY_ALERT => 'danger',
            self::MAINTENANCE => 'info',
            self::NEW_CUSTOMER => 'success',
            self::REVENUE_MILESTONE => 'success',
        };
    }

    public function priority(): int
    {
        return match($this) {
            self::SECURITY_ALERT => 1,
            self::SYSTEM_ALERT => 2,
            self::LICENSE_EXPIRED => 3,
            self::PAYMENT_OVERDUE => 4,
            self::LICENSE_EXPIRING => 5,
            self::CUSTOMER_INACTIVE => 6,
            self::MAINTENANCE => 7,
            self::NEW_CUSTOMER => 8,
            self::REVENUE_MILESTONE => 9,
        };
    }
}
