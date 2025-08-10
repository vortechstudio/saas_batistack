<?php

namespace App\Filament\Resources\Licenses\Schemas;

use App\Enums\LicenseStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LicenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'id')
                    ->required(),
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                TextInput::make('license_key')
                    ->required(),
                Select::make('status')
                    ->options(LicenseStatus::class)
                    ->default('active')
                    ->required(),
                DateTimePicker::make('starts_at')
                    ->required(),
                DateTimePicker::make('expires_at'),
                TextInput::make('max_users')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('current_users')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('last_used_at'),
            ]);
    }
}
