<?php

namespace App\Filament\Resources\Options\Schemas;

use App\Enums\BillingCycle;
use App\Enums\OptionType;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class OptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations de l\'option')
                    ->description('Définissez les informations de base de l\'option')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('key')
                                    ->label('Clé unique')
                                    ->required()
                                    ->maxLength(100)
                                    ->unique(ignoreRecord: true)
                                    ->rules(['alpha_dash'])
                                    ->helperText('Identifiant unique de l\'option (ex: extra_storage)')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set) {
                                        $set('key', \Illuminate\Support\Str::slug($state, '_'));
                                    }),

                                TextInput::make('name')
                                    ->label('Nom de l\'option')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, $set, $get) {
                                        if ($operation !== 'create' || filled($get('key'))) {
                                            return;
                                        }
                                        $set('key', \Illuminate\Support\Str::slug($state, '_'));
                                    }),
                            ]),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Description détaillée de l\'option et de ses bénéfices'),
                    ]),

                Section::make('Type et tarification')
                    ->description('Configurez le type et le prix de l\'option')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('type')
                                    ->label('Type d\'option')
                                    ->options(OptionType::class)
                                    ->default(OptionType::FEATURE)
                                    ->required()
                                    ->native(false)
                                    ->helperText('Catégorie de l\'option'),

                                TextInput::make('price')
                                    ->label('Prix (€)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Prix de l\'option'),

                                Select::make('billing_cycle')
                                    ->label('Cycle de facturation')
                                    ->options(BillingCycle::class)
                                    ->default(BillingCycle::MONTHLY)
                                    ->required()
                                    ->native(false)
                                    ->helperText('Fréquence de facturation'),
                            ]),
                    ]),

                Section::make('Statut et disponibilité')
                    ->description('Contrôlez la disponibilité de l\'option')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Option active')
                            ->helperText('L\'option est disponible pour les produits')
                            ->default(true),
                    ]),
            ]);
    }
}
