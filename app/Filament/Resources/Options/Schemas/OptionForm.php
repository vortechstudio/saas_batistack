<?php

namespace App\Filament\Resources\Options\Schemas;

use App\Enums\BillingCycle;
use App\Enums\OptionType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('type')
                    ->options(OptionType::class)
                    ->default('feature')
                    ->required(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Select::make('billing_cycle')
                    ->options(BillingCycle::class)
                    ->default('monthly')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
