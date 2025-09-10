<?php

namespace App\Jobs\Service\Install;

use App\Models\Customer\CustomerService;
use App\Models\User;
use App\Services\AaPanel\DatabaseService;
use App\Services\AaPanel\DomainService;
use App\Services\AaPanel\FetchService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InitDomain implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $domain;
    public $fetch;
    public $database;

    /**
     * Create a new job instance.
     */
    public function __construct(private CustomerService $service)
    {
        $this->domain = new DomainService();
        $this->fetch = new FetchService();
        $this->database = new DatabaseService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $domain = Str::slug($this->service->customer->entreprise). '.'.config('batistack.domain');
        $database = 'db_'.Str::slug($this->service->customer->entreprise);

        try {
            if(count($this->fetch->sites(10,1, $domain)['message']['data']) == 0) {
                $this->domain->add(
                    domain: $domain,
                    path: '/www/wwwroot/'.$domain,
                    runPath: '/public',
                    phpVersion: '83',
                );

                $this->database->add(
                    databaseUsername: $database,
                    databasePassword: $database,
                );

                $this->domain->checkRunPath($domain);
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
                ->sendToDatabase(User::where('email', 'admin@'.config('batistack.domain'))->first());
        }
    }
}
