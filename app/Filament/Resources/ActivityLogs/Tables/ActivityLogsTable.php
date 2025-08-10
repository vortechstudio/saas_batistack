<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('log_name')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'default' => 'gray',
                        'auth' => 'success',
                        'user' => 'info',
                        'security' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('subject_type')
                    ->label('Modèle')
                    ->formatStateUsing(fn (?string $state): string => 
                        $state ? class_basename($state) : '-'
                    ),

                TextColumn::make('causer.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->default('-'),

                TextColumn::make('event')
                    ->label('Événement')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Type de log')
                    ->options([
                        'default' => 'Défaut',
                        'auth' => 'Authentification',
                        'user' => 'Utilisateur',
                        'security' => 'Sécurité',
                    ]),

                SelectFilter::make('event')
                    ->label('Événement')
                    ->options([
                        'created' => 'Créé',
                        'updated' => 'Modifié',
                        'deleted' => 'Supprimé',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
