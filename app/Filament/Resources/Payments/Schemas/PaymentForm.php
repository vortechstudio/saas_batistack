<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Invoice;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Client')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->required(),

                Select::make('invoice_id')
                    ->label('Facture')
                    ->relationship('invoice', 'invoice_number')
                    ->searchable()
                    ->nullable(),

                TextInput::make('amount')
                    ->label('Montant')
                    ->numeric()
                    ->step(0.01)
                    ->required(),

                TextInput::make('currency')
                    ->label('Devise')
                    ->default('EUR')
                    ->required(),

                Select::make('payment_method')
                    ->label('Méthode de paiement')
                    ->options(collect(PaymentMethod::cases())->mapWithKeys(fn($case) => [$case->value => $case->label()]))
                    ->required(),

                Select::make('status')
                    ->label('Statut')
                    ->options(collect(PaymentStatus::cases())->mapWithKeys(fn($case) => [$case->value => $case->label()]))
                    ->required(),

                TextInput::make('stripe_payment_intent_id')
                    ->label('ID Stripe Payment Intent')
                    ->nullable(),

                TextInput::make('stripe_charge_id')
                    ->label('ID Stripe Charge')
                    ->nullable(),

                DateTimePicker::make('processed_at')
                    ->label('Traité le')
                    ->nullable(),

                TextInput::make('failure_reason')
                    ->label('Raison de l\'échec')
                    ->nullable(),

                Textarea::make('metadata')
                    ->label('Métadonnées')
                    ->nullable()
                    ->json(),
            ]);
    }
}
