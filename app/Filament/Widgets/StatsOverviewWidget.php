<?php

namespace App\Filament\Widgets;

use App\Enums\CustomerStatus;
use App\Enums\LicenseStatus;
use App\Models\Customer;
use App\Models\License;
use App\Models\Product;
use App\Models\Module;
use App\Models\Option;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected ?string $pollingInterval = '30s';
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Statistiques des clients
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', CustomerStatus::ACTIVE)->count();
        $inactiveCustomers = $totalCustomers - $activeCustomers;

        // Statistiques des licences
        $totalLicenses = License::count();
        $activeLicenses = License::where('status', LicenseStatus::ACTIVE)->count();
        $expiredLicenses = License::where('expires_at', '<', now())->count();
        $expiringSoon = License::where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addDays(30))
            ->count();

        // Revenus (basé sur les licences actives et leurs produits)
        $monthlyRevenue = License::join('products', 'licenses.product_id', '=', 'products.id')
            ->where('licenses.status', LicenseStatus::ACTIVE)
            ->whereMonth('licenses.created_at', now()->month)
            ->whereYear('licenses.created_at', now()->year)
            ->sum('products.base_price');

        $yearlyRevenue = License::join('products', 'licenses.product_id', '=', 'products.id')
            ->where('licenses.status', LicenseStatus::ACTIVE)
            ->whereYear('licenses.created_at', now()->year)
            ->sum('products.base_price');

        return [
            Stat::make('Clients Actifs', $activeCustomers)
                ->description($inactiveCustomers . ' clients inactifs')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Licences Actives', $activeLicenses)
                ->description($expiredLicenses . ' expirées')
                ->descriptionIcon('heroicon-m-key')
                ->color($expiredLicenses > 0 ? 'warning' : 'success'),

            Stat::make('À Renouveler', $expiringSoon)
                ->description('Dans les 30 prochains jours')
                ->descriptionIcon('heroicon-m-clock')
                ->color($expiringSoon > 0 ? 'danger' : 'success'),

            Stat::make('Revenus Mensuels', number_format($monthlyRevenue, 2) . ' €')
                ->description('Ce mois-ci')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Revenus Annuels', number_format($yearlyRevenue, 2) . ' €')
                ->description('Cette année')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Produits Actifs', Product::where('is_active', true)->count())
                ->description(Product::where('is_active', false)->count() . ' inactifs')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),
        ];
    }
}
