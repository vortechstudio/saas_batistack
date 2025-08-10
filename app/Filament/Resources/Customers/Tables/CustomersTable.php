<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Enums\CustomerStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')
                    ->label('Entreprise')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('contact_name')
                    ->label('Contact')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),
                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->toggleable(),
                TextColumn::make('city')
                    ->label('Ville')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('country')
                    ->label('Pays')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (CustomerStatus $state): string => $state->label())
                    ->color(fn (CustomerStatus $state): string => $state->color()),
                TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('licenses_count')
                    ->label('Licences')
                    ->counts('licenses')
                    ->badge()
                    ->color('primary'),
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
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(CustomerStatus::class)
                    ->native(false),
                SelectFilter::make('country')
                    ->label('Pays')
                    ->options([
                        'FR' => 'France',
                        'BE' => 'Belgique',
                        'CH' => 'Suisse',
                        'CA' => 'Canada',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->groupedBulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
