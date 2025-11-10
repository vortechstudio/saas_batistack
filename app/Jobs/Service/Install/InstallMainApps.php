<?php

namespace App\Jobs\Service\Install;

use App\Models\Customer\CustomerService;
use App\Models\User;
use App\Services\Forge;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
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
        $database = 'db_'.Str::slug($this->service->customer->entreprise);
        $serverId = collect(app(\App\Services\Forge::class)->client->servers())->first()->id;
        $siteId = collect(app(Forge::class)->client->sites($serverId))->where('name', $domain)->first()->id;

        if (config('app.env') == 'local') {
            $this->service->steps()->where('step', 'Installation des applications principales')->first()?->update([
                'done' => true,
            ]);
            dispatch(new VerifyInstallation($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));
        } else {
            try {
                // Lancement du déployment
                $deployInit = app(Forge::class)->client->deploySite($serverId, $siteId);

                // Boucle de la fonction "fetchDeploystatus" jusqu'à avoir le status "finished"
                $deployStatus = '';
                while ($deployStatus != 'finished') {
                    $deployStatus = $this->fetchDeployStatus($serverId, $siteId, $deployInit->id);
                    if ($deployStatus == 'failed') {
                        Log::error('Deploy failed');
                        break;
                    }
                    sleep(5);
                }

                $command = app(Forge::class)->client->executeSiteCommand($serverId, $siteId, [
                    'php artisan app:install '.$this->service->service_code
                ]);

                if ($command) {
                    $this->service->steps()->where('step', 'Installation des applications principales')->first()?->update([
                        'done' => true,
                    ]);
                    dispatch(new VerifyInstallation($this->service))->onQueue('installApp');
                } else {
                    Notification::make()
                        ->danger()
                        ->title("Installation d'un service en erreur !")
                        ->body("L'application principal est rester en cours d'installation")
                        ->sendToDatabase(User::where('email', 'admin@'.config('batistack.domain'))->first());

                    $this->service->steps()->where('step', 'Vérification de la base de donnée')->first()?->update([
                        'done' => false,
                        'comment' => "L'application principal est rester en cours d'installation",
                    ]);

                    $this->service->update([
                        'status' => 'error',
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Erreur lors de l\'installation des applications principales');
                $this->service->steps()->where('step', 'Vérification de la base de donnée')->first()?->update([
                    'done' => false,
                    'comment' => $e->getMessage(),
                ]);
                Notification::make()
                    ->danger()
                    ->title("Installation d'un service en erreur !")
                    ->body($e->getMessage())
                    ->sendToDatabase(User::where('email', 'admin@'.config('batistack.domain'))->first());
                $this->service->update([
                    'status' => 'error',
                ]);
            }
        }
    }

    public function fetchDeployStatus(int $serverId, int $siteId, int $deployId)
    {
        $status = app(Forge::class)->client->deploymentHistoryDeployment($serverId, $siteId, $deployId);
        return $status->data->attributes->status;
    }

}
