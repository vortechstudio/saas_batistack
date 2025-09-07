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

class ActivateLicenseModule implements ShouldQueue
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

            dispatch(new NotifyClientByMail($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'activation des modules de licence', [
                'service_id' => $this->service->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->danger()
                ->title("Installation d'un service en erreur !")
                ->body($e->getMessage())
                ->sendToDatabase(User::where('email', 'admin@'.config('batistack.domain'))->first());

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
            escapeshellarg($sshConfig['user']),
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
}
