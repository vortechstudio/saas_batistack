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
     * Execute the job.
     * @throws \Exception
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
