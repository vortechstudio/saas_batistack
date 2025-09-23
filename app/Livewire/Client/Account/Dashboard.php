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
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Mon Compte')]
class Dashboard extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions, InteractsWithSchemas;
    public $activeTab = 'general';
    public $latestInvoice;
    public ?array $profilData = [];
    public User $user;

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

    public function editProfil()
    {
        $data = $this->editProfilForm->getState();
        dd($data);
    }

    public function render()
    {
        return view('livewire.client.account.dashboard');
    }
}
