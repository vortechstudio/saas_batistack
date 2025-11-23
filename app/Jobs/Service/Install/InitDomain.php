<?php

namespace App\Jobs\Service\Install;

use App\Models\Customer\CustomerService;
use App\Models\User;
use App\Services\AaPanel\DatabaseService;
use App\Services\AaPanel\DomainService;
use App\Services\AaPanel\FetchService;
use App\Services\Ovh\Domain;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Forge\Forge;

class InitDomain implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $domain;
    public $fetch;
    public $database;

    /**
     * Create a new job instance.
     */
    public function __construct(private CustomerService $service)
    {
        $this->domain = new DomainService();
        $this->fetch = new FetchService();
        $this->database = new DatabaseService();
    }

    /**
     * Provisionne le domaine, la base de données et le site pour le service client.
     *
     * Génère un label de domaine et des identifiants de base de données, met à jour le champ `domain`
     * du service puis :
     * - en environnement local : marque l'étape "Création de domaine" comme terminée et planifie la vérification du domaine ;
     * - en environnement non-local : tente de déclarer le domaine (OVH), créer une base de données et créer un site via Forge, marque l'étape comme terminée et planifie la vérification en cas de succès ; en cas d'échec met le service en statut `error`, met l'étape en non-terminée avec le commentaire d'erreur et envoie une notification d'alerte à l'administrateur.
     */
    public function handle(): void
    {
        $label = Str::slug($this->service->customer->entreprise);
        $domainLabel = trim(Str::limit($label, 63, ''), '-');
        if ($domainLabel === '') {
            $domainLabel = 'client-'.substr(md5($label), 0, 8);
        }
        $domain = $domainLabel . '.' . config('batistack.domain');

        // DB : remplacer '-' par '_' et respecter la limite (MySQL ≤64)
        $dbLabel = substr(str_replace('-', '_', $domainLabel), 0, 61);
        $database_name = 'db_' . $dbLabel;
        $database_password = Str::random(16);

        $this->service->update([
            'domain' => $domain,
        ]);

        if (config('app.env') == 'local') {
            $this->service->steps()->where('step', 'Création de domaine')->first()?->update([
                'done' => true,
            ]);
            dispatch(new VerifyDomain($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));
        } else {
            try {
                // Forge & OVH Create Site
                // OVH Déclare Domaine
                app(Domain::class)->create('core', request()->ip());
                $serverId = collect(app(\App\Services\Forge::class)->client->servers())->first()->id;


                // Création de la base de donnée
                $database = app(\App\Services\Forge::class)->client->createDatabase(
                    $serverId,
                    [
                        'name' => $database_name,
                        'user' => $label,
                        'password' => $database_password,
                    ],
                );

                // Création du site sur service forge
                $site = app(\App\Services\Forge::class)->client->createSite(
                    $serverId,
                    [
                        'type' => 'laravel',
                        'domain-mode' => 'on-forge',
                        'name' => $domain,
                        'web_directory' => '/public',
                        'php_version' => '8.3',
                        'zero_downtime_deployments' => true,
                        'source_control_provider' => 'github',
                        'repository' => 'BatistackApp/Core2',
                        'branch' => 'production',
                        'database_id' => $database['data']['id'],
                        'database_user_id' => $label,
                    ]
                );

                if(isset($site['data'])) {
                    $this->service->steps()->where('step', 'Création de domaine')->first()?->update([
                        'done' => true,
                    ]);
                    dispatch(new VerifyDomain($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));
                } else {
                    $this->service->update([
                        'status' => 'error',
                    ]);
                    $this->service->steps()->where('step', 'Création de domaine')->first()?->update([
                        'done' => false,
                        'comment' => "Erreur lors de la création du site/domaine",
                    ]);
                    Notification::make()
                        ->danger()
                        ->title("Installation d'un service en erreur !")
                        ->body("Erreur lors de la création du site/domaine")
                        ->sendToDatabase(User::where('email', 'admin@'.config('batistack.domain'))->first());
                }



            } catch (\Exception $e) {
                $this->service->update([
                    'status' => 'error',
                ]);
                $this->service->steps()->where('step', 'Création de domaine')->first()?->update([
                    'done' => false,
                    'comment' => $e->getMessage(),
                ]);
                Notification::make()
                    ->danger()
                    ->title("Installation d'un service en erreur !")
                    ->body($e->getMessage())
                    ->sendToDatabase(User::where('email', 'admin@'.config('batistack.domain'))->first());
            }
        }
    }
}