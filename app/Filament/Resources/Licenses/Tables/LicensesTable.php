<?php

namespace App\Filament\Resources\Licenses\Tables;

use App\Enums\LicenseStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LicensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('license_key')
                    ->label('Clé de licence')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Clé copiée!')
                    ->copyMessageDuration(1500)
                    ->fontFamily('mono')
                    ->size('sm'),

                TextColumn::make('customer.company_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->url(fn ($record) => $record->customer ? route('filament.admin.resources.customers.edit', $record->customer) : null),

                TextColumn::make('product.name')
                    ->label('Produit')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->product ? route('filament.admin.resources.products.edit', $record->product) : null),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label())
                    ->color(fn ($state) => match($state) {
                        LicenseStatus::ACTIVE => 'success',
                        LicenseStatus::EXPIRED => 'danger',
                        LicenseStatus::SUSPENDED => 'warning',
                        LicenseStatus::CANCELLED => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('starts_at')
                    ->label('Début')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('expires_at')
                    ->label('Expiration')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Illimitée')
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : null),

                TextColumn::make('usage')
                    ->label('Utilisation')
                    ->formatStateUsing(function ($record) {
                        return $record->current_users . '/' . $record->max_users;
                    })
                    ->badge()
                    ->color(function ($record) {
                        $percentage = ($record->current_users / $record->max_users) * 100;
                        return match(true) {
                            $percentage >= 90 => 'danger',
                            $percentage >= 75 => 'warning',
                            default => 'success',
                        };
                    }),

                TextColumn::make('last_used_at')
                    ->label('Dernière utilisation')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Jamais utilisée')
                    ->toggleable(),

                IconColumn::make('is_expired')
                    ->label('Expirée')
                    ->getStateUsing(fn ($record) => $record->expires_at && $record->expires_at->isPast())
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Modifiée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(LicenseStatus::class),

                SelectFilter::make('customer')
                    ->label('Client')
                    ->relationship('customer', 'company_name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('product')
                    ->label('Produit')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('expired')
                    ->label('Expiration')
                    ->placeholder('Toutes les licences')
                    ->trueLabel('Licences expirées')
                    ->falseLabel('Licences valides')
                    ->queries(
                        true: fn (Builder $query) => $query->where('expires_at', '<', now()),
                        false: fn (Builder $query) => $query->where(function ($query) {
                            $query->whereNull('expires_at')
                                  ->orWhere('expires_at', '>=', now());
                        }),
                    ),

                Filter::make('usage_high')
                    ->label('Utilisation élevée (>80%)')
                    ->query(fn (Builder $query) => $query->whereRaw('current_users / max_users > 0.8')),
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
