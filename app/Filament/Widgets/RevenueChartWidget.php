<?php

namespace App\Filament\Widgets;

use App\Enums\LicenseStatus;
use App\Models\License;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Évolution des Revenus';
    protected ?string $description = 'Revenus mensuels des 12 derniers mois';
    protected static ?int $sort = 2;
    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // Générer les données pour les 12 derniers mois
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');

            $monthlyRevenue = License::join('products', 'licenses.product_id', '=', 'products.id')
                ->where('licenses.status', LicenseStatus::ACTIVE)
                ->whereMonth('licenses.created_at', $date->month)
                ->whereYear('licenses.created_at', $date->year)
                ->sum('products.base_price');

            $data[] = $monthlyRevenue;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenus (€)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return value + " €"; }',
                    ],
                ],
            ],
        ];
    }
}
