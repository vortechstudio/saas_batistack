<?php

namespace App\Enum\Helpdesk;

use App\Enum\EnumTrait;

enum ComponentStatusEnum: string
{
    use EnumTrait;

    case OPERATIONAL = 'operational';
    case PERFORMANCE_ISSUES = 'performance_issues';
    case PARTIAL_OUTAGE = 'partial_outage';
    case MAJOR_OUTAGE = 'major_outage';
    case MAINTENANCE = 'maintenance';

    public function getLabel(): string
    {
        return match ($this) {
            self::OPERATIONAL => 'Opérationnel',
            self::PERFORMANCE_ISSUES => 'Performances dégradées',
            self::PARTIAL_OUTAGE => 'Panne partielle',
            self::MAJOR_OUTAGE => 'Panne majeure',
            self::MAINTENANCE => 'Maintenance',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::OPERATIONAL => 'success',
            self::PERFORMANCE_ISSUES => 'info',
            self::PARTIAL_OUTAGE => 'warning',
            self::MAJOR_OUTAGE => 'danger',
            self::MAINTENANCE => 'gray',
        };
    }
}
