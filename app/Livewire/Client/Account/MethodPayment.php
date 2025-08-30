<?php

namespace App\Livewire\Client\Account;

use App\Services\Stripe\PaymentMethodService;
use App\Services\Stripe\StripeService;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup as ActionsActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Mes moyens de paiements')]
class MethodPayment extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public function table(Table $table): Table
    {
        //dd($this->getPaymentMethodsData());
        return $table
            ->records(fn () => $this->getPaymentMethodsData())
            ->columns([
                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'card' => 'Carte bancaire',
                        'sepa_debit' => 'Prélèvement SEPA',
                        'paypal' => 'PayPal',
                        default => ucfirst($state)
                    })
                    ->icon(fn (string $state): string => match($state) {
                        'card' => 'heroicon-o-credit-card',
                        'sepa_debit' => 'heroicon-o-building-library',
                        'paypal' => 'heroicon-o-globe-alt',
                        default => 'heroicon-o-banknotes'
                    }),

                TextColumn::make('card.brand')
                    ->label('Marque')
                    ->formatStateUsing(fn (?string $state): string => $state ? strtoupper($state) : '-')
                    ->visible(fn ($record): bool => $record && isset($record['type']) && $record['type'] === 'card'),

                TextColumn::make('card.last4')
                    ->label('Derniers chiffres')
                    ->formatStateUsing(fn (?string $state): string => $state ? '**** **** **** ' . $state : '-')
                    ->visible(fn ($record): bool => $record && isset($record['type']) && $record['type'] === 'card'),

                TextColumn::make('card.exp_month')
                    ->label('Expiration')
                    ->formatStateUsing(fn ($record): string =>
                        $record && isset($record['type']) && $record['type'] === 'card' && isset($record['card']['exp_month'], $record['card']['exp_year'])
                            ? sprintf('%02d/%d', $record['card']['exp_month'], $record['card']['exp_year'])
                            : '-'
                    )
                    ->visible(fn ($record): bool => $record && isset($record['type']) && $record['type'] === 'card'),

                IconColumn::make('is_default')
                    ->label('Par défaut')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                TextColumn::make('created')
                    ->label('Ajouté le')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->recordActions([
                ActionsActionGroup::make([
                    Action::make('setDefault')
                        ->label('Définir par défaut')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->visible(fn (array $record): bool => $record && isset($record['is_default']) && !$record['is_default'])
                        ->action(function (array $record) {
                            try {
                                app(PaymentMethodService::class)->setDefaultPaymentMethod(
                                    Auth::user()->customer,
                                    $record['id']
                                );

                                Notification::make()
                                    ->success()
                                    ->title('Moyen de paiement défini par défaut')
                                    ->send();

                            } catch (\Exception $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Erreur lors de la mise à jour')
                                    ->body($e->getMessage())
                                    ->send();
                            }
                        }),

                    Action::make('delete')
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer le moyen de paiement')
                        ->modalDescription('Êtes-vous sûr de vouloir supprimer ce moyen de paiement ? Cette action est irréversible.')
                        ->action(function (array $record) {
                            try {
                                app(PaymentMethodService::class)->detachPaymentMethod($record['id']);

                                Notification::make()
                                    ->success()
                                    ->title('Moyen de paiement supprimé')
                                    ->send();

                            } catch (\Exception $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Erreur lors de la suppression')
                                    ->body($e->getMessage())
                                    ->send();
                            }
                        }),
                ])
            ])
            ->headerActions([
                Action::make('add')
                    ->label('Ajouter un moyen de paiement')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->action(function () {
                        try {
                            $session = app(PaymentMethodService::class)->createSetupSession(
                                Auth::user()->customer,
                                route('client.account.method-payment')
                            );



                            return redirect($session->url);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Erreur lors de la création de la session')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
            ])
            ->emptyStateHeading('Aucun moyen de paiement')
            ->emptyStateDescription('Vous n\'avez pas encore ajouté de moyen de paiement.')
            ->emptyStateIcon('heroicon-o-credit-card')
            ->emptyStateActions([
                Action::make('add')
                    ->label('Ajouter un moyen de paiement')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->action(function () {
                        try {
                            $session = app(PaymentMethodService::class)->createSetupSession(
                                Auth::user()->customer,
                                request()->url()
                            );

                            return redirect($session->url);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Erreur lors de la création de la session')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
            ]);
    }

    public function getTableRecordKey($record): string
    {
        return $record['key'] ?? $record['id'];
    }

    protected function getPaymentMethodsData()
    {
        $paymentMethods = app(PaymentMethodService::class)
            ->listPaymentMethods(Auth::user()->customer)
            ->map(function ($paymentMethod) {
                $customer = Auth::user()->customer;
                $stripeCustomer = app(PaymentMethodService::class)->getStripeCustomer($customer->stripe_customer_id);

                return [
                    'key' => $paymentMethod->id, // Clé unique pour Filament
                    'id' => $paymentMethod->id,
                    'type' => $paymentMethod->type,
                    'card' => $paymentMethod->card ? $paymentMethod->card->toArray() : null,
                    'sepa_debit' => $paymentMethod->sepa_debit ? $paymentMethod->sepa_debit->toArray() : null,
                    'is_default' => isset($stripeCustomer->invoice_settings->default_payment_method)
                        && $stripeCustomer->invoice_settings->default_payment_method === $paymentMethod->id,
                    'created' => $paymentMethod->created,
                ];
            })
            ->toArray();

            return $paymentMethods;
    }

    public function mount()
    {
        // Gérer les retours de Stripe Checkout
        if (request()->has('setup')) {
            if (request()->get('setup') === 'success') {
                $paymentMethods = Auth::user()->customer->listPaymentMethods();
                $existingPaymentMethodIds = Auth::user()->customer->paymentMethods()->pluck('stripe_payment_method_id')->toArray();

                foreach ($paymentMethods as $method) {
                    $isNewPaymentMethod = !in_array($method->id, $existingPaymentMethodIds);

                    Auth::user()->customer->paymentMethods()->updateOrCreate(
                        ['stripe_payment_method_id' => $method->id],
                        [
                            "is_active" => true,
                            "is_default" => false,
                            "customer_id" => Auth::user()->customer->id,
                        ]
                    );

                    // Définir comme moyen de paiement par défaut uniquement lors de la création
                    if ($isNewPaymentMethod) {
                        app(StripeService::class)->client->customers->update(Auth::user()->customer->stripe_customer_id, [
                            'invoice_settings' => [
                                'default_payment_method' => $method->id,
                            ]
                        ]);
                    }
                }

                Notification::make()
                    ->success()
                    ->title('Moyen de paiement ajouté avec succès')
                    ->send();
            } elseif (request()->get('setup') === 'cancel') {
                Notification::make()
                    ->warning()
                    ->title('Ajout du moyen de paiement annulé')
                    ->send();
            }
        }
    }

    public function render()
    {
        return view('livewire.client.account.method-payment');
    }
}
