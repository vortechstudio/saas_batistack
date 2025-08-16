<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\License;
use App\Notifications\PaymentSuccessNotification;
use App\Services\LicenseCreationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSuccessfulPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Invoice $invoice;
    public string $stripeSessionId;

    /**
     * Create a new job instance.
     */
    public function __construct(Invoice $invoice, string $stripeSessionId)
    {
        $this->invoice = $invoice;
        $this->stripeSessionId = $stripeSessionId;
    }

    /**
     * Execute the job.
     */
    public function handle(LicenseCreationService $licenseService): void
    {
        try {
            Log::info('Processing successful payment', [
                'invoice_id' => $this->invoice->id,
                'stripe_session_id' => $this->stripeSessionId,
            ]);

            // Créer la licence si elle n'existe pas déjà
            $license = License::where('invoice_id', $this->invoice->id)->first();

            if (!$license) {
                $license = $licenseService->createLicenseFromInvoice($this->invoice);
                Log::info('License created', ['license_id' => $license->id]);
            }

            // Envoyer la notification de succès
            $this->invoice->customer->notify(new PaymentSuccessNotification($this->invoice, $license));

            Log::info('Payment success notification sent', [
                'customer_id' => $this->invoice->customer->id,
                'invoice_id' => $this->invoice->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process successful payment', [
                'invoice_id' => $this->invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-lancer l'exception pour que le job soit marqué comme échoué
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessSuccessfulPaymentJob failed', [
            'invoice_id' => $this->invoice->id,
            'stripe_session_id' => $this->stripeSessionId,
            'error' => $exception->getMessage(),
        ]);
    }
}
