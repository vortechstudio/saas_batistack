<?php

namespace App\Jobs\Service;

use App\Enum\Commerce\OrderStatusEnum;
use App\Models\Commerce\Order;
use App\Models\Customer\CustomerService;
use App\Services\NebuloService;
use App\Services\PanelService;
use Filament\Actions\Action;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Process;

class InitService implements ShouldQueue
{
    use Queueable;
    private $panel;

    /**
     * Create a new job instance.
     */
    public function __construct(public CustomerService $service, public Order $order)
    {
        $this->panel = new PanelService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /**
         * Passage de la commande à livré
         * Initialisation des étapes d'installation du service
         * Création de domaine
         * Vérification du domaine
         * Vérification de la base de donnée
         * Ouverture des droits de stockages pour le service
         * Installation de l'application principal
         * Vérification de l'installation
         * Vérification de la connexion au service (SAAS)
         * Vérification des informations relatives à la license
         * Activation des modules de la license
         * Notification au client par email
         * Passage du service à OK
         */
        $this->passOrderToDelivered();
        $this->initServiceSteps();
        $this->initDomain();
        $this->verifyDomain();
        $this->verifyDatabase();
        $this->installMainApps();
        $this->verifyInstallation();
        $this->verifyServiceConnection();
        $this->verifyLicenseInformation();
        $this->activateLicenseModules();
        $this->notifyClientByEmail();
        $this->passServiceToOk();
    }

    private function passOrderToDelivered()
    {
        $this->order->update([
            'status' => OrderStatusEnum::DELIVERED,
        ]);
    }

    private function initServiceSteps()
    {
        $this->service->steps()->createMany([
            [
                'type' => 'license',
                'step' => 'Création de domaine',
            ],
            [
                'type' => 'license',
                'step' => 'Vérification du domaine',
            ],
            [
                'type' => 'license',
                'step' => 'Vérification de la base de donnée',
            ],
            [
                'type' => 'license',
                'step' => 'Installation de l\'application principal',
            ],
            [
                'type' => 'license',
                'step' => 'Vérification de l\'installation',
            ],
            [
                'type' => 'license',
                'step' => 'Vérification de la connexion au service (SAAS)',
            ],
            [
                'type' => 'license',
                'step' => 'Vérification des informations relatives à la license',
            ],
            [
                'type' => 'license',
                'step' => 'Activation des modules de la license',
            ],
        ]);
    }

    private function initDomain()
    {
        $domain = Str::slug($this->service->customer->entreprise). '.'.config('batistack.domain');

        try {
            $this->panel->client->addSite(
                domain: $domain,
                path: '/www/wwwroot/'.$domain,
                runPath: '/public',
                phpVersion: '83',
                sql: true,
                databaseUsername: 'db_'.$domain,
                databasePassword: 'db_'.$domain,
                setSsl: 1,
                forceSsl: 1
            );
            $this->service->steps()->where('step', 'Création de domaine')->first()->update([
                'done' => true,
            ]);
        } catch (\Exception $e) {
            $this->service->steps()->where('step', 'Création de domaine')->first()->update([
                'done' => false,
                'comment' => $e->getMessage(),
            ]);
        }

    }

    private function verifyDomain()
    {
        $domain = Str::slug($this->service->customer->entreprise). '.'.config('batistack.domain');
        try {
            $this->panel->client->fetchSites(10, 1, $domain);
            $this->service->steps()->where('step', 'Vérification du domaine')->first()->update([
                'done' => true,
            ]);
        } catch (\Exception $e) {
            $this->service->steps()->where('step', 'Vérification du domaine')->first()->update([
                'done' => false,
                'comment' => $e->getMessage(),
            ]);
        }
    }

    public function verifyDatabase()
    {
        $domain = Str::slug($this->service->customer->entreprise). '.'.config('batistack.domain');
        $database = 'db_'.$domain;
        try {
            // Comment vérifier qu'une base de donnée existe pour le domaine
            $this->panel->client->fetchDatabases(10, 1, $database);
            $this->service->steps()->where('step', 'Vérification de la base de donnée')->first()->update([
                'done' => true,
            ]);
        } catch (\Exception $e) {
            $this->service->steps()->where('step', 'Vérification de la base de donnée')->first()->update([
                'done' => false,
                'comment' => $e->getMessage(),
            ]);
        }
    }

