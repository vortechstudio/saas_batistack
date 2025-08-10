<?php

namespace App\Filament\Resources\Modules\Tables;

use App\Enums\ModuleCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ModulesTable
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

                TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label())
                    ->color(fn ($state) => match($state) {
                        ModuleCategory::CORE => 'danger',
                        ModuleCategory::ADVANCED => 'success',
                        ModuleCategory::PREMIUM => 'warning',
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

                TextColumn::make('base_price')
                    ->label('Prix')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

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
                SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options(ModuleCategory::class),

                TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->placeholder('Tous les modules')
                    ->trueLabel('Modules actifs')
                    ->falseLabel('Modules inactifs'),

                SelectFilter::make('base_price')
                    ->label('Type de prix')
                    ->options([
                        '0' => 'Gratuit',
                        '>0' => 'Payant',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value'] === '0') {
                            return $query->where('base_price', 0);
                        } elseif ($data['value'] === '>0') {
                            return $query->where('base_price', '>', 0);
                        }
                        return $query;
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
            ->defaultSort('sort_order', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
