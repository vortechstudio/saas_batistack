<?php

namespace App\Filament\Widgets;

use App\Models\License;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiringLicensesWidget extends BaseWidget
{
    protected static ?string $heading = 'Licences à Renouveler';
    protected static ?string $description = 'Licences expirant dans les 30 prochains jours';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                License::query()
                    ->with(['customer', 'product'])
                    ->where('expires_at', '>', now())
                    ->where('expires_at', '<=', now()->addDays(30))
                    ->orderBy('expires_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('customer.company_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produit')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expire le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($state) => $state->diffInDays() <= 7 ? 'danger' : 'warning')
                    ->description(fn ($record) => 
                        'Dans ' . $record->expires_at->diffForHumans(null, true)
                    ),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->color(fn ($state) => $state->color()),
                    
                Tables\Columns\TextColumn::make('current_users')
                    ->label('Utilisateurs')
                    ->description(fn ($record) => $record->max_users ? 
                        "/ {$record->max_users} max" : 'Illimité'
                    ),
            ])
            ->defaultSort('expires_at')
            ->paginated(false)
            ->emptyStateHeading('Aucune licence à renouveler')
            ->emptyStateDescription('Toutes les licences sont valides pour plus de 30 jours.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
