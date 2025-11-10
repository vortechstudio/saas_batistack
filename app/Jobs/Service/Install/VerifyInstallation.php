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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

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


        if (config('app.env') === 'local') {
            $step = $this->service->steps()
                ->firstOrCreate(['step' => "Vérification de l'installation"], ['done' => false, 'comment' => null]);
            $step->update([
                'done' => true,
                'comment' => 'Installation vérifiée avec succès.',
            ]);
            dispatch(new VerifyServiceConnection($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));

            Log::info("Installation vérifiée avec succès pour le service", [
                'service_id' => $this->service->id,
                'domain' => $domain,
            ]);
        } else {
            try {
                $serverId = collect(app(\App\Services\Forge::class)->client->servers())->first()->id;
                $siteId = collect(app(Forge::class)->client->sites($serverId))->where('name', $domain)->first()->id;

                $verifVersion = app(Forge::class)->client->executeSiteCommand($serverId, $siteId, [
                    'command' => 'php artisan about --version'
                ]);

                $commandStatus = '';
                while ($commandStatus != 'finished') {
                    sleep(1);
                    $commandStatus = app(Forge::class)->client->getSiteCommand(983394, 2915773, $verifVersion->id)[0]->status;
                }
                $outputCommand = Str::replace('Laravel Framework ', '', app(Forge::class)->client->getSiteCommand(983394, 2915773, $verifVersion->id)[0]->output);

                // Installation vérifiée avec succès
                $step = $this->service->steps()
                    ->firstOrCreate(['step' => "Vérification de l'installation"], ['done' => false, 'comment' => null]);
                $step->update([
                    'done' => true,
                    'comment' => 'Installation vérifiée avec succès. Laravel version: ' . trim($outputCommand)
                ]);
                dispatch(new VerifyServiceConnection($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));

                Log::info("Installation vérifiée avec succès pour le service", [
                    'service_id' => $this->service->id,
                    'domain' => $domain,
                    'laravel_version' => trim($outputCommand)
                ]);

            } catch (\Exception $e) {
                // Gestion des erreurs - éviter les déréférencements null
                $step = $this->service->steps()->where('step', 'Vérification de l\'installation')->first();
                if ($step !== null) {
                    $step->update([
                        'done' => false,
                        'comment' => $e->getMessage()
                    ]);
                }

                $this->service->update([
                    'status' => 'error',
                ]);

                Log::error('Erreur vérification installation', [
                    'service_id' => $this->service->id,
                    'domain' => $domain,
                    'error' => $e->getMessage()
                ]);

                $adminUser = User::where('email', 'admin@'.config('batistack.domain'))->first();
                if ($adminUser !== null) {
                    Notification::make()
                        ->danger()
                        ->title("Installation d'un service en erreur !")
                        ->body($e->getMessage())
                        ->sendToDatabase($adminUser);
                }

                throw $e;
            }
        }
    }
}
