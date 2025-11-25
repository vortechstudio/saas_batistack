<?php

namespace App\Trait\Product;

use App\Enum\Product\ProductCategoryEnum;
use App\Enum\Product\ProductPriceFrequencyEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;

trait ProductSchema
{
    public function getSchemaProduct(): array
    {
        return [
            Wizard::make()
                ->schema([
                    Wizard\Step::make('Produit')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('name')
                                        ->label("Désignation du produit")
                                        ->required(),

                                    Select::make('category')
                                        ->label("Catégorie")
                                        ->live()
                                        ->options(ProductCategoryEnum::options())
                                        ->required(),

                                    Checkbox::make('active')
                                        ->label("Activer le produit")
                                        ->default(true),
                                ]),

                            Textarea::make('description')
                                ->label("Description du produit"),

                            FileUpload::make('image')
                                ->required()
                                ->label('Icone')
                                ->disk('public')
                                ->visibility('public')
                                ->directory(fn (Get $get) => match($get('category')) {
                                    'license' => 'product',
                                    'module' => 'modules',
                                    'option' => 'options',
                                    'support' => 'support',
                                })
                                ->imageEditor()
                        ]),

                    Wizard\Step::make('Tarifications')
                        ->schema([
                            Repeater::make('tarifs')
                                ->schema([
                                    Grid::make()
                                        ->schema([
                                            Select::make('frequency')
                                                ->label("Fréquence")
                                                ->options(ProductPriceFrequencyEnum::options())
                                                ->required(),

                                            TextInput::make('price')
                                                ->label("Prix")
                                                ->required(),
                                        ])
                                ])
                        ]),

                    Wizard\Step::make('Fonctionnalités')
                        ->visible(fn (Get $get) => $get('category') === ProductCategoryEnum::LICENSE->value)
                        ->schema([]),
                ])
        ];
    }
}
