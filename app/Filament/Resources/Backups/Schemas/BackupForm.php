<?php

namespace App\Filament\Resources\Backups\Schemas;

use App\Enums\BackupStatus;
use App\Enums\BackupType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BackupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label('Type')
                    ->options([
                        BackupType::FULL->value => BackupType::FULL->label(),
                        BackupType::INCREMENTAL->value => BackupType::INCREMENTAL->label(),
                        BackupType::DIFFERENTIAL->value => BackupType::DIFFERENTIAL->label(),
                    ])
                    ->required()
                    ->default(BackupType::FULL->value),
                Select::make('status')
                    ->label('Statut')
                    ->options([
                        BackupStatus::PENDING->value => BackupStatus::PENDING->label(),
                        BackupStatus::RUNNING->value => BackupStatus::RUNNING->label(),
                        BackupStatus::COMPLETED->value => BackupStatus::COMPLETED->label(),
                        BackupStatus::FAILED->value => BackupStatus::FAILED->label(),
                    ])
                    ->required()
                    ->default(BackupStatus::PENDING->value),
                Select::make('storage_driver')
                    ->label('Stockage')
                    ->options([
                        'local' => 'Local',
                        's3' => 'Amazon S3',
                        'ftp' => 'FTP',
                    ])
                    ->required()
                    ->default('local'),
                TextInput::make('file_path')
                    ->default(null),
                TextInput::make('file_size')
                    ->numeric()
                    ->default(null),
                Textarea::make('metadata')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('error_message')
                    ->default(null)
                    ->columnSpanFull(),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('completed_at'),
            ]);
    }
}
