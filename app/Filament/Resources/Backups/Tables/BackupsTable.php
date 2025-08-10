<?php

namespace App\Filament\Resources\Backups\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BackupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof \App\Enums\BackupType ? $state->label() : $state)
                    ->color(function ($state) {
                        $value = $state instanceof \App\Enums\BackupType ? $state->value : $state;
                        return match ($value) {
                            'full' => 'primary',
                            'incremental' => 'warning',
                            'differential' => 'info',
                            default => 'gray',
                        };
                    }),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof \App\Enums\BackupStatus ? $state->label() : $state)
                    ->color(function ($state) {
                        if ($state instanceof \App\Enums\BackupStatus) {
                            return $state->color();
                        }
                        return match ($state) {
                            'pending' => 'gray',
                            'running' => 'warning',
                            'completed' => 'success',
                            'failed' => 'danger',
                            default => 'gray',
                        };
                    }),
                TextColumn::make('storage_driver')
                    ->label('Stockage')
                    ->badge(),
                TextColumn::make('file_size')
                    ->label('Taille')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1024 / 1024, 2) . ' MB' : '-'),
                TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Terminée le')
                    ->dateTime()
                    ->sortable(),
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