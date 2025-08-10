<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Enums\CustomerStatus;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Informations de base du client')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('company_name')
                                    ->label('Nom de l\'entreprise')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('contact_name')
                                    ->label('Nom du contact')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('email')
                                    ->label('Adresse email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('Téléphone')
                                    ->tel()
                                    ->maxLength(20),
                            ]),
                        Select::make('status')
                            ->label('Statut')
                            ->options(CustomerStatus::class)
                            ->default(CustomerStatus::ACTIVE)
                            ->required()
                            ->native(false),
                    ]),

                Section::make('Adresse')
                    ->description('Informations d\'adresse du client')
                    ->schema([
                        Textarea::make('address')
                            ->label('Adresse')
                            ->rows(3)
                            ->columnSpanFull(),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('city')
                                    ->label('Ville')
                                    ->maxLength(100),
                                TextInput::make('postal_code')
                                    ->label('Code postal')
                                    ->maxLength(10),
                                TextInput::make('country')
                                    ->label('Pays')
                                    ->required()
                                    ->default('FR')
                                    ->maxLength(2),
                            ]),
                    ]),

                Section::make('Informations légales')
                    ->description('Informations fiscales et légales')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('siret')
                                    ->label('SIRET')
                                    ->maxLength(14),
                                TextInput::make('vat_number')
                                    ->label('Numéro de TVA')
                                    ->maxLength(20),
                            ]),
                    ]),

                Section::make('Configuration système')
                    ->description('Paramètres techniques')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('Utilisateur associé')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('stripe_customer_id')
                                    ->label('ID Client Stripe')
                                    ->maxLength(255)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->helperText('Généré automatiquement lors de la création'),
                            ]),
                    ]),
            ]);
    }
}
