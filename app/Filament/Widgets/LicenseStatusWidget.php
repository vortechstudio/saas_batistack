<?php

namespace App\Filament\Widgets;

use App\Enums\LicenseStatus;
use App\Models\License;
use Filament\Widgets\ChartWidget;

class LicenseStatusWidget extends ChartWidget
{
    protected ?string $heading = 'Répartition des Licences';
    protected ?string $description = 'État actuel de toutes les licences';
    protected static ?int $sort = 3;
    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $active = License::where('status', LicenseStatus::ACTIVE)->count();
        $suspended = License::where('status', LicenseStatus::SUSPENDED)->count();
        $expired = License::where('status', LicenseStatus::EXPIRED)->count();
        $cancelled = License::where('status', LicenseStatus::CANCELLED)->count();

        return [
            'datasets' => [
                [
                    'data' => [$active, $suspended, $expired, $cancelled],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',   // Vert pour actif
                        'rgb(251, 191, 36)',  // Jaune pour suspendu
                        'rgb(239, 68, 68)',   // Rouge pour expiré
                        'rgb(107, 114, 128)', // Gris pour annulé
                    ],
                ],
            ],
            'labels' => ['Actives', 'Suspendues', 'Expirées', 'Annulées'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
