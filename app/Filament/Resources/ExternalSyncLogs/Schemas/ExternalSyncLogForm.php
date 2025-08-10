<?php

namespace App\Filament\Resources\ExternalSyncLogs\Schemas;

use App\Enums\SyncStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ExternalSyncLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('system_name')
                    ->label('Système externe')
                    ->options([
                        'crm' => 'CRM',
                        'erp' => 'ERP',
                        'accounting' => 'Comptabilité',
                        'analytics' => 'Analytics',
                    ])
                    ->required(),
                Select::make('operation')
                    ->label('Opération')
                    ->options([
                        'create' => 'Création',
                        'update' => 'Mise à jour',
                        'delete' => 'Suppression',
                        'sync' => 'Synchronisation',
                    ])
                    ->required(),
                Select::make('entity_type')
                    ->label('Type d\'entité')
                    ->options([
                        'customer' => 'Client',
                        'license' => 'Licence',
                        'product' => 'Produit',
                        'user' => 'Utilisateur',
                    ])
                    ->required(),
                TextInput::make('entity_id')
                    ->label('ID de l\'entité')
                    ->numeric()
                    ->default(null),
                Select::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'running' => 'En cours',
                        'success' => 'Succès',
                        'failed' => 'Échec',
                    ])
                    ->required()
                    ->default('pending'),
                Textarea::make('request_data')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('response_data')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('error_message')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('retry_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('last_retry_at'),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('completed_at'),
            ]);
    }
}
