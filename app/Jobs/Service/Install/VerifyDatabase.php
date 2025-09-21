<?php

namespace App\Jobs\Service\Install;

use App\Models\Customer\CustomerService;
use App\Models\User;
use App\Services\AaPanel\DatabaseService;
use App\Services\AaPanel\FetchService;
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
     * Execute the job.
     */
    public function handle(): void
    {
        $database = 'db_'.Str::slug($this->service->customer->entreprise);

        if (config('app.env') == 'local') {
            $this->service->steps()->where('step', 'Vérification de la base de donnée')->first()->update([
                'done' => true,
            ]);
            dispatch(new InstallMainApps($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));
        } else {
            try {
                // Comment vérifier qu'une base de donnée existe pour le domaine
                if(count($this->fetch->databases(10, 1, $database)['message']['data']) > 0){
                    $this->service->steps()->where('step', 'Vérification de la base de donnée')->first()->update([
                        'done' => true,
                    ]);
                    dispatch(new InstallMainApps($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));
                } else {
                    Notification::make()
                        ->danger()
                        ->title("Installation d'un service en erreur !")
                        ->body("La base de donnée $database n'existe pas !")
                        ->sendToDatabase(User::where('email', 'admin@'.config('batistack.domain'))->first());

                    $this->service->steps()->where('step', 'Vérification de la base de donnée')->first()->update([
                        'done' => false,
                        'comment' => "La base de donnée $database n'existe pas !",
                    ]);

                    $this->service->update([
                        'status' => 'error',
                    ]);
                }
            } catch (\Exception $e) {
                $this->service->steps()->where('step', 'Vérification de la base de donnée')->first()->update([
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
