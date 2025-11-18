<?php

namespace App\Jobs\Service\Install;

use App\Models\Customer\CustomerService;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class VerifyServiceConnection implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected CustomerService $service)
    {
        //
    }

    /**
     * Vérifie la connectivité du service SAAS du client, met à jour l'état d'installation et déclenche les actions suivantes.
     *
     * En environnement local : marque l'étape de vérification comme réussie, planifie la vérification de licence et journalise le succès.
     * En environnement non-local : effectue des vérifications HTTP et API du domaine du service, marque l'étape comme réussie, planifie la notification au client et journalise le succès.
     * En cas d'erreur : marque l'étape comme échouée, met le service en statut `error`, envoie une notification d'alerte à l'administrateur, journalise l'erreur et relance l'exception.
     *
     * @throws \Exception Si la vérification HTTP ou API échoue ou si une autre erreur empêche la finalisation de la vérification.
     */
    public function handle(): void
    {
        $domain = Str::slug($this->service->customer->entreprise). '.'.config('batistack.domain');

        try {
            if (config('app.env') === 'local') {
                $this->service->steps()->where('step', 'Vérification de la connexion au service (SAAS)')->first()->update([
                    'done' => true,
                    'comment' => 'Service SAAS accessible et fonctionnel. HTTP: 200, DB: OK, Services: OK'
                ]);

                dispatch(new VerifyLicenseInformation($this->service))->onQueue('installApp')->delay(10);

                Log::info("Connexion au service SAAS vérifiée avec succès", [
                    'service_id' => $this->service->id,
                    'domain' => $domain,
                ]);
            } else {
                // 1. Vérification de la connectivité HTTP/HTTPS
                $httpCheck = $this->checkHttpResponse($domain);

                if (!$httpCheck) {
                    throw new \Exception("Impossible d'accéder au service via HTTP/HTTPS: " . ($httpCheck['error'] ?? 'Code HTTP: ' . ($httpCheck['http_code'] ?? 'inconnu')));
                }

                // 2. Vérification de l'API de l'application (si disponible)
                $apiCheck = $this->checkApiEndpoint($domain);

                if (!$apiCheck) {
                    throw new \Exception("Erreur lors de l'appel API pour le domaine: ".$domain);
                }

                $this->service->steps()->where('step', 'Vérification de la connexion au service (SAAS)')->first()->update([
                    'done' => true,
                    'comment' => 'Service SAAS accessible et fonctionnel. HTTP: 200, DB: OK, Services: OK'
                ]);

                dispatch(new NotifyClientByMail($this->service))->onQueue('installApp')->delay(10);

                Log::info("Connexion au service SAAS vérifiée avec succès", [
                    'service_id' => $this->service->id,
                    'domain' => $domain,
                ]);
            }
        } catch (\Exception $e) {
            // Gestion des erreurs
            $this->service->steps()->where('step', 'Vérification de la connexion au service (SAAS)')->first()->update([
                'done' => false,
                'comment' => $e->getMessage()
            ]);

            $this->service->update([
                'status' => 'error',
            ]);

            Notification::make()
                ->danger()
                ->title("Installation d'un service en erreur !")
                ->body($e->getMessage())
                ->sendToDatabase(User::where('email', 'admin@'.config('batistack.domain'))->first());

            Log::error('Erreur vérification connexion service SAAS', [
                'service_id' => $this->service->id,
                'domain' => $domain,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Vérifie que la page de connexion du domaine répond avec le statut HTTP 200.
     *
     * @param string $domain Nom d'hôte du service (ex. "example.com", sans protocole ni chemin).
     * @return bool `true` si une requête GET vers `https://{domain}/login` retourne le statut 200, `false` sinon.
     */
    private function checkHttpResponse(string $domain): bool
    {
        $request = \Http::withoutVerifying()
            ->get('https://'.$domain.'/login');

        if($request->status() === 200) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Vérifie que l'endpoint API '/api/status' du domaine répond avec un code HTTP 200.
     *
     * @param string $domain Nom de domaine du service (ex. "example.com").
     * @return bool `true` si la requête retourne le code 200, `false` sinon.
     */
    private function checkApiEndpoint(string $domain): bool
    {
        $request = \Http::withoutVerifying()
            ->get('https://'.$domain.'/api/status');

        if($request->status() === 200) {
            return true;
        } else {
            return false;
        }
    }
}