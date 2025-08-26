<?php

namespace App\Livewire\Dashboard;

use App\Models\Commerce\OrderPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class TotalInvoiceAmountWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Calculer le montant total des paiements du mois dernier
        $totalAmount = OrderPayment::whereHas('order', function ($query) {
                $query->where('customer_id', Auth::user()->customer->id);
            })
            ->where('status', 'completed')
            ->whereMonth('processed_at', now()->subMonth()->month)
            ->whereYear('processed_at', now()->subMonth()->year)
            ->sum('amount');

        return [
            Stat::make('Montant total des factures', Number::currency($totalAmount / 100, 'EUR'))
                ->description('Le mois dernier')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-blue-500 to-blue-700 text-white',
                ])
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->url(route('client.dashboard')) // Remplacez par votre route
        ];
    }

    protected function getColumns(): int
    {
        return 1;
    }
}