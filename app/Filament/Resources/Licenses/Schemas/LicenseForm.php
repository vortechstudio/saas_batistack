<?php

namespace App\Filament\Resources\Licenses\Schemas;

use App\Enums\LicenseStatus;
use App\Models\Customer;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;

class LicenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Attribution de la licence')
                    ->description('Associez la licence à un client et un produit')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Client')
                                    ->relationship('customer', 'company_name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('company_name')
                                            ->label('Nom de l\'entreprise')
                                            ->required(),
                                        TextInput::make('contact_name')
                                            ->label('Nom du contact')
                                            ->required(),
                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required(),
                                    ])
                                    ->helperText('Sélectionnez le client propriétaire de cette licence'),

                                Select::make('product_id')
                                    ->label('Produit')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Sélectionnez le produit associé à cette licence'),
                            ]),
                    ]),

                Section::make('Informations de la licence')
                    ->description('Configurez les détails de la licence')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('license_key')
                                    ->label('Clé de licence')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->suffixAction(
                                        Action::make('generate')
                                            ->icon('heroicon-o-arrow-path')
                                            ->action(function ($set) {
                                                $set('license_key', 'LIC-' . strtoupper(\Illuminate\Support\Str::random(16)));
                                            })
                                    )
                                    ->helperText('Clé unique d\'identification de la licence'),

                                Select::make('status')
                                    ->label('Statut')
                                    ->options(LicenseStatus::class)
                                    ->default(LicenseStatus::ACTIVE)
                                    ->required()
                                    ->native(false)
                                    ->helperText('Statut actuel de la licence'),
                            ]),
                    ]),

                Section::make('Période de validité')
                    ->description('Définissez la période d\'activité de la licence')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->label('Date de début')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->helperText('Date d\'activation de la licence'),

                                DateTimePicker::make('expires_at')
                                    ->label('Date d\'expiration')
                                    ->native(false)
                                    ->after('starts_at')
                                    ->helperText('Date d\'expiration (vide = illimitée)'),
                            ]),
                    ]),

                Section::make('Limites d\'utilisation')
                    ->description('Configurez les limites d\'usage de la licence')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('max_users')
                                    ->label('Utilisateurs maximum')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->helperText('Nombre maximum d\'utilisateurs autorisés'),

                                TextInput::make('current_users')
                                    ->label('Utilisateurs actuels')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Nombre d\'utilisateurs actuellement actifs'),

                                TextInput::make('usage_percentage')
                                    ->disabled()
                                    ->label('Taux d\'utilisation')
                                    ->formatStateUsing(function ($get) {
                                        $max = $get('max_users') ?: 1;
                                        $current = $get('current_users') ?: 0;
                                        $percentage = round(($current / $max) * 100, 1);
                                        return $percentage . '%';
                                    }),
                            ]),
                    ]),

                Section::make('Informations d\'usage')
                    ->description('Suivi de l\'utilisation de la licence')
                    ->schema([
                        DateTimePicker::make('last_used_at')
                            ->label('Dernière utilisation')
                            ->native(false)
                            ->helperText('Date de la dernière activité enregistrée'),
                    ]),
            ]);
    }
}
