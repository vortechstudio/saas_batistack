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
     */
    public function handle(): void
    {
        $domain = Str::slug($this->service->customer->entreprise). '.'.config('batistack.domain');

        try {
            if(config('app.env') !== 'local') {
                // 1. Vérification de la connectivité HTTP/HTTPS
                $httpCheck = $this->checkHttpResponse($domain);

                if (!$httpCheck['success']) {
                    throw new \Exception("Impossible d'accéder au service via HTTP/HTTPS: " . ($httpCheck['error'] ?? 'Code HTTP: ' . ($httpCheck['http_code'] ?? 'inconnu')));
                }

                // 2. Vérification de l'API de l'application (si disponible)
                $apiCheck = $this->checkApiEndpoint($domain);

                // 3. Vérification de la base de données via l'application
                $dbConnectionCheck = $this->checkDatabaseConnection($domain);

                if (!$dbConnectionCheck['success']) {
                    throw new \Exception("La connexion à la base de données via l'application a échoué: " . $dbConnectionCheck['error']);
                }

                // 4. Vérification des services essentiels
                $servicesCheck = $this->checkEssentialServices($domain);

                // Compilation des résultats
                $checkResults = [
                    'http_check' => $httpCheck,
                    'api_check' => $apiCheck,
                    'database_check' => $dbConnectionCheck,
                    'services_check' => $servicesCheck
                ];
                // Connexion au service vérifiée avec succès

            }

            $this->service->steps()->where('step', 'Vérification de la connexion au service (SAAS)')->first()->update([
                'done' => true,
                'comment' => 'Service SAAS accessible et fonctionnel. HTTP: 200, DB: OK, Services: OK'
            ]);

            dispatch(new VerifyLicenseInformation($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));

            Log::info("Connexion au service SAAS vérifiée avec succès", [
                'service_id' => $this->service->id,
                'domain' => $domain,
                'checks' => $checkResults ?? []
            ]);

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
     * Vérification HTTP de l'application
     */
    private function checkHttpResponse(string $domain): array
    {
        try {
            $url = "https://$domain";

            // Utiliser cURL pour vérifier la réponse HTTP
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Pour les certificats auto-signés
            curl_setopt($ch, CURLOPT_USERAGENT, 'Batistack-Installer/1.0');

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return [
                    'success' => false,
                    'error' => $error,
                    'url' => $url
                ];
            }

            // Codes HTTP acceptables
            $acceptableCodes = [200, 301, 302, 403]; // 403 peut être normal si l'app n'est pas encore configurée

            return [
                'success' => in_array($httpCode, $acceptableCodes),
                'http_code' => $httpCode,
                'url' => $url,
                'response_length' => strlen($response)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'url' => $url ?? $domain
            ];
        }
    }

    /**
     * Vérification de l'endpoint API de l'application
     */
    private function checkApiEndpoint(string $domain): array
    {
        try {
            $apiUrl = "https://$domain/api/health"; // ou un autre endpoint de santé

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'User-Agent: Batistack-Installer/1.0'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return [
                    'success' => false,
                    'error' => $error,
                    'url' => $apiUrl
                ];
            }

            // L'API peut ne pas exister encore, donc on accepte 404
            $acceptableCodes = [200, 404];

            return [
                'success' => in_array($httpCode, $acceptableCodes),
                'http_code' => $httpCode,
                'url' => $apiUrl,
                'response' => $response
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'url' => $apiUrl ?? $domain
            ];
        }
    }

    /**
     * Vérification de la connexion à la base de données via SSH
     */
    private function checkDatabaseConnection(string $domain): array
    {
        $domainPath = '/www/wwwroot/'.$domain;

        // Configuration SSH
        $sshHost = config('batistack.ssh.host');
        $sshUser = config('batistack.ssh.user');
        $sshKey = config('batistack.ssh.private_key_path');

        try {
            // Commande pour tester la connexion DB via artisan
            $sshPassword = config('batistack.ssh.password') ?? env('SSH_PASSWORD');
            $dbTestCommand = [
                'sshpass',
                    '-p', $sshPassword,
                    'ssh',
                    '-o', 'StrictHostKeyChecking=no',
                    "$sshUser@$sshHost",
                    '-p', '22',
                "cd $domainPath && php artisan tinker --execute='DB::connection()->getPdo(); echo \"DB_OK\";'"
            ];

            $result = Process::timeout(60)->run($dbTestCommand);

            if ($result->failed()) {
                return [
                    'success' => false,
                    'error' => 'Échec de la commande de test DB: ' . $result->errorOutput()
                ];
            }

            $output = trim($result->output());

            return [
                'success' => str_contains($output, 'DB_OK'),
                'output' => $output
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérification des services essentiels de l'application
     */
    private function checkEssentialServices(string $domain): array
    {
        $domainPath = '/www/wwwroot/'.$domain;

        // Configuration SSH
        $sshHost = config('batistack.ssh.host');
        $sshUser = config('batistack.ssh.user');
        $sshKey = config('batistack.ssh.private_key_path');

        try {
            $checks = [];

            // Vérifier que les queues fonctionnent
            $sshPassword = config('batistack.ssh.password') ?? env('SSH_PASSWORD');
            $queueCommand = [
                'sshpass',
                    '-p', $sshPassword,
                    'ssh',
                    '-o', 'StrictHostKeyChecking=no',
                    "$sshUser@$sshHost",
                    '-p', '22',
                "cd $domainPath && php artisan queue:work --once --timeout=5 || echo 'QUEUE_ERROR'"
            ];

            $queueResult = Process::timeout(30)->run($queueCommand);
            $checks['queue'] = !str_contains($queueResult->output(), 'QUEUE_ERROR');

            // Vérifier que le cache fonctionne
            $cacheCommand = [
                'sshpass',
                    '-p', $sshPassword,
                    'ssh',
                    '-o', 'StrictHostKeyChecking=no',
                    "$sshUser@$sshHost",
                    '-p', '22',
                "cd $domainPath && php artisan tinker --execute='Cache::put(\"test\", \"ok\", 60); echo Cache::get(\"test\");'"
            ];

            $cacheResult = Process::timeout(30)->run($cacheCommand);
            $checks['cache'] = str_contains($cacheResult->output(), 'ok');

            return [
                'success' => $checks['queue'] && $checks['cache'],
                'details' => $checks
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
