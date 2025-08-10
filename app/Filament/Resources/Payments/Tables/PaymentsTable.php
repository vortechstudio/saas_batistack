<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                
                TextColumn::make('customer.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('invoice.invoice_number')
                    ->label('Facture')
                    ->searchable()
                    ->placeholder('Aucune'),
                
                TextColumn::make('amount')
                    ->label('Montant')
                    ->money('EUR')
                    ->sortable(),
                
                BadgeColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(function ($state) {
                        return match ($state) {
                            PaymentStatus::PENDING => 'warning',
                            PaymentStatus::PROCESSING => 'info',
                            PaymentStatus::SUCCEEDED => 'success',
                            PaymentStatus::FAILED => 'danger',
                            PaymentStatus::CANCELLED => 'gray',
                            PaymentStatus::REFUNDED => 'info',
                            PaymentStatus::PARTIALLY_REFUNDED => 'warning',
                            default => 'gray',
                        };
                    }),
                
                IconColumn::make('payment_method')
                    ->label('Méthode')
                    ->icon(fn ($state) => $state?->icon() ?? 'heroicon-o-question-mark-circle')
                    ->tooltip(fn ($state) => $state?->label() ?? 'Inconnue'),
                
                TextColumn::make('processed_at')
                    ->label('Traité le')
                    ->date('d/m/Y H:i')
                    ->placeholder('Non traité'),
                
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(collect(PaymentStatus::cases())->mapWithKeys(fn($case) => [$case->value => $case->label()])),
                
                SelectFilter::make('payment_method')
                    ->label('Méthode de paiement')
                    ->options(collect(PaymentMethod::cases())->mapWithKeys(fn($case) => [$case->value => $case->label()])),
                
                SelectFilter::make('customer_id')
                    ->label('Client')
                    ->relationship('customer', 'name')
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
