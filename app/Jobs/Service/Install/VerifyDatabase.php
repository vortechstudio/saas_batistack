<?php

namespace App\Jobs\Service\Install;

use App\Models\Customer\CustomerService;
use App\Services\PanelService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class VerifyDatabase implements ShouldQueue
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
        $database = 'db_'.$domain;
        try {
            // Comment vérifier qu'une base de donnée existe pour le domaine
            $this->panel->fetchDatabases(10, 1, $database);
            $this->service->steps()->where('step', 'Vérification de la base de donnée')->first()->update([
                'done' => true,
            ]);
            dispatch(new InstallMainApps($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));
        } catch (\Exception $e) {
            $this->service->steps()->where('step', 'Vérification de la base de donnée')->first()->update([
                'done' => false,
                'comment' => $e->getMessage(),
            ]);
            $this->service->update([
                'status' => 'error',
            ]);
        }
    }
}
