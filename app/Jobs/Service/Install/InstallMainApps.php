<?php

namespace App\Jobs\Service\Install;

use App\Models\Customer\CustomerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class InstallMainApps implements ShouldQueue
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
        $gitRepo = 'https://github.com/vortechstudio/batistack2.git';

        // Configuration SSH
        $sshHost = config('batistack.ssh.host');
        $sshUser = config('batistack.ssh.user');
        $sshPassword = config('batistack.ssh.password', 'rbU89a-4');

        try {
            // Optimisation : génération directe du contenu .env sans fichier temporaire
            $envContent = $this->generateEnvContent($domain);
            $envTempPath = base_path('.env.temp');
            file_put_contents($envTempPath, $envContent);

            // Optimisation : commandes groupées et plus efficaces
            $this->executeInstallationCommands($sshHost, $sshUser, $sshPassword, $domain, $domainPath, $gitRepo, $envTempPath);

            // Installation réussie
            $this->service->steps()->where('step', 'Installation de l\'application principal')->first()->update([
                'done' => true,
                'comment' => 'Application installée avec succès via Process'
            ]);

            dispatch(new VerifyInstallation($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));

        } catch (\Exception $e) {
            // Gestion des erreurs
            $this->service->steps()->where('step', 'Installation de l\'application principal')->first()->update([
                'done' => false,
                'comment' => $e->getMessage()
            ]);

            $this->service->update([
                'status' => 'error',
            ]);

            Log::error('Erreur installation application', [
                'service_id' => $this->service->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Génère le contenu du fichier .env de manière optimisée
     */
    private function generateEnvContent(string $domain): string
    {
        $envTemplate = file_get_contents(base_path('.env.example'));

        $replacements = [
            'DB_DATABASE=localhost' => 'DB_DATABASE=db_'.$domain,
            'DB_USERNAME=root' => 'DB_USERNAME=db_'.$domain,
            'DB_PASSWORD=' => 'DB_PASSWORD=db_'.$domain,
            'APP_URL=http://localhost' => 'APP_URL=https://'.$domain,
            'APP_DOMAIN=' => 'APP_DOMAIN='.config('batistack.domain'),
            '# REDIS_PASSWORD=null' => 'REDIS_PASSWORD='.config('batistack.ssh.password', 'rbU89a-4'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $envTemplate);
    }

    /**
     * Exécute les commandes d'installation de manière optimisée
     */
    private function executeInstallationCommands(string $sshHost, string $sshUser, string $sshPassword, string $domain, string $domainPath, string $gitRepo, string $envTempPath): void
    {
        // Optimisation : commandes groupées en une seule connexion SSH
        $bashScript = $this->buildInstallationScript($domain, $domainPath, $gitRepo, $envTempPath);

        $sshCommand = [
            'sshpass',
            '-p', $sshPassword,
            'ssh',
            '-o', 'StrictHostKeyChecking=no',
            '-o', 'ConnectTimeout=30',
            "$sshUser@$sshHost",
            '-p', '22',
            'bash', '-c', $bashScript
        ];

        $result = Process::timeout(600)->run($sshCommand); // 10 minutes timeout

        if ($result->failed()) {
            throw new \Exception(
                "Erreur lors de l'installation: " .
                "\nCode de sortie: " . $result->exitCode() .
                "\nErreur: " . $result->errorOutput() .
                "\nSortie: " . $result->output()
            );
        }

        Log::info("Installation SSH exécutée avec succès", [
            'domain' => $domain,
            'output' => $result->output()
        ]);
    }

    /**
     * Construit le script bash pour l'installation
     */
    private function buildInstallationScript(string $domain, string $domainPath, string $gitRepo, string $envTempPath): string
    {
        $envContent = base64_encode(file_get_contents($envTempPath));

        return "
            set -e  # Arrêter en cas d'erreur
            cd /www/wwwroot/
            rm -rf {$domain}
            git clone {$gitRepo} {$domain}
            cd {$domainPath}
            echo '{$envContent}' | base64 -d > .env
            composer install --no-interaction --optimize-autoloader
            php artisan key:generate --force
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            php artisan app:install --license={$this->service->service_code}
        ";
    }
}
