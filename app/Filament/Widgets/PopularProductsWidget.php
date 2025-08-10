<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\License;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PopularProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'Produits Populaires';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->withCount('licenses')
                    ->orderBy('licenses_count', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Produit')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('base_price')
                    ->label('Prix')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('billing_cycle')
                    ->label('Cycle')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => match($state->value) {
                        'monthly' => 'success',
                        'yearly' => 'warning',
                        'lifetime' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('licenses_count')
                    ->label('Licences')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Vedette')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('licenses_count', 'desc')
            ->paginated(false);
    }
}