    public function installMainApps()
    {
        $domain = Str::slug($this->service->customer->entreprise). '.'.config('batistack.domain');
        $domainPath = '/www/wwwroot/'.$domain;
        $gitRepo = 'https://github.com/vortechstudio/batistack2.git';

        // Configuration SSH
        $sshHost = config('batistack.ssh.host');
        $sshUser = config('batistack.ssh.user');
        $sshKey = config('batistack.ssh.private_key_path');

        try {

            // Commandes à exécuter séquentiellement
            $commands = [
                ['cd', $domainPath],
                ['git', 'clone', $gitRepo, '.'],
                ['php', 'artisan', 'app:install', '--license='.$this->service->service_code]
            ];

            foreach ($commands as $command) {
                // Construction de la commande SSH avec Process
                $sshCommand = [
                    'sshpass',
                    '-p', 'rbU89a-4',
                    'ssh',
                    '-o', 'StrictHostKeyChecking=no',
                    "$sshUser@$sshHost",
                    '-p', '22',
                    implode(' ', array_map('escapeshellarg', $command))
                ];

                // Exécution avec Process
                $result = Process::timeout(300)->run($sshCommand); // 5 minutes timeout

                if ($result->failed()) {
                    throw new \Exception(
                        "Erreur lors de l'exécution de la commande: " . implode(' ', $command) .
                        "\nCode de sortie: " . $result->exitCode() .
                        "\nErreur: " . $result->errorOutput() .
                        "\nSortie: " . $result->output()
                    );
                }

                // Log de chaque étape réussie
                Log::info("Commande SSH exécutée avec succès", [
                    'command' => implode(' ', $command),
                    'output' => $result->output()
                ]);
            }

            // Installation réussie
            $this->service->steps()->where('step', 'Installation de l\'application principal')->first()->update([
                'done' => true,
                'comment' => 'Application installée avec succès via Process'
            ]);

        } catch (\Exception $e) {
            // Gestion des erreurs
            $this->service->steps()->where('step', 'Installation de l\'application principal')->first()->update([
                'done' => false,
                'comment' => $e->getMessage()
            ]);

            Log::error('Erreur installation application', [
                'service_id' => $this->service->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function verifyInstallation()
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

    /**
     * Vérification de la connexion au service (SAAS)
     * Cette fonction vérifie que l'application SAAS est accessible et répond correctement
     */
    public function verifyServiceConnection()
    {
        $domain = Str::slug($this->service->customer->entreprise). '.'.config('batistack.domain');

        try {
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
            $this->service->steps()->where('step', 'Vérification de la connexion au service (SAAS)')->first()->update([
                'done' => true,
                'comment' => 'Service SAAS accessible et fonctionnel. HTTP: ' . $httpCheck['http_code'] . ', DB: OK'
            ]);

            Log::info("Connexion au service SAAS vérifiée avec succès", [
                'service_id' => $this->service->id,
                'domain' => $domain,
                'checks' => $checkResults
            ]);

        } catch (\Exception $e) {
            // Gestion des erreurs
            $this->service->steps()->where('step', 'Vérification de la connexion au service (SAAS)')->first()->update([
                'done' => false,
                'comment' => $e->getMessage()
            ]);

            Log::error('Erreur vérification connexion service SAAS', [
                'service_id' => $this->service->id,
                'domain' => $domain,
                'error' => $e->getMessage()
            ]);

            throw $e;
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
            $dbTestCommand = [
                'sshpass',
                    '-p', 'rbU89a-4',
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
            $queueCommand = [
                'sshpass',
                    '-p', 'rbU89a-4',
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
                    '-p', 'rbU89a-4',
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

    private function passServiceToOk()
    {
        $this->service->update([
            'status' => 'ok',
        ]);
    }

    /**
     * Vérification des informations relatives à la license
     * Cette fonction vérifie que les informations de licence sont correctes et cohérentes
     */
    public function verifyLicenseInformation()
    {
        try {
            // 1. Vérification des informations de base du service
            $serviceValidation = $this->validateServiceInformation();

            if (!$serviceValidation['valid']) {
                throw new \Exception("Informations de service invalides: " . $serviceValidation['error']);
            }

            // 2. Vérification de la cohérence avec Stripe
            $stripeValidation = $this->validateStripeSubscription();

            if (!$stripeValidation['valid']) {
                throw new \Exception("Incohérence avec l'abonnement Stripe: " . $stripeValidation['error']);
            }

            // 3. Vérification des features du produit
            $featuresValidation = $this->validateProductFeatures();

            if (!$featuresValidation['valid']) {
                throw new \Exception("Problème avec les fonctionnalités du produit: " . $featuresValidation['error']);
            }

            // 4. Vérification des dates de licence
            $datesValidation = $this->validateLicenseDates();

            if (!$datesValidation['valid']) {
                throw new \Exception("Dates de licence invalides: " . $datesValidation['error']);
            }

            // 5. Vérification du statut de paiement
            $paymentValidation = $this->validatePaymentStatus();

            if (!$paymentValidation['valid']) {
                Log::warning("Problème de paiement détecté", $paymentValidation);
                // Ne pas faire échouer pour les problèmes de paiement, juste logger
            }

            // Compilation des résultats de validation
            $validationResults = [
                'service' => $serviceValidation,
                'stripe' => $stripeValidation,
                'features' => $featuresValidation,
                'dates' => $datesValidation,
                'payment' => $paymentValidation
            ];

            // Vérification des informations de licence réussie
            $this->service->steps()->where('step', 'Vérification des informations relatives à la license')->first()->update([
                'done' => true,
                'comment' => 'Licence valide. Produit: ' . $this->service->product->name . ', Expiration: ' . $this->service->expirationDate->format('d/m/Y')
            ]);

            Log::info("Informations de licence vérifiées avec succès", [
                'service_id' => $this->service->id,
                'service_code' => $this->service->service_code,
                'product_name' => $this->service->product->name,
                'validation_results' => $validationResults
            ]);

        } catch (\Exception $e) {
            // Gestion des erreurs
            $this->service->steps()->where('step', 'Vérification des informations relatives à la license')->first()->update([
                'done' => false,
                'comment' => $e->getMessage()
            ]);

            Log::error('Erreur vérification informations licence', [
                'service_id' => $this->service->id,
                'service_code' => $this->service->service_code,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Validation des informations de base du service
     */
    private function validateServiceInformation(): array
    {
        try {
            // Vérifier que le service a toutes les informations requises
            $requiredFields = ['service_code', 'creationDate', 'expirationDate', 'customer_id', 'product_id'];

            foreach ($requiredFields as $field) {
                if (empty($this->service->$field)) {
                    return [
                        'valid' => false,
                        'error' => "Champ requis manquant: $field"
                    ];
                }
            }

            // Vérifier que le service_code est unique
            $duplicateService = \App\Models\Customer\CustomerService::where('service_code', $this->service->service_code)
                ->where('id', '!=', $this->service->id)
                ->first();

            if ($duplicateService) {
                return [
                    'valid' => false,
                    'error' => "Code de service en doublon: {$this->service->service_code}"
                ];
            }

            // Vérifier que le produit existe et est actif
            if (!$this->service->product || !$this->service->product->active) {
                return [
                    'valid' => false,
                    'error' => "Produit inexistant ou inactif"
                ];
            }

            // Vérifier que le client existe
            if (!$this->service->customer) {
                return [
                    'valid' => false,
                    'error' => "Client inexistant"
                ];
            }

            return [
                'valid' => true,
                'service_code' => $this->service->service_code,
                'product_name' => $this->service->product->name,
                'customer_code' => $this->service->customer->code_client
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validation de la cohérence avec l'abonnement Stripe
     */
    private function validateStripeSubscription(): array
    {
        try {
            if (empty($this->service->stripe_subscription_id)) {
                return [
                    'valid' => false,
                    'error' => "ID d'abonnement Stripe manquant"
                ];
            }

            // Récupérer l'abonnement Stripe
            $stripeService = app(\App\Services\Stripe\StripeService::class);
            $subscription = $stripeService->client->subscriptions->retrieve($this->service->stripe_subscription_id);

            // Vérifier le statut de l'abonnement
            $validStatuses = ['active', 'trialing', 'past_due'];
            if (!in_array($subscription->status, $validStatuses)) {
                return [
                    'valid' => false,
                    'error' => "Statut d'abonnement Stripe invalide: {$subscription->status}"
                ];
            }

            // Vérifier que le client Stripe correspond
            if ($subscription->customer !== $this->service->customer->stripe_customer_id) {
                return [
                    'valid' => false,
                    'error' => "Incohérence client entre service et abonnement Stripe"
                ];
            }

            // Vérifier que le produit correspond
            $stripeProductId = null;
            foreach ($subscription->items->data as $item) {
                $productPrice = \App\Models\Product\ProductPrice::where('stripe_price_id', $item->price->id)->first();
                if ($productPrice && $productPrice->product_id === $this->service->product_id) {
                    $stripeProductId = $item->price->product;
                    break;
                }
            }

            if (!$stripeProductId || $stripeProductId !== $this->service->product->stripe_product_id) {
                return [
                    'valid' => false,
                    'error' => "Produit Stripe ne correspond pas au service"
                ];
            }

            return [
                'valid' => true,
                'subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status,
                'current_period_end' => $subscription->current_period_end
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => "Erreur lors de la vérification Stripe: " . $e->getMessage()
            ];
        }
    }

    /**
     * Validation des fonctionnalités du produit
     */
    private function validateProductFeatures(): array
    {
        try {
            // Récupérer les informations du produit depuis Stripe
            $stripeProduct = $this->service->product->getInfoProductStripe();

            // Vérifier les métadonnées importantes
            $requiredMetadata = ['storage_limit'];
            foreach ($requiredMetadata as $key) {
                if (!isset($stripeProduct->metadata->$key)) {
                    return [
                        'valid' => false,
                        'error' => "Métadonnée manquante dans le produit Stripe: $key"
                    ];
                }
            }

            // Vérifier que les features du produit sont cohérentes
            $productFeatures = $this->service->product->features;

            if ($productFeatures->isEmpty()) {
                Log::warning("Aucune fonctionnalité définie pour le produit", [
                    'product_id' => $this->service->product->id,
                    'product_name' => $this->service->product->name
                ]);
            }

            return [
                'valid' => true,
                'features_count' => $productFeatures->count(),
                'storage_limit' => $stripeProduct->metadata->storage_limit ?? 'non défini',
                'stripe_product_active' => $stripeProduct->active
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => "Erreur lors de la validation des fonctionnalités: " . $e->getMessage()
            ];
        }
    }

    /**
     * Validation des dates de licence
     */
    private function validateLicenseDates(): array
    {
        try {
            $now = now();
            $creationDate = $this->service->creationDate;
            $expirationDate = $this->service->expirationDate;
            $nextBillingDate = $this->service->nextBillingDate;

            // Vérifier que la date de création n'est pas dans le futur
            if ($creationDate->isFuture()) {
                return [
                    'valid' => false,
                    'error' => "Date de création dans le futur: {$creationDate->format('d/m/Y')}"
                ];
            }

            // Vérifier que la date d'expiration est après la date de création
            if ($expirationDate->isBefore($creationDate)) {
                return [
                    'valid' => false,
                    'error' => "Date d'expiration antérieure à la date de création"
                ];
            }

            // Vérifier que la prochaine date de facturation est cohérente
            if ($nextBillingDate->isBefore($creationDate)) {
                return [
                    'valid' => false,
                    'error' => "Date de facturation incohérente"
                ];
            }

            // Calculer le statut basé sur les dates
            $isExpired = $expirationDate->isPast();
            $daysUntilExpiration = $now->diffInDays($expirationDate, false);

            return [
                'valid' => true,
                'is_expired' => $isExpired,
                'days_until_expiration' => $daysUntilExpiration,
                'creation_date' => $creationDate->format('d/m/Y'),
                'expiration_date' => $expirationDate->format('d/m/Y'),
                'next_billing_date' => $nextBillingDate->format('d/m/Y')
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => "Erreur lors de la validation des dates: " . $e->getMessage()
            ];
        }
    }

    /**
     * Validation du statut de paiement
     */
    private function validatePaymentStatus(): array
    {
        try {
            // Vérifier que le client a des moyens de paiement
            $hasPaymentMethods = $this->service->customer->hasPaymentMethods();

            if (!$hasPaymentMethods) {
                return [
                    'valid' => false,
                    'error' => "Aucun moyen de paiement configuré pour le client",
                    'warning' => true // Marquer comme warning plutôt qu'erreur critique
                ];
            }

            // Vérifier le statut du service
            $serviceStatus = $this->service->status;
            $validStatuses = [\App\Enum\Customer\CustomerServiceStatusEnum::OK, \App\Enum\Customer\CustomerServiceStatusEnum::PENDING];

            if (!in_array($serviceStatus, $validStatuses)) {
                return [
                    'valid' => false,
                    'error' => "Statut de service problématique: {$serviceStatus->label()}",
                    'warning' => $serviceStatus === \App\Enum\Customer\CustomerServiceStatusEnum::UNPAID
                ];
            }

            return [
                'valid' => true,
                'service_status' => $serviceStatus->label(),
                'has_payment_methods' => $hasPaymentMethods
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => "Erreur lors de la validation du paiement: " . $e->getMessage()
            ];
        }
    }

        /**
     * Activation des modules de la license
     */
    private function activateLicenseModules(): void
    {
        try {
            // Mise à jour du statut de l'étape

            Log::info('Début de l\'activation des modules de licence', [
                'service_id' => $this->service->id,
                'product_id' => $this->service->product->id
            ]);

            // Récupérer les fonctionnalités du produit
            $features = $this->service->product->features;

            if ($features->isEmpty()) {
                Log::warning('Aucune fonctionnalité à activer pour ce produit', [
                    'product_id' => $this->service->product->id
                ]);
                return;
            }

            // Préparer la configuration des modules
            $moduleConfig = $this->prepareModuleConfiguration($features);

            // Créer le fichier de configuration temporaire
            $configContent = $this->generateModuleConfigFile($moduleConfig);
            $tempConfigFile = tempnam(sys_get_temp_dir(), 'batistack_modules_');
            file_put_contents($tempConfigFile, $configContent);

            try {
                // Transférer le fichier de configuration vers le serveur
                $this->transferConfigurationFile($tempConfigFile);

                // Activer les modules via SSH
                $this->activateModulesOnServer($moduleConfig);

                // Vérifier l'activation des modules
                $this->verifyModuleActivation($features);

                Log::info('Modules de licence activés avec succès', [
                    'service_id' => $this->service->id,
                    'activated_modules' => $features->pluck('slug')->toArray()
                ]);


            } finally {
                // Nettoyer le fichier temporaire
                if (file_exists($tempConfigFile)) {
                    unlink($tempConfigFile);
                }
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'activation des modules de licence', [
                'service_id' => $this->service->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Préparer la configuration des modules
     */
    private function prepareModuleConfiguration($features): array
    {
        $config = [
            'license_key' => $this->service->uuid,
            'product_id' => $this->service->product->id,
            'customer_id' => $this->service->customer->id,
            'domain' => $this->service->domain,
            'modules' => [],
            'limits' => []
        ];

        foreach ($features as $feature) {
            $config['modules'][$feature->slug] = [
                'name' => $feature->name,
                'enabled' => true,
                'description' => $feature->description
            ];
        }

        // Ajouter les limites depuis Stripe si disponibles
        try {
            $stripeProduct = $this->service->product->getInfoProductStripe();
            if (isset($stripeProduct->metadata)) {
                foreach ($stripeProduct->metadata as $key => $value) {
                    if (str_ends_with($key, '_limit')) {
                        $config['limits'][$key] = $value;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Impossible de récupérer les métadonnées Stripe', [
                'error' => $e->getMessage()
            ]);
        }

        return $config;
    }

    /**
     * Générer le contenu du fichier de configuration
     */
    private function generateModuleConfigFile(array $config): string
    {
        return json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Transférer le fichier de configuration vers le serveur
     */
    private function transferConfigurationFile(string $localFile): void
    {
        $sshConfig = config('batistack.ssh');
        $remoteConfigPath = '/var/www/html/config/license.json';

        // Commande SCP pour transférer le fichier
        $scpCommand = sprintf(
            'scp -i %s -o StrictHostKeyChecking=no %s %s@%s:%s',
            escapeshellarg($sshConfig['private_key_path']),
            escapeshellarg($localFile),
            escapeshellarg($sshConfig['username']),
            escapeshellarg($sshConfig['host']),
            escapeshellarg($remoteConfigPath)
        );

        $process = Process::run($scpCommand);

        if ($process->failed()) {
            throw new \Exception('Échec du transfert du fichier de configuration: ' . $process->errorOutput());
        }

        Log::info('Fichier de configuration transféré avec succès', [
            'remote_path' => $remoteConfigPath
        ]);
    }

    /**
     * Activer les modules sur le serveur distant
     */
    private function activateModulesOnServer(array $moduleConfig): void
    {
        $sshConfig = config('batistack.ssh');

        // Commandes d'activation des modules
        $commands = [
            'cd /var/www/html',
            'php artisan config:cache',
            'php artisan license:activate',
            'php artisan module:enable --all',
            'php artisan cache:clear'
        ];

        foreach ($commands as $command) {
            $sshCommand = sprintf(
                'ssh -i %s -o StrictHostKeyChecking=no %s@%s "%s"',
                escapeshellarg($sshConfig['private_key_path']),
                escapeshellarg($sshConfig['username']),
                escapeshellarg($sshConfig['host']),
                escapeshellarg($command)
            );

            $process = Process::run($sshCommand);

            if ($process->failed()) {
                throw new \Exception("Échec de la commande '$command': " . $process->errorOutput());
            }

            Log::info('Commande d\'activation exécutée', [
                'command' => $command,
                'output' => $process->output()
            ]);
        }
    }

    /**
     * Vérifier l'activation des modules
     */
    private function verifyModuleActivation($features): void
    {
        $sshConfig = config('batistack.ssh');

        // Vérifier le statut des modules
        $verifyCommand = sprintf(
            'ssh -i %s -o StrictHostKeyChecking=no %s@%s "cd /var/www/html && php artisan module:status"',
            escapeshellarg($sshConfig['private_key_path']),
            escapeshellarg($sshConfig['username']),
            escapeshellarg($sshConfig['host'])
        );

        $process = Process::run($verifyCommand);

        if ($process->failed()) {
            throw new \Exception('Impossible de vérifier le statut des modules: ' . $process->errorOutput());
        }

        $moduleStatus = $process->output();

        // Vérifier que chaque fonctionnalité est bien activée
        foreach ($features as $feature) {
            if (!str_contains($moduleStatus, $feature->slug)) {
                Log::warning('Module non trouvé dans le statut', [
                    'module' => $feature->slug,
                    'status_output' => $moduleStatus
                ]);
            }
        }

        Log::info('Vérification des modules terminée', [
            'module_status' => $moduleStatus
        ]);
    }

        /**
     * Notification au client par email
     */
    private function notifyClientByEmail(): void
    {
        try {
            // Mise à jour du statut de l'étape

            Log::info('Début de l\'envoi de notification email au client', [
                'service_id' => $this->service->id,
                'customer_id' => $this->service->customer->id,
                'customer_email' => $this->service->customer->user->email
            ]);

            // Préparer les détails d'installation pour l'email
            $installationDetails = $this->prepareInstallationDetails();

            // Envoyer la notification Filament (base de données)
            $this->sendDatabaseNotification();

            // Envoyer la notification email
            $this->sendEmailNotification($installationDetails);

            Log::info('Notification email envoyée avec succès', [
                'service_id' => $this->service->id,
                'customer_email' => $this->service->customer->user->email
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de la notification email', [
                'service_id' => $this->service->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Préparer les détails d'installation pour l'email
     */
    private function prepareInstallationDetails(): array
    {
        $details = [
            'installation_completed' => true,
            'modules_activated' => [],
            'features' => [],
            'installation_date' => now(),
            'domain_configured' => !empty($this->service->domain)
        ];

        // Récupérer les fonctionnalités du produit
        if ($this->service->product->features->isNotEmpty()) {
            $details['features'] = $this->service->product->features->map(function ($feature) {
                return [
                    'name' => $feature->name,
                    'description' => $feature->description
                ];
            })->toArray();

            $details['modules_activated'] = $this->service->product->features->pluck('name')->toArray();
        }

        // Ajouter des informations depuis les étapes précédentes
        $completedSteps = $this->service->steps()->where('status', 'completed')->get();
        $details['completed_steps'] = $completedSteps->pluck('step_name')->toArray();

        // Informations sur les limites du produit
        try {
            $stripeProduct = $this->service->product->getInfoProductStripe();
            if (isset($stripeProduct->metadata)) {
                $details['limits'] = [];
                foreach ($stripeProduct->metadata as $key => $value) {
                    if (str_ends_with($key, '_limit')) {
                        $details['limits'][$key] = $value;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Impossible de récupérer les métadonnées Stripe pour l\'email', [
                'error' => $e->getMessage()
            ]);
        }

        return $details;
    }

    /**
     * Envoyer la notification en base de données (Filament)
     */
    private function sendDatabaseNotification(): void
    {
        \Filament\Notifications\Notification::make()
            ->title('Service Batistack initialisé')
            ->body('Votre service Batistack est maintenant prêt à être utilisé sur le domaine: ' . $this->service->domain)
            ->success()
            ->icon('heroicon-o-check-circle')
            ->actions([
                Action::make('view_service')
                    ->label('Voir le service')
                    ->url(route('client.dashboard'))
                    ->button()
            ])
            ->sendToDatabase($this->service->customer->user);
    }

    /**
     * Envoyer la notification email
     */
    private function sendEmailNotification(array $installationDetails): void
    {
        // Utiliser la classe de notification Laravel
        $this->service->customer->user->notify(
            new \App\Notifications\Service\ServiceInitialized(
                service: $this->service,
                installationDetails: $installationDetails
            )
        );
    }
}


