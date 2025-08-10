<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations de la facture')
                    ->schema([
                        Select::make('customer_id')
                            ->label('Client')
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        TextInput::make('invoice_number')
                            ->label('Numéro de facture')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Select::make('status')
                            ->label('Statut')
                            ->options(collect(InvoiceStatus::cases())->mapWithKeys(fn($case) => [$case->value => $case->label()]))
                            ->default(InvoiceStatus::DRAFT->value)
                            ->required(),

                        DatePicker::make('due_date')
                            ->label('Date d\'échéance')
                            ->required(),

                        TextInput::make('currency')
                            ->label('Devise')
                            ->default('EUR')
                            ->required(),
                    ])->columns(2),

                Section::make('Montants')
                    ->schema([
                        TextInput::make('subtotal_amount')
                            ->label('Sous-total')
                            ->numeric()
                            ->prefix('€')
                            ->required(),

                        TextInput::make('tax_amount')
                            ->label('Montant TVA')
                            ->numeric()
                            ->prefix('€')
                            ->default(0),

                        TextInput::make('total_amount')
                            ->label('Total')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                    ])->columns(3),

                Section::make('Détails')
                    ->schema([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3),
                    ]),

                Section::make('Lignes de facture')
                    ->schema([
                        Repeater::make('invoiceItems')
                            ->relationship()
                            ->schema([
                                TextInput::make('description')
                                    ->label('Description')
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('quantity')
                                    ->label('Quantité')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),

                                TextInput::make('unit_price')
                                    ->label('Prix unitaire')
                                    ->numeric()
                                    ->prefix('€')
                                    ->required(),

                                TextInput::make('total_price')
                                    ->label('Total')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(false),
                            ])
                            ->columns(5)
                            ->defaultItems(1)
                            ->addActionLabel('Ajouter une ligne'),
                    ]),
            ]);
    }
}
