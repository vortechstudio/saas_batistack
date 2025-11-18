<?php

namespace App\Livewire\Client\Account;

use App\Mail\Service\CreateUser;
use App\Models\Customer\CustomerService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Mes Service - Détail')]
class ServiceShow extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public CustomerService $service;
    public string $stateInstallLabel = '';
    public int $stateInstallCurrent = 0;
    public int $stateInstallTotal = 0;
    public ?string $comment = null;
    public ?array $infoStorage = null;
    public bool $limitUser = false;

    // Gestion des onglets
    public string $activeTab = 'modules';

    /**
     * Initialise le composant avec les données du service spécifié et calcule l'état d'installation.
     *
     * Charge le CustomerService correspondant au code fourni (avec produit, étapes, modules et options)
     * et initialise les propriétés stateInstallTotal, stateInstallCurrent et stateInstallLabel
     * à partir des étapes d'installation associées.
     *
     * @param string $service_code Code unique du service à afficher.
     */
    public function mount(string $service_code)
    {
        $this->service = CustomerService::with('product', 'steps', 'modules.feature', 'options.product')->where('service_code', $service_code)->first();
        $this->stateInstallTotal = $this->service->steps->count();
        $this->stateInstallCurrent = $this->service->steps->where('done', true)->count()+1;
        $this->stateInstallLabel = $this->service->steps()->where('done', false)->latest()->first()->step ?? '';


    }

    /**
     * Met à jour les propriétés représentant l'état d'installation du service.
     *
     * Met à jour :
     * - $stateInstallTotal : nombre total d'étapes d'installation du service,
     * - $stateInstallCurrent : index de l'étape courante (nombre d'étapes complétées + 1),
     * - $stateInstallLabel : libellé de la dernière étape incomplète ou `'Fin'` si aucune,
     * - $comment : commentaire associé à la dernière étape incomplète ou `null`.
     */
    public function refreshStateInstall()
    {
        $this->stateInstallTotal = $this->service->steps->count();
        $this->stateInstallCurrent = $this->service->steps->where('done', true)->count()+1;
        $this->stateInstallLabel = $this->service->steps()->where('done', false)->latest()->first()->step ?? 'Fin';

        if($this->stateInstallCurrent == $this->stateInstallTotal) {
            $this->stateInstallLabel = 'Fin';
        }

        $this->comment = $this->service->steps()->where('done', false)->latest()->first()->comment ?? null;
    }

    /**
     * Définit l'onglet actif et met à jour les informations de stockage ainsi que l'indicateur de quota d'utilisateurs.
     *
     * Met à jour l'onglet courant utilisé par l'interface, recharge les informations de stockage du service et calcule si la création
     * de nouveaux utilisateurs doit être limitée en fonction du nombre d'utilisateurs présents sur le service.
     *
     * @param string $tab Identifiant de l'onglet à activer (par exemple 'modules', 'storage').
     */
    public function setActiveTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->getStorageInfo();
        $users = Http::withoutVerifying()
            ->get('//'.$this->service->domain.'/api/users')
            ->collect()
            ->toArray();

        $this->limitUser = count($users) >= $this->service->max_user;
    }

    /**
     * Vérifie si l'option "Sauvegarde et rétention" est associée au service
     */
    public function hasBackupOption(): bool
    {
        return $this->service->options()
            ->whereHas('product', function ($query) {
                $query->where('slug', 'sauvegarde-et-retentions');
            })
            ->exists();
    }

    /**
     * Récupère les informations de stockage du service
     */
    public function getStorageInfo()
    {
        $response = Http::withoutVerifying()
            ->get('//'.$this->service->domain.'/api/core/storage/info');

        if ($response->status() == 200) {
            $this->infoStorage = $response->object();
        } else {
            $this->infoStorage = [];
        }

    }

    public function table(Table $table): Table
    {
        $users = [];

        try {
            $response = Http::withoutVerifying()
                ->timeout(5)
                ->get('//'.$this->service->domain.'/api/users');

            if($response->successful()) {
                $users = $response->collect()->toArray();
            }
        }catch (\Exception $exception) {

        }


        return $table->records(fn () => $users)
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('name')->label('Identité')->searchable(isIndividual: true),
                TextColumn::make('email')->label('Email'),
                TextColumn::make('role')->label('Rôle'),
                IconColumn::make('blocked')
                    ->label('Accès')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->color(fn (bool $state): string => $state ? 'danger' : 'success')
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Créer un utilisateur')
                    ->visible(fn () => $this->service->max_user > count($users))
                    ->modal(true)
                    ->schema([
                        TextInput::make('name')->label('Identité')->required(),
                        TextInput::make('email')->label('Email')->required()->email(),
                        Select::make('role')->label('Rôle')->options([
                            'user' => 'Utilisateur',
                            'admin' => 'Administrateur',
                        ])->required(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (array $data) use ($users) {
                        // 1. On vérifie le nombre d'utilisateur sur l'espace et le nombre autorisé
                        // 2. On envoie les données du nouvelle utilisateur sur l'espace du client
                        // 3. On envoie un mail de définition de mot de passe à l'utilisateur.
                        // 4. On notifie l'utilisateur actuel que l'utilisateur a été créé.

                        // 1. On vérifie le nombre d'utilisateur sur l'espace et le nombre autorisé
                        if(count($users) >= $this->service->max_user) {
                            Notification::make()
                                ->title("Création de l'utilisateur")
                                ->body("Le nombre maximum d'utilisateur a été atteint.")
                                ->danger()
                                ->send();
                            return;
                        }

                        // 2. On envoie les données du nouvelle utilisateur sur l'espace du client
                        try{
                            Http::withoutVerifying()
                            ->post('https://'.$this->service->domain.'/api/users', $data);

                            Log::debug("Utilisateur créé avec succès");
                        } catch (\Exception $e) {
                            Log::error($e->getMessage());
                            Notification::make()
                                ->title("Création de l'utilisateur")
                                ->body("Une erreur est survenue lors de la création de l'utilisateur.")
                                ->danger()
                                ->send();
                            return;
                        }

                        // 4. On notifie l'utilisateur actuel que l'utilisateur a été créé.
                        Notification::make()
                            ->title("Création de l'utilisateur")
                            ->body("L'utilisateur {$data['name']} a été créé.")
                            ->success()
                            ->send();
                    }),

                Action::make('refresh')
                    ->label('Actualiser')
                    ->icon(Heroicon::ArrowPath)
                    ->action(fn() => $this->resetTable()),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('edit')
                        ->label("Editer l'utilisateur")
                        ->icon(Heroicon::Pencil)
                        ->modal(true)
                        ->schema([
                            TextInput::make('name')->label('Identité')->required()->default(fn ($record) => $record['name']),
                            TextInput::make('email')->label('Email')->required()->email()->default(fn ($record) => $record['email']),
                            Select::make('role')->label('Rôle')->options([
                                'user' => 'Utilisateur',
                                'admin' => 'Administrateur',
                            ])->required()->default(fn ($record) => $record['role']),
                        ])
                        ->action(function (array $data, $record) {
                            try {
                                $request = Http::withoutVerifying()
                                ->put('//'.$this->service->domain.'/api/users/'.$record['id'], $data);

                                if($request->failed()) {
                                    throw new \Exception($request->body());
                                }

                                // 5. On notifie l'utilisateur actuel que l'utilisateur a été modifié.
                                Notification::make()
                                    ->title("Modification de l'utilisateur")
                                    ->body("L'utilisateur {$data['name']} a été modifié.")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Log::error($e->getMessage());
                                Notification::make()
                                    ->title("Modification de l'utilisateur")
                                    ->body("Une erreur est survenue lors de la modification de l'utilisateur.")
                                    ->danger()
                                    ->send();
                                return;
                            }
                        }),

                    Action::make('reinit-password')
                        ->label("Réinitialiser le mot de passe")
                        ->icon(Heroicon::OutlinedKey)
                        ->requiresConfirmation()
                        ->action(function (array $data, $record) {
                            try {
                                Http::withoutVerifying()
                                    ->get('//'.$this->service->domain.'/api/users/'.$record['id'].'/password-reset', $data);

                                Notification::make()
                                    ->title("Réinitialisation du mot de passe")
                                    ->body("Le mot de passe de l'utilisateur {$record['name']} a été réinitialisé.")
                                    ->success()
                                    ->send();

                            } catch (\Exception $e) {
                                Log::error($e->getMessage());
                                Notification::make()
                                    ->title("Réinitialisation du mot de passe")
                                    ->body("Une erreur est survenue lors de la réinitialisation du mot de passe.")
                                    ->danger()
                                    ->send();
                                return;
                            }
                        }),

                    Action::make('delete')
                        ->label('Supprimer l\'utilisateur')
                        ->icon(Heroicon::Trash)
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            try {
                                Http::withoutVerifying()
                                    ->delete('//'.$this->service->domain.'/api/users/'.$record['id']);

                                Notification::make()
                                    ->title("Suppression de l'utilisateur")
                                    ->body("L'utilisateur {$record['name']} a été supprimé.")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Log::error($e->getMessage());
                                Notification::make()
                                    ->title("Suppression de l'utilisateur")
                                    ->body("Une erreur est survenue lors de la suppression de l'utilisateur.")
                                    ->danger()
                                    ->send();
                                return;
                            }
                        }),

                    Action::make('user-block')
                        ->visible(fn ($record) => $record['blocked'] == false)
                        ->label("Bloquer l'utilisateur")
                        ->icon(Heroicon::LockClosed)
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            try {
                                Http::withoutVerifying()
                                    ->put('//'.$this->service->domain.'/api/users/'.$record['id'], [
                                        'blocked' => 1,
                                    ]);

                                Notification::make()
                                    ->title("Bloquage de l'utilisateur")
                                    ->body("L'utilisateur {$record['name']} a été bloqué.")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Log::error($e->getMessage());
                                Notification::make()
                                    ->title("Bloquage de l'utilisateur")
                                    ->body("Une erreur est survenue lors du bloquage de l'utilisateur.")
                                    ->danger()
                                    ->send();
                                return;
                            }
                        }),

                    Action::make('user-unblock')
                        ->visible(fn ($record) => $record['blocked'] == true)
                        ->label("Débloquer l'utilisateur")
                        ->icon(Heroicon::LockOpen)
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            try {
                                Http::withoutVerifying()
                                    ->put('//'.$this->service->domain.'/api/users/'.$record['id'], [
                                        'blocked' => 0,
                                    ]);

                                Notification::make()
                                    ->title("Débloquage de l'utilisateur")
                                    ->body("L'utilisateur {$record['name']} a été débloqué.")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Log::error($e->getMessage());
                                Notification::make()
                                    ->title("Débloquage de l'utilisateur")
                                    ->body("Une erreur est survenue lors du débloquage de l'utilisateur.")
                                    ->danger()
                                    ->send();
                                return;
                            }
                        }),

                ]),
                Action::make('impersonate')
                    ->tooltip('Se connecter')
                    ->iconButton()
                    ->icon(Heroicon::ArrowRightEndOnRectangle)
                    ->visible(fn ($record) => !$record['blocked'])
                    ->action(function ($record) {
                        try {
                            $response = Http::withoutVerifying()
                                ->post('//'.$this->service->domain.'/api/auth/sso-link', [
                                    'email' => $record['email'],
                                    'source' => 'saas_dashboard',
                                    'secret' => config('batistack.shared_secret')
                                ]);

                            if ($response->successful() && $url = $response->json('url')) {
                                return redirect()->away($url);
                            }
                            throw new \Exception("L'instance n'a pas renvoyé de lien valide.");
                        }catch (\Exception $exception) {
                            Notification::make()
                                ->title("Connexion échouée")
                                ->body("Impossible d'établir la connexion SSO avec l'instance.")
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }

    public function render()
    {
        //dd($this->service->product->info_stripe);
        return view('livewire.client.account.service-show');
    }
}
