<?php

namespace App\Jobs\Service;

use App\Enum\Commerce\OrderStatusEnum;
use App\Jobs\Service\Install\ActivateLicenseModule;
use App\Jobs\Service\Install\InitDomain;
use App\Jobs\Service\Install\InitServiceSteps;
use App\Jobs\Service\Install\InstallMainApps;
use App\Jobs\Service\Install\NotifyClientByMail;
use App\Jobs\Service\Install\PassServiceToOk;
use App\Jobs\Service\Install\VerifyDatabase;
use App\Jobs\Service\Install\VerifyDomain;
use App\Jobs\Service\Install\VerifyInstallation;
use App\Jobs\Service\Install\VerifyLicenseInformation;
use App\Jobs\Service\Install\VerifyServiceConnection;
use App\Models\Commerce\Order;
use App\Models\Customer\CustomerService;
use App\Services\NebuloService;
use App\Services\PanelService;
use Filament\Actions\Action;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Process;
use Throwable;

class InitService implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;
    private $panel;

    /**
     * Create a new job instance.
     */
    public function __construct(private CustomerService $service, private Order $order)
    {
        $this->panel = new PanelService();
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


