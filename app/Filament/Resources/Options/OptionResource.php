<?php

namespace App\Filament\Resources\Options;

use App\Filament\Resources\Options\Pages\CreateOption;
use App\Filament\Resources\Options\Pages\EditOption;
use App\Filament\Resources\Options\Pages\ListOptions;
use App\Filament\Resources\Options\Schemas\OptionForm;
use App\Filament\Resources\Options\Tables\OptionsTable;
use App\Models\Option;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OptionResource extends Resource
{
    protected static ?string $model = Option::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'Options';

    protected static ?string $modelLabel = 'Option';

    protected static ?string $pluralModelLabel = 'Options';

    protected static ?string $recordTitleAttribute = 'name';


    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return OptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OptionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // Relations avec produits seront ajoutées plus tard
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOptions::route('/'),
            'create' => CreateOption::route('/create'),
            'edit' => EditOption::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['key', 'name', 'description'];
    }
}
