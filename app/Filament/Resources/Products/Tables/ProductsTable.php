<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\BillingCycle;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable()
                    ->copyable()
                    ->copyMessage('Slug copié!')
                    ->copyMessageDuration(1500),

                TextColumn::make('base_price')
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

                TextColumn::make('max_users')
                    ->label('Utilisateurs')
                    ->numeric()
                    ->sortable()
                    ->placeholder('Illimité')
                    ->alignCenter(),

                TextColumn::make('max_projects')
                    ->label('Projets')
                    ->numeric()
                    ->sortable()
                    ->placeholder('Illimité')
                    ->alignCenter(),

                TextColumn::make('storage_limit')
                    ->label('Stockage')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' GB' : 'Illimité')
                    ->sortable()
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                IconColumn::make('is_featured')
                    ->label('Vedette')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                TextColumn::make('modules_count')
                    ->label('Modules')
                    ->counts('modules')
                    ->badge()
                    ->color('info'),

                TextColumn::make('options_count')
                    ->label('Options')
                    ->counts('options')
                    ->badge()
                    ->color('success'),

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
                SelectFilter::make('billing_cycle')
                    ->label('Cycle de facturation')
                    ->options(BillingCycle::class),

                TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->placeholder('Tous les produits')
                    ->trueLabel('Produits actifs')
                    ->falseLabel('Produits inactifs'),

                TernaryFilter::make('is_featured')
                    ->label('Mise en avant')
                    ->placeholder('Tous les produits')
                    ->trueLabel('Produits en vedette')
                    ->falseLabel('Produits normaux'),
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
