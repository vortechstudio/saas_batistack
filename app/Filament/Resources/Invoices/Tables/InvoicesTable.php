<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Enums\InvoiceStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('N° Facture')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(function ($state) {
                        return match ($state) {
                            InvoiceStatus::DRAFT => 'gray',
                            InvoiceStatus::PENDING => 'warning',
                            InvoiceStatus::PAID => 'success',
                            InvoiceStatus::OVERDUE => 'danger',
                            InvoiceStatus::CANCELLED => 'gray',
                            InvoiceStatus::REFUNDED => 'info',
                            default => 'gray',
                        };
                    }),

                TextColumn::make('total_amount')
                    ->label('Montant')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Échéance')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->label('Payée le')
                    ->date('d/m/Y H:i')
                    ->placeholder('Non payée'),

                TextColumn::make('created_at')
                    ->label('Créée le')
                    ->date('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(collect(InvoiceStatus::cases())->mapWithKeys(fn($case) => [$case->value => $case->label()])),

                SelectFilter::make('customer_id')
                    ->label('Client')
                    ->relationship('customer', 'name')
                    ->searchable(),

                Filter::make('overdue')
                    ->label('En retard')
                    ->query(fn (Builder $query): Builder => $query->overdue()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('downloadPdf')
                    ->label('Télécharger PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn ($record) => route('invoice.pdf', $record->id))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }


}
