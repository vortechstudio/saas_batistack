<?php

namespace App\Filament\Resources\Modules\Schemas;

use App\Enums\ModuleCategory;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ModuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations du module')
                    ->description('Définissez les informations de base du module')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('key')
                                    ->label('Clé unique')
                                    ->required()
                                    ->maxLength(100)
                                    ->unique(ignoreRecord: true)
                                    ->rules(['alpha_dash'])
                                    ->helperText('Identifiant unique du module (ex: user_management)')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set) {
                                        $set('key', \Illuminate\Support\Str::slug($state, '_'));
                                    }),

                                TextInput::make('name')
                                    ->label('Nom du module')
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
                            ->helperText('Description détaillée des fonctionnalités du module'),
                    ]),

                Section::make('Catégorisation et tarification')
                    ->description('Configurez la catégorie et le prix du module')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('category')
                                    ->label('Catégorie')
                                    ->options(ModuleCategory::class)
                                    ->default(ModuleCategory::CORE)
                                    ->required()
                                    ->native(false)
                                    ->helperText('Catégorie fonctionnelle du module'),

                                TextInput::make('base_price')
                                    ->label('Prix de base (€)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0.00)
                                    ->prefix('€')
                                    ->helperText('Prix additionnel du module'),
                            ]),
                    ]),

                Section::make('Configuration et statut')
                    ->description('Paramètres d\'affichage et de disponibilité')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('sort_order')
                                    ->label('Ordre d\'affichage')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Ordre d\'affichage dans les listes (0 = premier)'),

                                Toggle::make('is_active')
                                    ->label('Module actif')
                                    ->helperText('Le module est disponible pour les produits')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}
