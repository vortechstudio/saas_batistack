<?php

namespace App\Jobs\Service\Install;

use App\Models\Customer\CustomerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Log;
use Process;

class VerifyInstallation implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private CustomerService $service)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $domain = Str::slug($this->service->customer->entreprise). '.'.config('batistack.domain');
        $domainPath = '/www/wwwroot/'.$domain;

        // Configuration SSH
        $sshHost = config('batistack.ssh.host');
        $sshUser = config('batistack.ssh.user');
        $sshKey = config('batistack.ssh.private_key_path');

        try {
            // Vérifications à effectuer
            $verifications = [
                // Vérifier que les fichiers principaux existent
                'files' => [
                    'composer.json',
                    'artisan',
                    '.env',
                    'public/index.php'
                ],
                // Vérifier que les dossiers principaux existent
                'directories' => [
                    'app',
                    'config',
                    'database',
                    'resources',
                    'storage'
                ],
                // Vérifier que l'application répond
                'http_check' => true
            ];

            // 1. Vérification des fichiers essentiels
            foreach ($verifications['files'] as $file) {
                // Construction de la commande SSH avec Process
                $sshCommand = [
                    'sshpass',
                    '-p', 'rbU89a-4',
                    'ssh',
                    '-o', 'StrictHostKeyChecking=no',
                    "$sshUser@$sshHost",
                    '-p', '22',

                ];

                $checkFileCommand = [
                    'sshpass',
                    '-p', 'rbU89a-4',
                    'ssh',
                    '-o', 'StrictHostKeyChecking=no',
                    "$sshUser@$sshHost",
                    '-p', '22',
                    "test -f $domainPath/$file && echo 'EXISTS' || echo 'MISSING'"
                ];

                $result = Process::timeout(30)->run($checkFileCommand);

                if ($result->failed() || trim($result->output()) !== 'EXISTS') {
                    throw new \Exception("Fichier manquant ou inaccessible: $file");
                }
            }

            // 2. Vérification des dossiers essentiels
            foreach ($verifications['directories'] as $directory) {
                $checkDirCommand = [
                    'sshpass',
                    '-p', 'rbU89a-4',
                    'ssh',
                    '-o', 'StrictHostKeyChecking=no',
                    "$sshUser@$sshHost",
                    '-p', '22',
                    "test -d $domainPath/$directory && echo 'EXISTS' || echo 'MISSING'"
                ];

                $result = Process::timeout(30)->run($checkDirCommand);

                if ($result->failed() || trim($result->output()) !== 'EXISTS') {
                    throw new \Exception("Dossier manquant ou inaccessible: $directory");
                }
            }

            // 3. Vérifier que les permissions sont correctes
            $permissionsCommand = [
                'sshpass',
                    '-p', 'rbU89a-4',
                    'ssh',
                    '-o', 'StrictHostKeyChecking=no',
                    "$sshUser@$sshHost",
                    '-p', '22',
                "ls -la $domainPath/storage && ls -la $domainPath/bootstrap/cache"
            ];

            $permissionsResult = Process::timeout(30)->run($permissionsCommand);

            if ($permissionsResult->failed()) {
                throw new \Exception("Impossible de vérifier les permissions des dossiers storage et bootstrap/cache");
            }

            // 4. Vérifier que l'application Laravel fonctionne
            $artisanCommand = [
                'sshpass',
                    '-p', 'rbU89a-4',
                    'ssh',
                    '-o', 'StrictHostKeyChecking=no',
                    "$sshUser@$sshHost",
                    '-p', '22',
                "cd $domainPath && php artisan --version"
            ];

            $artisanResult = Process::timeout(60)->run($artisanCommand);

            if ($artisanResult->failed()) {
                throw new \Exception("L'application Laravel ne répond pas correctement: " . $artisanResult->errorOutput());
            }

            // 5. Vérification HTTP (optionnelle)
            if ($verifications['http_check']) {
                $httpCheck = $this->checkHttpResponse($domain);
                if (!$httpCheck['success']) {
                    Log::warning("Vérification HTTP échouée pour $domain", $httpCheck);
                    // Ne pas faire échouer l'installation pour ça, juste logger
                }
            }

            // 6. Vérifier la base de données
            $dbCheckCommand = [
                'sshpass',
                    '-p', 'rbU89a-4',
                    'ssh',
                    '-o', 'StrictHostKeyChecking=no',
                    "$sshUser@$sshHost",
                    '-p', '22',
                "cd $domainPath && php artisan migrate:status"
            ];

            $dbResult = Process::timeout(60)->run($dbCheckCommand);

            if ($dbResult->failed()) {
                throw new \Exception("Impossible de vérifier l'état de la base de données: " . $dbResult->errorOutput());
            }

            // Installation vérifiée avec succès
            $this->service->steps()->where('step', 'Vérification de l\'installation')->first()->update([
                'done' => true,
                'comment' => 'Installation vérifiée avec succès. Laravel version: ' . trim($artisanResult->output())
            ]);
            dispatch(new VerifyServiceConnection($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));

            Log::info("Installation vérifiée avec succès pour le service", [
                'service_id' => $this->service->id,
                'domain' => $domain,
                'laravel_version' => trim($artisanResult->output())
            ]);

        } catch (\Exception $e) {
            // Gestion des erreurs
            $this->service->steps()->where('step', 'Vérification de l\'installation')->first()->update([
                'done' => false,
                'comment' => $e->getMessage()
            ]);

            Log::error('Erreur vérification installation', [
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
}
