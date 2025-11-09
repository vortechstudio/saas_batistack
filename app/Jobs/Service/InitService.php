<?php

namespace App\Jobs\Service;

use App\Enum\Commerce\OrderStatusEnum;
use App\Jobs\Service\Install\InitServiceSteps;
use App\Models\Commerce\Order;
use App\Models\Customer\CustomerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class InitService implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private CustomerService $service, private Order $order)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /**
         * Passage de la commande à livré
         * Initialisation des étapes d'installation du service
         * Création de domaine
         * Vérification du domaine
         * Vérification de la base de donnée
         * Ouverture des droits de stockages pour le service
         * Installation de l'application principal
         * Vérification de l'installation
         * Vérification de la connexion au service (SAAS)
         * Vérification des informations relatives à la license
         * Activation des modules de la license
         * Notification au client par email
         * Passage du service à OK
         */
        $this->passOrderToDelivered();
        dispatch(new InitServiceSteps($this->service))->onQueue('installApp');
    }

    private function passOrderToDelivered()
    {
        $this->order->update([
            'status' => OrderStatusEnum::DELIVERED,
            'delivered_at' => now(),
        ]);
    }

}


