<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Filament\Resources\ActivityLogs\Schemas\ActivityLogForm;
use App\Filament\Resources\ActivityLogs\Tables\ActivityLogsTable;
use BackedEnum;
use Spatie\Activitylog\Models\Activity;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'description';

    protected static ?string $navigationLabel = 'Journal d\'audit';

    protected static ?string $modelLabel = 'Activité';

    protected static ?string $pluralModelLabel = 'Journal d\'audit';

    protected static string | UnitEnum | null $navigationGroup = 'Securité';

    public static function form(Schema $schema): Schema
    {
        return ActivityLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivityLogsTable::configure($table);
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
            'index' => ListActivityLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }
}
