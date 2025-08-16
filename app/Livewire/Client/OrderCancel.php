<?php

namespace App\Livewire\Client;

use App\Models\Invoice;
use App\Models\Payment;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Notifications\PaymentFailedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Paiement annulé')]
class OrderCancel extends Component
{
    public ?Invoice $invoice = null;
    public ?array $orderDetails = null;
    public string $cancelReason = 'cancelled';
    public bool $canRetry = true;
    public string $message = '';

    public function mount($invoice)
    {
        try {
            // Récupérer la facture
            $this->invoice = Invoice::with(['customer', 'invoiceItems.product', 'payments'])
                ->where('id', $invoice)
                ->where('customer_id', Auth::user()->customer->id)
                ->firstOrFail();

            if ($this->invoice && $this->invoice->status !== 'paid') {
                // Envoyer la notification d'échec si pas déjà envoyée
                if (!$this->invoice->customer->notifications()->where('type', PaymentFailedNotification::class)->where('data->invoice_id', $this->invoice->id)->exists()) {
                    $this->invoice->customer->notify(new PaymentFailedNotification($this->invoice));
                }
            }

            // Analyser la raison de l'annulation
            $this->analyzeCancelReason();

            // Préparer les détails de la commande
            $this->prepareOrderDetails();

            // Mettre à jour le statut si nécessaire
            $this->updateInvoiceStatus();

            Log::info('OrderCancel page accessed', [
                'invoice_id' => $this->invoice->id,
                'customer_id' => $this->invoice->customer_id,
                'cancel_reason' => $this->cancelReason
            ]);

        } catch (\Exception $e) {
            Log::error('Error in OrderCancel mount', [
                'invoice_id' => $invoice,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            $this->message = 'Une erreur est survenue lors du chargement de votre commande.';
        }
    }

    private function analyzeCancelReason()
    {
        // Vérifier s'il y a des paiements échoués
        $failedPayment = $this->invoice->payments()
            ->where('status', PaymentStatus::FAILED)
            ->latest()
            ->first();

        if ($failedPayment) {
            $this->cancelReason = 'failed';
            $this->message = 'Votre paiement a échoué : ' . ($failedPayment->failure_reason ?? 'Erreur de paiement');
        } else {
            $this->cancelReason = 'cancelled';
            $this->message = 'Vous avez annulé votre paiement. Vous pouvez réessayer à tout moment.';
        }

        // Vérifier si on peut réessayer
        $this->canRetry = $this->invoice->status !== InvoiceStatus::PAID;
    }

    private function prepareOrderDetails()
    {
        $metadata = $this->invoice->metadata ?? [];

        $this->orderDetails = [
            'product' => $metadata['product_name'] ?? 'Produit',
            'domain' => $metadata['domain'] ?? 'Non spécifié',
            'billing_cycle' => $metadata['billing_cycle'] ?? 'monthly',
            'modules' => $metadata['modules'] ?? [],
            'options' => $metadata['options'] ?? [],
            'total_amount' => $this->invoice->total_amount,
            'currency' => $this->invoice->currency
        ];
    }

    private function updateInvoiceStatus()
    {
        if ($this->cancelReason === 'failed' && $this->invoice->status !== InvoiceStatus::FAILED) {
            $this->invoice->update(['status' => InvoiceStatus::FAILED]);
        }
    }

    public function retryPayment()
    {
        if (!$this->canRetry) {
            session()->flash('error', 'Impossible de réessayer le paiement pour cette commande.');
            return;
        }

        try {
            // Rediriger vers la page de paiement des factures
            return redirect()->route('client.invoices')
                ->with('retry_invoice', $this->invoice->id)
                ->with('message', 'Vous pouvez maintenant réessayer le paiement de votre commande.');

        } catch (\Exception $e) {
            Log::error('Error retrying payment', [
                'invoice_id' => $this->invoice->id,
                'error' => $e->getMessage()
            ]);

            session()->flash('error', 'Une erreur est survenue lors de la tentative de paiement.');
        }
    }

    public function createNewOrder()
    {
        // Rediriger vers le formulaire de commande avec les données pré-remplies
        return redirect()->route('client.order')
            ->with('prefill_data', $this->orderDetails)
            ->with('message', 'Vous pouvez créer une nouvelle commande avec les mêmes paramètres.');
    }

    public function goToDashboard()
    {
        return redirect()->route('client.dashboard');
    }

    public function contactSupport()
    {
        return redirect()->route('client.support')
            ->with('subject', 'Problème de paiement - Facture #' . $this->invoice->invoice_number)
            ->with('message', 'J\'ai rencontré un problème avec le paiement de ma commande.');
    }

    public function render()
    {
        return view('livewire.client.order-cancel');
    }
}
