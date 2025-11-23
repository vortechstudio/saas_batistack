<?php

namespace App\Jobs\Service\Install;

use App\Models\Customer\CustomerService;
use App\Models\User;
use App\Services\AaPanel\DatabaseService;
use App\Services\AaPanel\FetchService;
use App\Services\Forge;
use App\Services\PanelService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VerifyDatabase implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $fetch;

    /**
     * Create a new job instance.
     */
    public function __construct(private CustomerService $service)
    {
        $this->fetch = new FetchService();
    }

    /**
     * Vérifie l'existence de la base de données du client et met à jour l'état d'installation en conséquence.
     *
     * Marque l'étape "Vérification de la base de donnée" comme réussie et planifie l'installation des applications principales si la base existe. 
     * En cas d'absence de la base ou d'erreur, notifie l'administrateur, marque l'étape comme non accomplie (avec commentaire) et positionne le service en statut `error`.
     */
    public function handle(): void
    {
        $database = 'db_'.Str::slug($this->service->customer->entreprise);

        if (config('app.env') == 'local') {
            $this->service->steps()->where('step', 'Vérification de la base de donnée')->first()?->update([
                'done' => true,
            ]);
            dispatch(new InstallMainApps($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));
        } else {
            try {
                $serverId = collect(app(\App\Services\Forge::class)->client->servers())->first()->id;
                $databaseServ = collect(app(\App\Services\Forge::class)->client->databases($serverId))->where('name', $database)->first()->name;

                if (isset($databaseServ)) {
                    $this->service->steps()->where('step', 'Vérification de la base de donnée')->first()?->update([
                        'done' => true,
                    ]);
                    dispatch(new InstallMainApps($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));
                } else {
                    Notification::make()
                        ->danger()
                        ->title("Installation d'un service en erreur !")
                        ->body("La base de donnée $database n'existe pas !")
                        ->sendToDatabase(User::where('email', 'admin@'.config('batistack.domain'))->first());

                    $this->service->steps()->where('step', 'Vérification de la base de donnée')->first()?->update([
                        'done' => false,
                        'comment' => "La base de donnée $database n'existe pas !",
                    ]);

                    $this->service->update([
                        'status' => 'error',
                    ]);
                }
            } catch (\Exception $e) {
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
}