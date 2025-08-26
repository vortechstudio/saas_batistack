<?php

namespace App\Livewire\Client\Account;

use App\Models\Customer\Customer;
use App\Services\Stripe\CustomerService;
use App\Services\Stripe\StripeCheckoutService;
use Auth;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Mes factures')]
class Invoice extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->records(fn () => Auth::user()->customer->getListInvoices())
            ->columns([
                TextColumn::make('id')
                    ->label('Référence')
                    ->searchable(isIndividual: true)
                    ->sortable(),

                TextColumn::make('metadata.order_number')
                    ->label('Numéro de commande')
                    ->sortable(),

                TextColumn::make('created')
                    ->label('Date d\'émission')
                    ->sortable()
                    ->date('d/m/Y'),

                TextColumn::make('subtotal')
                    ->label('Montant HT')
                    ->sortable()
                    ->money('EUR', 0, 'fr'),

                TextColumn::make('total')
                    ->label('Montant TTC')
                    ->sortable()
                    ->money('EUR', 0, 'fr'),

                TextColumn::make('amount_due')
                    ->label("Solde")
                    ->money('EUR', 0, 'fr'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'info',
                        'paid' => 'success',
                        'unpaid' => 'error',
                        'uncollectible' => 'error',
                        'void' => 'primary',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Ouverte',
                        'paid' => 'Payée',
                        'unpaid' => 'Non payée',
                        'uncollectible' => 'Impayée',
                        'void' => 'Annulée',
                        default => 'En attente',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'open' => 'Ouverte',
                        'paid' => 'Payée',
                        'unpaid' => 'Non payée',
                        'uncollectible' => 'Impayée',
                        'void' => 'Annulée',
                    ])
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('download')
                        ->label('Voir la version PDF')
                        ->url(function (array $record) {
                            $invoice = app(CustomerService::class)->getInvoice($record['id']);
                            return $invoice->invoice_pdf;
                        }),

                    Action::make('view')
                        ->label('Voir la version html')
                        ->url(function (array $record) {
                            $invoice = app(CustomerService::class)->getInvoice($record['id']);
                            return $invoice->hosted_invoice_url;
                        }),

                    Action::make('pay')
                        ->label('Payer la facture')
                        ->color('success')
                        ->icon('heroicon-o-credit-card')
                        ->visible(fn (array $record): bool => $record['status'] !== 'paid')
                        ->action(function (array $record) {
                            // Logique de paiement à implémenter
                            // Vérifier d'abord qu'un moyen de paiement est enregistrer, si oui on déclenche le paiement de la facture
                            // Sinon on affiche la facture hebergé pour le paiement

                            $paymentMethods = app(CustomerService::class)->listPaymentMethods(Auth::user()->customer);
                            $invoice = Customer::find(Auth::user()->customer->id)->getInvoice($record['id']);
                            if($paymentMethods->count() > 0) {
                                $pay = app(CustomerService::class)->payInvoice($record['id']);
                                if ($pay->status === 'paid') {
                                    Notification::make()
                                        ->success()
                                        ->title('Facture payée avec succès')
                                        ->toDatabase();

                                } else {
                                    Notification::make()
                                        ->danger()
                                        ->title('Erreur de paiement')
                                        ->body("{$pay->payment_intent->last_payment_error->message}")
                                        ->actions([
                                            Action::make('retry')
                                                ->label('Réessayer')
                                                ->url($invoice->hosted_invoice_url),
                                            Action::make('contact')
                                                ->label('Contacter le support')
                                                ->url('mailto:support@batistack.com'),
                                        ])
                                        ->toDatabase();
                                }
                            } else {
                                $this->redirect($invoice->hosted_invoice_url);
                            }
                        }),
                ]),
            ]);
    }

    public function render()
    {
        return view('livewire.client.account.invoice');
    }
}
