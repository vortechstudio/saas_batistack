<?php

namespace App\Trait\Commerce;

use App\Enum\Customer\CustomerSupportTypeEnum;
use App\Enum\Customer\CustomerTypeEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Illuminate\Support\HtmlString;

trait TiersSchema
{
    public function getSchemaTiers(): array
    {
        return [
            Wizard::make([
                Wizard\Step::make('Identité')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('type_compte')
                                    ->label("Type de client")
                                    ->live()
                                    ->options(CustomerTypeEnum::options()),

                                TextInput::make('entreprise')
                                    ->label("Raison Social")
                                    ->visible(fn (Get $get) => $get('type_compte') !== CustomerTypeEnum::PARTICULIER->value),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('nom')
                                    ->label("Nom de famille")
                                    ->required(),

                                TextInput::make('prenom')
                                    ->label("Prénom")
                                    ->required(),
                            ]),
                    ]),

                Wizard\Step::make('Adresse')
                    ->schema([
                        Textarea::make('adresse')
                            ->label("Adresse postal")
                            ->required(),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('code_postal')
                                    ->label("Code postal")
                                    ->required(),

                                TextInput::make('ville')
                                    ->label("Ville")
                                    ->required(),

                                TextInput::make('pays')
                                    ->label("Pays")
                                    ->required(),
                            ]),
                    ]),

                Wizard\Step::make('Contact')
                    ->schema([
                        TextInput::make('tel')
                            ->label("Téléphone Fixe")
                            ->mask("99 99 99 99 99"),

                        TextInput::make('portable')
                            ->label("Téléphone portable")
                            ->mask("99 99 99 99 99"),

                        TextInput::make('email')
                            ->label("Adresse Mail")
                            ->required()
                            ->email(),
                    ]),

                Wizard\Step::make('Support')
                    ->schema([
                        Select::make('support_type')
                            ->label("Support Technique")
                            ->options(CustomerSupportTypeEnum::options()),
                    ]),
            ])->submitAction(new HtmlString("<button type='submit' class='btn btn-primary'>Valider</button>"))
        ];
    }
}
