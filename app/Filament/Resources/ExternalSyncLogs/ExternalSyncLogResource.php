<?php

namespace App\Filament\Resources\ExternalSyncLogs;

use App\Filament\Resources\ExternalSyncLogs\Pages\CreateExternalSyncLog;
use App\Filament\Resources\ExternalSyncLogs\Pages\EditExternalSyncLog;
use App\Filament\Resources\ExternalSyncLogs\Pages\ListExternalSyncLogs;
use App\Filament\Resources\ExternalSyncLogs\Schemas\ExternalSyncLogForm;
use App\Filament\Resources\ExternalSyncLogs\Tables\ExternalSyncLogsTable;
use App\Models\ExternalSyncLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExternalSyncLogResource extends Resource
{
    protected static ?string $model = ExternalSyncLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $recordTitleAttribute = 'system';
    
    protected static ?string $navigationLabel = 'Synchronisations';
    
    protected static ?string $modelLabel = 'Synchronisation';
    
    protected static ?string $pluralModelLabel = 'Synchronisations';

    public static function form(Schema $schema): Schema
    {
        return ExternalSyncLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExternalSyncLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExternalSyncLogs::route('/'),
            'create' => CreateExternalSyncLog::route('/create'),
            'edit' => EditExternalSyncLog::route('/{record}/edit'),
        ];
    }
}
