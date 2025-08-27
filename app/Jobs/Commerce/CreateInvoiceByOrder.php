<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateInvoiceByOrder implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Order $order)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Créer la facture sur stripe et envoyer une notification mail et database au client.

    }
}
