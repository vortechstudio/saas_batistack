<?php

namespace App\Livewire\Dashboard;

use App\Models\Customer\CustomerService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LatestServiceWidget extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => CustomerService::where('customer_id', Auth::user()->customer->id)->limit(4))
            ->heading("Derniers services")
            ->emptyStateHeading("Aucun service définie")
            ->emptyStateActions([
                Action::make('create_order')
                    ->label('Passer commande')
                    ->icon(Heroicon::ShoppingCart)
                    ->button()
                    ->url(route('client.dashboard'))
            ])
            ->paginated(false)
            ->columns([
                TextColumn::make('reference')
                    ->label(''),
                TextColumn::make('order.reference')
                    ->label(''),
                TextColumn::make('payment_method')
                    ->badge()
                    ->label(''),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'failed' => 'danger',
                        'pending' => 'warning',
                        'processing' => 'info',
                        'cancelled' => 'gray',
                        'refunded' => 'warning',
                        'partially_refunded' => 'warning',
                        default => 'gray',
                    })
                    ->label(''),
                TextColumn::make('amount')
                    ->money('EUR')
                    ->label(''),
                TextColumn::make('processed_at')
                    ->dateTime()
                    ->label(''),
            ])
            ->headerActions([
                Action::make('view_all')
                    ->label('Voir tout')
                    ->iconPosition(IconPosition::After)
                    ->icon(Heroicon::ArrowSmallRight)
                    ->url(route('client.dashboard'))
                    ->button()
                    ->outlined()
                    ->color('bg-blue-800'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
