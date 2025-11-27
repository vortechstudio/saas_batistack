<?php

namespace App\Livewire\Authentification;

use App\Enum\Customer\CustomerTypeEnum;
use App\Models\Country;
use App\Models\Customer\Customer;
use App\Models\User;
use App\Services\Stripe\CustomerService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Register extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];

    public function mount()
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Wizard\Step::make('Général')
                        ->schema([
                            TextInput::make('nom')->label('Nom')->required(),
                            TextInput::make('prenom')->label('Prénom')->required(),
                            TextInput::make('email')->label('Email')->required()->email(),
                        ]),

                    Wizard\Step::make('Complémentaire')
                        ->schema([
                            Select::make('type_compte')
                                ->label("Type de compte")
                                ->live()
                                ->options(CustomerTypeEnum::options()),

                            TextInput::make('entreprise')
                                ->label('Raison Social')
                                ->visible(fn (Get $get) => $get('type_compte') !== CustomerTypeEnum::PARTICULIER->value),

                            Textarea::make('adresse')
                                ->label('Adresse')
                                ->required(),

                            TextInput::make('code_postal')
                                ->label('Code postal')
                                ->required(),

                            TextInput::make('ville')
                                ->label('Ville')
                                ->required(),

                            TextInput::make('pays')
                                ->label("Pays")
                                ->required(),

                            TextInput::make('tel')
                                ->label("Téléphone")
                                ->mask("99 99 99 99 99"),

                            TextInput::make('portable')
                                ->label("Portable")
                                ->mask("99 99 99 99 99"),
                        ]),

                    Wizard\Step::make('Sécurité')
                        ->schema([
                            TextInput::make('password')
                                ->label("Mot de Passe")
                                ->required()
                                ->revealable()
                                ->password()
                                ->confirmed(),

                            TextInput::make('password_confirmation')
                                ->label("Confirmation du mot de passe")
                                ->required()
                                ->password()
                                ->revealable(),
                        ]),
                ])
                    ->submitAction(new HtmlString('<button type="submit" class="btn btn-primary">Valider</button>')),
            ])
            ->statePath('data');
    }

    public function register()
    {
        $data = $this->form->getState();

        $data['password'] = \Hash::make($data['password']);
        $user = User::create([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        event(new Registered($user));

        $customer = Customer::create([
            'code_client' => "CLI".rand(100000,999999999),
            'type_compte' => $data['type_compte'],
            'entreprise' => $data['entreprise'] ?? null,
            'adresse' => $data['adresse'],
            'code_postal' => $data['code_postal'],
            'ville' => $data['ville'],
            'pays' => $data['pays'],
            'tel' => $data['tel'],
            'portable' => $data['portable'],
            'user_id' => $user->id,
        ]);
        app(CustomerService::class)->create($customer);

        \Auth::login($user);

        $this->redirectIntended(route('client.dashboard', absolute: false), navigate: true);
    }

    public function render()
    {
        return view('livewire.authentification.register');
    }
}
