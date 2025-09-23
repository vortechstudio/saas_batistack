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
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Customer\CustomerRestrictedIp;
use App\Enum\Customer\CustomerRestrictedIpTypeEnum;
use Filament\Forms\Components\Toggle;

#[Layout('components.layouts.client')]
#[Title('Mon Compte')]
class Dashboard extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions, InteractsWithSchemas;
    public $activeTab = 'emails';
    public $latestInvoice;
    public ?array $profilData = [];
    public ?array $passwordData = [];
    public User $user;
    public $ipRestrictionData = [];

    public function mount()
    {
        // Récupérer la dernière facture
        $this->latestInvoice = Order::where('customer_id', Auth::user()->customer->id)
            ->whereNotNull('delivered_at')
            ->latest('delivered_at')
            ->first();

        $this->user = User::with('customer')->find(Auth::user()->id);

        $this->editProfilForm->fill($this->user->toArray());
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
}
