<?php

namespace App\Filament\Widgets;

use App\Models\License;
use App\Models\Customer;
use App\Models\ActivityLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class NavigationStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public function getPollingInterval(): ?string
    {
        return '30s';
    }

    protected function getStats(): array
    {
        // Licences qui expirent dans les 30 prochains jours
        $expiringLicenses = License::where('expires_at', '<=', Carbon::now()->addDays(30))
            ->where('expires_at', '>', Carbon::now())
            ->count();

        // Licences expirées
        $expiredLicenses = License::where('expires_at', '<', Carbon::now())
            ->count();

        // Nouveaux clients cette semaine
        $newCustomersThisWeek = Customer::where('created_at', '>=', Carbon::now()->startOfWeek())
            ->count();

        // Activités aujourd'hui
        $activitiesToday = Activity::whereDate('created_at', Carbon::today())
            ->count();

        // Total des notifications
        $totalNotifications = $expiringLicenses + $expiredLicenses + $newCustomersThisWeek + $activitiesToday;

        return [
            Stat::make('Notifications totales', $totalNotifications)
                ->description('Toutes les notifications actives')
                ->descriptionIcon('heroicon-m-bell')
                ->color($totalNotifications > 10 ? 'danger' : ($totalNotifications > 5 ? 'warning' : 'success'))
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Licences à expirer', $expiringLicenses)
                ->description('Dans les 30 prochains jours')
                ->descriptionIcon('heroicon-m-clock')
                ->color($expiringLicenses > 5 ? 'danger' : ($expiringLicenses > 0 ? 'warning' : 'success'))
                ->url(route('filament.admin.resources.licenses.index', ['tableFilters[expires_soon][value]' => true])),

            Stat::make('Licences expirées', $expiredLicenses)
                ->description('Nécessitent un renouvellement')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($expiredLicenses > 0 ? 'danger' : 'success')
                ->url(route('filament.admin.resources.licenses.index', ['tableFilters[expired][value]' => true])),

            Stat::make('Nouveaux clients', $newCustomersThisWeek)
                ->description('Cette semaine')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success')
                ->url(route('filament.admin.resources.customers.index', ['tableFilters[new_this_week][value]' => true])),

            Stat::make('Activités', $activitiesToday)
                ->description("Aujourd'hui")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info')
                ->url(route('filament.admin.resources.activity-logs.index')),
        ];
    }

    protected function getColumns(): int
    {
        return 5;
    }

    public static function canView(): bool
    {
        return Auth::check();
    }
}
