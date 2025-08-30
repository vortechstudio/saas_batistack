<?php

namespace App\Jobs\Service\Install;

use App\Models\Customer\CustomerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InitServiceSteps implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

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
        dispatch(new InitDomain($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));
    }
}
