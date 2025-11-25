<?php

namespace App\Livewire\Admin\Commerce\Customer;

use App\Enum\Customer\CustomerTypeEnum;
use App\Models\Customer\Customer;
use App\Models\User;
use App\Notifications\Customer\WelcomeCustomerNotification;
use App\Services\Stripe\CustomerService;
use App\Trait\Commerce\TiersSchema;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ListCustomer extends Component implements HasTable, HasActions, HasSchemas
{
    use InteractsWithTable, InteractsWithActions, InteractsWithSchemas, TiersSchema;

    public function table(Table $table): Table
    {
        return $table
            ->query(Customer::query())
            ->heading("Liste des Clients")
            ->columns([
                TextColumn::make('entreprise')
                    ->label('Client')
                    ->description(fn (?Model $record) => $record->code_client)
                    ->searchable(),

                TextColumn::make('type_compte')
                    ->label("Type de client")
                    ->formatStateUsing(fn (?Model $record) => $record->type_compte->label()),

                TextColumn::make('adresse')
                    ->label("Adresse")
                    ->formatStateUsing(function (?Model $record) {
                        return "{$record->adresse}<br>{$record->code_postal} {$record->ville}<br>{$record->pays}";
                    })
                    ->html(),

                TextColumn::make('tel')
                    ->label("Coordonnées")
                    ->formatStateUsing(function (?Model $record) {
                        return "<strong>Téléphone:</strong> {$record->tel}<br><strong>Email:</strong> {$record->user->email}";
                    })
                    ->html(),

                TextColumn::make('status')
                    ->label("Statut")
                    ->badge()
                    ->formatStateUsing(function (?Model $record) {
                        return \Str::ucfirst($record->status);
                    })
                    ->color(fn (?Model $record) => match($record->status) {
                        "active" => "info",
                        "inactive" => "danger"
                    }),
            ])
            ->filters([
                SelectFilter::make('type_compte')
                    ->label("Type de client")
                    ->options(CustomerTypeEnum::options()),

                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        "active" => "Active",
                        "inactive" => "Inactive"
                    ])
            ])
            ->headerActions([
                CreateAction::make('create')
                    ->icon(Heroicon::PlusCircle)
                    ->label("Créer un client")
                    ->modalHeading("Nouveau client")
                    ->modalWidth(Width::FourExtraLarge)
                    ->schema($this->getSchemaTiers())
                    ->using(function (array $data) {
                        $password = \Str::random(10);

                        try {
                            $user = User::create([
                                'nom' => $data['nom'],
                                'prenom' => $data['prenom'],
                                'email' => $data['email'],
                                'password' => \Hash::make($password),
                            ]);

                            $customer = Customer::create([
                                'type_compte' => $data['type_compte'],
                                'entreprise' => $data['entreprise'] ?? null,
                                'adresse' => $data['adresse'],
                                'code_postal' => $data['code_postal'],
                                'ville' => $data['ville'],
                                'pays' => $data['pays'],
                                'tel' => $data['tel'],
                                'portable' => $data['portable'],
                                'support_type' => $data['support_type'],
                                'user_id' => $user->id
                            ]);

                            app(CustomerService::class)->create($customer);
                            $user->notify(new WelcomeCustomerNotification());
                        }catch (\Exception $exception) {
                            \Log::emergency($exception->getMessage(), $exception);
                            throw $exception;
                        }
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->iconButton()
                    ->icon(Heroicon::Eye)
                    ->tooltip("Voir le client")
                    ->url(fn (?Model $record) => route('admin.commerce.customers.show', $record)),

                ActionGroup::make([
                    EditAction::make('edit')
                        ->icon(Heroicon::Pencil)
                        ->modalHeading("Nouveau client")
                        ->modalWidth(Width::FourExtraLarge)
                        ->label("Modifier le client")
                        ->schema($this->getSchemaTiers()),

                    Action::make('active')
                        ->label('Activer le client')
                        ->icon(Heroicon::CheckCircle)
                        ->visible(fn (?Model $record) => $record->status === 'inactive')
                        ->color('success')
                        ->action(function (?Model $record) {
                            $record->update(['status' => 'active']);
                        }),

                    Action::make('desactive')
                        ->label('Désactiver le client')
                        ->icon(Heroicon::XCircle)
                        ->visible(fn (?Model $record) => $record->status === 'active')
                        ->color('danger')
                        ->action(function (?Model $record) {
                            $record->update(['status' => 'inactive']);
                        }),

                    Action::make('reinit-password')
                        ->label("Réinitialiser le mot de passe")
                        ->icon(Heroicon::Key)
                        ->color('warning')
                        ->action(function (?Model $record) {
                            Password::sendResetLink(['email' => $record->user->email]);
                            Notification::make()
                                ->success()
                                ->title("Un lien de réinitialisation à été envoyer au client")
                                ->send();
                        }),

                    DeleteAction::make('delete')
                        ->icon(Heroicon::Trash)
                        ->label("Supprimer le client")
                        ->color('danger')
                        ->modalHeading("Supprimer le client")
                        ->mutateDataUsing(function (?Model $record, array $data) {
                            $data['nom'] = $record->user->nom;
                            $data['prenom'] = $record->user->prenom;
                            return $data;
                        })
                        ->requiresConfirmation()
                        ->using(function (Customer $record) {
                            $record->delete();
                        })

                ])
            ]);
    }

    public function render()
    {
        return view('livewire.admin.commerce.customer.list-customer');
    }
}
