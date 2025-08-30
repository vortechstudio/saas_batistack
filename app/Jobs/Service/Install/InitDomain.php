<?php

namespace App\Jobs\Service\Install;

use App\Models\Customer\CustomerService;
use App\Services\PanelService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InitDomain implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $panel;

    /**
     * Create a new job instance.
     */
    public function __construct(private CustomerService $service)
    {
        $this->panel = new PanelService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $domain = Str::slug($this->service->customer->entreprise). '.'.config('batistack.domain');

        try {
            if(count($this->panel->fetchSites(10,1, $domain)['message']['data']) == 0) {
                $this->panel->addSite(
                    domain: $domain,
                    path: '/www/wwwroot/'.$domain,
                    runPath: '/public',
                    phpVersion: '83',
                    sql: true,
                    databaseUsername: 'db_'.Str::slug($this->service->customer->entreprise),
                    databasePassword: 'db_'.Str::slug($this->service->customer->entreprise),
                );

                $this->panel->checkRunPath($domain);
                $this->panel->uploadCert(
                    domain: $domain,
                    key: file_get_contents(storage_path('ssl/certificat.key')),
                    cert: file_get_contents(storage_path('ssl/certificat.crt')),
                );

                if(config('app.env') == 'local') {
                    $this->panel->uploadCert($domain, storage_path('ssl/certificat.key'), storage_path('ssl/certificat.crt'));
                }
            }

            $this->service->steps()->where('step', 'Création de domaine')->first()->update([
                'done' => true,
            ]);
            dispatch(new VerifyDomain($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));
        } catch (\Exception $e) {
            $this->service->update([
                'status' => 'error',
            ]);
            $this->service->steps()->where('step', 'Création de domaine')->first()->update([
                'done' => false,
                'comment' => $e->getMessage(),
            ]);
            Notification::make()
                ->danger()
                ->title("Installation d'un service en erreur !")
                ->body($e->getMessage())
                ->sendToDatabase(Auth::user()->where('email', 'admin@'.config('batistack.domain'))->first());
        }
    }
}
