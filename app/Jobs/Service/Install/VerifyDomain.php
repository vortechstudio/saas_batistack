<?php

namespace App\Jobs\Service\Install;

use App\Models\Customer\CustomerService;
use App\Models\User;
use App\Services\AaPanel\FetchService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VerifyDomain implements ShouldQueue
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
        $domain = Str::slug($this->service->customer->entreprise). '.'.config('batistack.domain');

        if (config('app.env') == 'local') {
            $this->service->steps()->where('step', 'Vérification du domaine')->first()->update([
                'done' => true,
            ]);
            dispatch(new VerifyDatabase($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));
        } else {
            try {
                if(count($this->fetch->sites(1, 1, $domain)['message']['data']) > 0) {
                    $this->service->steps()->where('step', 'Vérification du domaine')->first()->update([
                        'done' => true,
                    ]);
                    dispatch(new VerifyDatabase($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));
                } else {
                    $this->service->steps()->where('step', 'Vérification du domaine')->first()->update([
                        'done' => false,
                        'comment' => 'Le domaine n\'existe pas !',
                    ]);
                    $this->service->update([
                        'status' => 'error',
                    ]);
                    Notification::make()
                        ->danger()
                        ->title("Installation d'un service en erreur !")
                        ->body("Le domaine $domain n'existe pas !")
                        ->sendToDatabase(User::where('email', 'admin@'.config('batistack.domain'))->first());
                }

            } catch (\Exception $e) {
                $this->service->steps()->where('step', 'Vérification du domaine')->first()->update([
                    'done' => false,
                    'comment' => $e->getMessage(),
                ]);

                $this->service->update([
                    'status' => 'error',
                ]);

                Notification::make()
                        ->danger()
                        ->title("Installation d'un service en erreur !")
                        ->body($e->getMessage())
                        ->sendToDatabase(User::where('email', 'admin@'.config('batistack.domain'))->first());
            }
        }
    }
}
