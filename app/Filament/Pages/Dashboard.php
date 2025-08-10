<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\LicenseStatusWidget;
use App\Filament\Widgets\PopularProductsWidget;
use App\Filament\Widgets\RecentLicensesWidget;
use App\Filament\Widgets\ExpiringLicensesWidget;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Tableau de Bord';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 1;

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            RevenueChartWidget::class,
            LicenseStatusWidget::class,
            PopularProductsWidget::class,
            RecentLicensesWidget::class,
            ExpiringLicensesWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
            'xl' => 4,
        ];
    }
}
