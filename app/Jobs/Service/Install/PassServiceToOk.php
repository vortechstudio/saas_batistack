<?php

namespace App\Jobs\Service\Install;

use App\Models\Customer\CustomerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class PassServiceToOk implements ShouldQueue
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
        $this->service->update([
            'status' => 'ok',
        ]);
    }
}
