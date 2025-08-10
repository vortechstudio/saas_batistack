<?php

namespace App\Filament\Resources\ExternalSyncLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExternalSyncLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('system_name')
                    ->label('Système')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'crm' => 'primary',
                        'erp' => 'warning',
                        'analytics' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('operation')
                    ->label('Opération')
                    ->searchable()
                    ->badge(),
                TextColumn::make('entity_type')
                    ->label('Type d\'entité')
                    ->searchable(),
                TextColumn::make('entity_id')
                    ->label('ID Entité')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof \App\Enums\SyncStatus ? $state->label() : $state)
                    ->color(function ($state) {
                        if ($state instanceof \App\Enums\SyncStatus) {
                            return $state->color();
                        }
                        return match ($state) {
                            'pending' => 'gray',
                            'running' => 'warning',
                            'success' => 'success',
                            'failed' => 'danger',
                            default => 'gray',
                        };
                    })
                    ->searchable(),
                TextColumn::make('retry_count')
                    ->label('Tentatives')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_retry_at')
                    ->label('Dernière tentative')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->label('Démarrée le')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Terminée le')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Modifiée le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
