<?php

namespace App\Filament\Resources\Options\Tables;

use App\Enums\BillingCycle;
use App\Enums\OptionType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class OptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Clé')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Clé copiée!')
                    ->copyMessageDuration(1500)
                    ->fontFamily('mono')
                    ->size('sm'),

                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label())
                    ->color(fn ($state) => match($state) {
                        OptionType::FEATURE => 'success',
                        OptionType::STORAGE => 'info',
                        OptionType::SUPPORT => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(),

                TextColumn::make('price')
                    ->label('Prix')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('billing_cycle')
                    ->label('Facturation')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label())
                    ->color(fn ($state) => match($state) {
                        BillingCycle::MONTHLY => 'success',
                        BillingCycle::YEARLY => 'warning',
                        BillingCycle::ONE_TIME => 'info',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('products_count')
                    ->label('Produits')
                    ->counts('products')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type d\'option')
                    ->options(OptionType::class),

                SelectFilter::make('billing_cycle')
                    ->label('Cycle de facturation')
                    ->options(BillingCycle::class),

                TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->placeholder('Toutes les options')
                    ->trueLabel('Options actives')
                    ->falseLabel('Options inactives'),

                SelectFilter::make('price_range')
                    ->label('Gamme de prix')
                    ->options([
                        '0' => 'Gratuit',
                        '0-10' => '0€ - 10€',
                        '10-50' => '10€ - 50€',
                        '50+' => '50€+',
                    ])
                    ->query(function ($query, $data) {
                        return match($data['value']) {
                            '0' => $query->where('price', 0),
                            '0-10' => $query->whereBetween('price', [0.01, 10]),
                            '10-50' => $query->whereBetween('price', [10.01, 50]),
                            '50+' => $query->where('price', '>', 50),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
