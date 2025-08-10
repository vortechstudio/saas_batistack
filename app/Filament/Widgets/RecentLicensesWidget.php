<?php

namespace App\Filament\Widgets;

use App\Models\License;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentLicensesWidget extends BaseWidget
{
    protected static ?string $heading = 'Licences Récentes';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                License::query()
                    ->with(['customer', 'product'])
                    ->latest()
                    ->limit(10)
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
                    
                Tables\Columns\TextColumn::make('license_key')
                    ->label('Clé')
                    ->copyable()
                    ->copyMessage('Clé copiée!')
                    ->limit(20),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->color(fn ($state) => $state->color()),
                    
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expire le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : 
                        ($state && $state->diffInDays() <= 30 ? 'warning' : 'success')),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->date('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
