<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\BillingCycle;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Définissez les informations de base du produit')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nom du produit')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, $set) {
                                        if ($operation !== 'create') {
                                            return;
                                        }
                                        $set('slug', \Illuminate\Support\Str::slug($state));
                                    }),

                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->rules(['alpha_dash'])
                                    ->helperText('URL-friendly version du nom'),
                            ]),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Description détaillée du produit'),
                    ]),

                Section::make('Tarification')
                    ->description('Configurez les prix et la facturation')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('base_price')
                                    ->label('Prix de base (€)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€'),

                                Select::make('billing_cycle')
                                    ->label('Cycle de facturation')
                                    ->options(BillingCycle::class)
                                    ->default(BillingCycle::MONTHLY)
                                    ->required()
                                    ->native(false),
                            ]),

                        TextInput::make('stripe_price_id')
                            ->label('ID Prix Stripe')
                            ->helperText('Identifiant du prix dans Stripe')
                            ->maxLength(255),
                    ]),

                Section::make('Limites et restrictions')
                    ->description('Définissez les limites du produit')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('max_users')
                                    ->label('Utilisateurs max')
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText('Nombre maximum d\'utilisateurs (vide = illimité)'),

                                TextInput::make('max_projects')
                                    ->label('Projets max')
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText('Nombre maximum de projets (vide = illimité)'),

                                TextInput::make('storage_limit')
                                    ->label('Stockage (GB)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(0.1)
                                    ->suffix('GB')
                                    ->helperText('Limite de stockage en GB (vide = illimité)'),
                            ]),
                    ]),

                Section::make('Statut et visibilité')
                    ->description('Contrôlez la disponibilité du produit')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Produit actif')
                                    ->helperText('Le produit est disponible à la vente')
                                    ->default(true),

                                Toggle::make('is_featured')
                                    ->label('Produit mis en avant')
                                    ->helperText('Afficher ce produit en vedette')
                                    ->default(false),
                            ]),
                    ]),
            ]);
    }
}
