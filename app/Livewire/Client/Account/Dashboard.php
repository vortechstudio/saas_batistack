<?php

namespace App\Livewire\Client\Account;

use App\Enum\Customer\CustomerTypeEnum;
use App\Models\Commerce\Order;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Enum\Customer\CustomerRestrictedIpTypeEnum;
use App\Jobs\Customer\DeleteCustomerAccountJob;
use App\Notifications\Customer\AccountDeletionScheduled;
use Filament\Forms\Components\Toggle;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\Size;
use Illuminate\Support\Facades\Hash;

#[Layout('components.layouts.client')]
#[Title('Mon Compte')]
class Dashboard extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions, InteractsWithSchemas;
    public $activeTab = 'personal';
    public $latestInvoice;
    public ?array $profilData = [];
    public ?array $passwordData = [];
    public User $user;
    public $ipRestrictionData = [];
    public $deleteAccountData = [];
    public $showDeleteConfirmation = false;

    public function mount()
    {
        // Récupérer la dernière facture
        $this->latestInvoice = Order::where('customer_id', Auth::user()->customer->id)
            ->whereNotNull('delivered_at')
            ->latest('delivered_at')
            ->first();

        $this->user = User::with('customer')->find(Auth::user()->id);

        $this->editProfilForm->fill($this->user->toArray());

        if(request()->query('action') === 'cancelDeletion') {
            $this->cancelAccountDeletion();
        }
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function editProfilAction()
    {
        $this->dispatch('open-modal', id: 'edit-profil');
    }

    public function editPasswordAction()
    {
        $this->dispatch('open-modal', id: 'edit-password');
    }

    public function getListCountry()
    {
        $request = Http::withoutVerifying()
            ->get('https://www.apicountries.com/countries');

        if ($request->successful()) {
            $countries = $request->json();

            return collect($countries)->mapWithKeys(function ($country) {
                return [$country['name'] => $country['name']];
            })->toArray();
        }

        return [];
    }

    public function editProfilForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Votre Identification client')
                    ->schema([
                        TextInput::make('customer.code_client')
                            ->label('Code Client')
                            ->readOnly(),

                        Select::make('customer.type_compte')
                            ->label('Type Client')
                            ->live()
                            ->options(CustomerTypeEnum::options()),
                    ]),

                Section::make('Vos informations personnelles')
                    ->schema([
                        TextInput::make('nom')
                            ->label('Nom')
                            ->required(),
                        TextInput::make('prenom')
                            ->label('Prénom')
                            ->required(),
                    ]),

                Section::make('Vos informations de contact')
                    ->schema([
                        TextInput::make('email')
                            ->label('Email')
                            ->required()
                            ->email(),

                        Select::make('customer.pays')
                            ->label('Pays')
                            ->options($this->getListCountry()),

                        TextInput::make('customer.adresse')
                            ->label('Adresse')
                            ->required(),

                        Grid::make()
                            ->schema([
                                TextInput::make('customer.code_postal')
                                    ->label('Code Postal')
                                    ->required(),

                                TextInput::make('customer.ville')
                                    ->label('Ville')
                                    ->required(),
                            ]),

                        TextInput::make('customer.tel')
                            ->label('Telephone')
                            ->required(),

                        TextInput::make('customer.portable')
                            ->label('Telephone'),
                        ]),

                Section::make('Votre Activité')
                        ->schema([
                            TextInput::make('customer.entreprise')
                                ->label('Entreprise'),
                        ])
                        ->visible(fn (Get $get) => $get('customer.type_compte') !== CustomerTypeEnum::PARTICULIER->value),
            ])
            ->statePath('profilData')
            ->model($this->user);
    }

    public function editPasswordForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('old_password')
                    ->label('Ancien Mot de passe')
                    ->password()
                    ->required(),

                TextInput::make('password')
                    ->label('Mot de passe')
                    ->password()
                    ->different('old_password')
                    ->confirmed()
                    ->required(),

                TextInput::make('password_confirmation')
                    ->label('Confirmation du mot de passe')
                    ->password()
                    ->required(),
            ])
            ->statePath('passwordData');
    }

    public function editProfil()
    {
        $data = $this->editProfilForm->getState();
        $this->user->update($data);
        $this->dispatch('close-modal', id: 'edit-profil');
        Notification::make()
            ->success()
            ->title('Profil modifié avec succès')
            ->send();
    }

    public function editPassword()
    {
        $data = $this->editPasswordForm->getState();
        if(Hash::check($data['old_password'], $this->user->password)) {
            $this->user->update($data);
            $this->dispatch('close-modal', id: 'edit-password');
            Notification::make()
                ->success()
                ->title('Mot de passe modifié avec succès')
                ->send();
        } else {
            Notification::make()
                ->error()
                ->title('Ancien mot de passe incorrect')
                ->send();
        }

    }

    public function deleteAccountAction()
    {
        $this->dispatch('open-modal', id: 'delete-account');
    }

    public function deleteAccount()
    {
        $customer = Auth::user()->customer;
        $data = $this->deleteAccountForm->getState();
        $deletionRequest = $this->createDeletionRequest($customer, $data);
        $this->validateAccountDeletion($customer);
        $this->deactivateAccount($customer);

        dispatch(new DeleteCustomerAccountJob($customer, $deletionRequest))->delay(now()->addDays(7));
        Auth::user()->notify(new AccountDeletionScheduled($customer, $deletionRequest));

        $this->dispatch('close-modal', id: 'delete-account');

        Notification::make()
            ->success()
            ->color('success')
            ->title("Suppression de votre compte client")
            ->body("La suppression de votre compte client à été programmé, un mail vous à été envoyé pour le confirmé.")
            ->send();
    }

    public function render()
    {
        return view('livewire.client.account.dashboard');
    }

    public function ipRestrictionForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ip_address')
                    ->label('Adresse IP')
                    ->required()
                    ->ip()
                    ->placeholder('192.168.1.1'),

                Select::make('authorize')
                    ->label('Type d\'autorisation')
                    ->options(CustomerRestrictedIpTypeEnum::options())
                    ->required()
                    ->default(CustomerRestrictedIpTypeEnum::ALLOW->value),

                Toggle::make('alert')
                    ->label('Activer les alertes')
                    ->default(false),
            ])
            ->statePath('ipRestrictionData');
    }

    public function addIpRestriction()
    {
        $data = $this->ipRestrictionForm->getState();

        // Vérifier si l'IP existe déjà pour ce client
        $existingIp = $this->customer->restrictedIps()
            ->where('ip_address', $data['ip_address'])
            ->first();

        if ($existingIp) {
            Notification::make()
                ->warning()
                ->title('Cette adresse IP est déjà configurée')
                ->send();
            return;
        }

        // Créer la nouvelle restriction IP
        $this->customer->restrictedIps()->create([
            'ip_address' => $data['ip_address'],
            'authorize' => CustomerRestrictedIpTypeEnum::from($data['authorize']),
            'alert' => $data['alert'],
        ]);

        $this->dispatch('close-modal', id: 'add-ip-restriction');
        $this->reset('ipRestrictionData');

        Notification::make()
            ->success()
            ->title('Restriction IP ajoutée avec succès')
            ->send();
    }

    public function removeIpRestriction($ipId)
    {
        $ipRestriction = $this->customer->restrictedIps()->find($ipId);

        if ($ipRestriction) {
            $ipRestriction->delete();

            Notification::make()
                ->success()
                ->title('Restriction IP supprimée avec succès')
                ->send();
        }
    }

    public function toggleIpRestrictionStatus($ipId)
    {
        $ipRestriction = $this->customer->restrictedIps()->find($ipId);

        if ($ipRestriction) {
            $newStatus = $ipRestriction->authorize === CustomerRestrictedIpTypeEnum::ALLOW
                ? CustomerRestrictedIpTypeEnum::DENY
                : CustomerRestrictedIpTypeEnum::ALLOW;

            $ipRestriction->update(['authorize' => $newStatus]);

            Notification::make()
                ->success()
                ->title('Statut de la restriction IP modifié')
                ->send();
        }
    }

    public function deleteAccountForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Confirmation de suppression')
                    ->description('Veuillez confirmer votre demande de suppression de compte')
                    ->schema([
                        Checkbox::make('confirm_data_loss')
                            ->label('Je comprends que toutes mes données seront définitivement supprimées')
                            ->required()
                            ->accepted(),

                        Checkbox::make('confirm_services_termination')
                            ->label('Je comprends que tous mes services actifs seront résiliés')
                            ->required()
                            ->accepted(),

                        Checkbox::make('confirm_billing_final')
                            ->label('Je comprends que ma facturation sera finalisée et que je devrai régler les montants dus')
                            ->required()
                            ->accepted(),

                        Textarea::make('reason')
                            ->label('Raison de la suppression (optionnel)')
                            ->placeholder('Pouvez-vous nous dire pourquoi vous souhaitez supprimer votre compte ?')
                            ->rows(3)
                            ->maxLength(500),

                        TextInput::make('password_confirmation')
                            ->label('Confirmez votre mot de passe')
                            ->password()
                            ->required()
                            ->rule('current_password'),
                    ])
            ])
            ->statePath('deleteAccountData');
    }

    private function validateAccountDeletion($customer): void
    {
        // Vérifier s'il y a des factures impayées
        $unpaidOrders = $customer->orders()
            ->whereIn('status', ['pending', 'cancelled', 'refunded'])
            ->where('total_amount', '>', 0)
            ->exists();

        if ($unpaidOrders) {
            $this->dispatch('close-modal', id: 'delete-account');
            Notification::make()
                ->warning()
                ->title('Impossible de supprimer le compte : des factures sont encore impayées.')
                ->color('warning')
                ->send();
        }

        // Vérifier s'il y a des services actifs critiques
        $criticalServices = $customer->services()
            ->where('status', 'ok')
            ->whereHas('product', function($query) {
                $query->where('category', 'license'); // Supposons qu'il y ait un flag pour les produits critiques
            })
            ->exists();

        if ($criticalServices) {
            $this->dispatch('close-modal', id: 'delete-account');
            Notification::make()
                ->warning()
                ->title('Veuillez d\'abord résilier vos services actifs avant de supprimer votre compte.')
                ->color('warning')
                ->send();
            return;
        }
    }

    private function createDeletionRequest($customer, array $data): object
    {
        return (object) [
            'customer_id' => $customer->id,
            'requested_at' => now(),
            'scheduled_for' => now()->addDays(7),
            'reason' => $data['reason'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'confirmations' => [
                'data_loss' => $data['confirm_data_loss'],
                'services_termination' => $data['confirm_services_termination'],
                'billing_final' => $data['confirm_billing_final'],
            ]
        ];
    }

    private function deactivateAccount($customer): void
    {
        // Marquer le compte comme en cours de suppression
        $customer->update([
            'status' => 'pending_deletion',
            'deactivated_at' => now()
        ]);

        // Suspendre tous les services
        $customer->services()->update([
            'status' => 'suspended'
        ]);

        // Désactiver toutes les méthodes de paiement
        $customer->paymentMethods()->update([
            'is_active' => false
        ]);

        // Envoyer un email de confirmation
        // Mail::to($customer->user->email)->send(new AccountDeletionScheduled($customer));
    }

    public function cancelAccountDeletion(): void
    {
        $customer = $this->user->customer;

        if ($customer->status !== 'pending_deletion') {
            Notification::make()
                ->warning()
                ->title('Aucune suppression en cours')
                ->send();
            return;
        }

        try {
            DB::beginTransaction();

            // Réactiver le compte
            $customer->update([
                'status' => 'active',
                'deactivated_at' => null
            ]);

            // Réactiver les services (si ils étaient actifs avant)
            $customer->services()
                ->where('status', 'suspended')
                ->update(['status' => 'ok']);

            // Annuler le job de suppression programmé
            // Ici vous devriez implémenter la logique pour annuler le job

            DB::commit();

            Notification::make()
                ->success()
                ->title('Suppression annulée')
                ->body('Votre compte a été réactivé avec succès.')
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'annulation de suppression', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);

            Notification::make()
                ->danger()
                ->title('Erreur')
                ->body('Impossible d\'annuler la suppression. Contactez le support.')
                ->send();
        }
    }
}
