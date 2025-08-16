<?php

namespace App\Livewire\Client;

use App\Models\Invoice;
use App\Models\License;
use App\Services\LicenseCreationService;
use App\Enums\InvoiceStatus;
use App\Jobs\ProcessSuccessfulPaymentJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

#[Layout('components.layouts.app')]
#[Title('Commande réussie')]
class OrderSuccess extends Component
{
    public ?Invoice $invoice = null;
    public ?License $license = null;
    public ?array $orderDetails = null;
    public ?string $sessionId = null;
    public bool $licenseCreated = false;
    public string $message = '';
    public string $messageType = 'success';

    public function mount($invoice)
    {
        try {
            // Récupérer la facture
            $this->invoice = Invoice::with(['customer', 'invoiceItems.product', 'payments'])
                ->where('id', $invoice->id)
                ->where('customer_id', Auth::user()->customer->id)
                ->firstOrFail();


            // Récupérer l'ID de session Stripe depuis l'URL
            $this->sessionId = request()->get('session_id');

            // Vérifier le statut de la session Stripe si disponible
            if ($this->sessionId) {
                $this->verifyStripeSession();
            }

            if ($this->invoice && $this->invoice->status === 'paid') {
                // Dispatcher le job pour traiter le paiement réussi
                ProcessSuccessfulPaymentJob::dispatch($this->invoice, $this->sessionId);
            }
            // Préparer les détails de la commande
            $this->prepareOrderDetails();

            // Vérifier si une licence existe déjà
            $this->license = License::where('customer_id', $this->invoice->customer_id)
                ->first();

            if ($this->license) {
                $this->licenseCreated = true;
                $this->message = 'Votre licence a été créée avec succès !';
            } else {
                // Créer la licence si le paiement est confirmé
                $this->createLicenseIfPaid();
            }

            Log::info('OrderSuccess page accessed', [
                'invoice_id' => $this->invoice->id,
                'customer_id' => $this->invoice->customer_id,
                'session_id' => $this->sessionId,
                'license_created' => $this->licenseCreated
            ]);

        } catch (\Exception $e) {
            Log::error('Error in OrderSuccess mount', [
                'invoice_id' => $invoice,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            $this->message = 'Une erreur est survenue lors du chargement de votre commande.';
            $this->messageType = 'error';
        }
    }

    private function verifyStripeSession()
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = StripeSession::retrieve($this->sessionId);

            if ($session->payment_status === 'paid') {
                // Mettre à jour le statut de la facture si nécessaire
                if ($this->invoice->status !== InvoiceStatus::PAID) {
                    $this->invoice->update([
                        'status' => InvoiceStatus::PAID,
                        'paid_at' => now()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not verify Stripe session', [
                'session_id' => $this->sessionId,
                'error' => $e->getMessage()
            ]);
        }
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

    private function createLicenseIfPaid()
    {
        if ($this->invoice->status === InvoiceStatus::PAID) {
            try {
                $licenseService = new LicenseCreationService();
                $this->license = $licenseService->createLicenseFromInvoice($this->invoice);
                $this->licenseCreated = true;
                $this->message = 'Votre licence a été créée avec succès !';

                Log::info('License created from OrderSuccess', [
                    'license_id' => $this->license->id,
                    'invoice_id' => $this->invoice->id
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to create license from OrderSuccess', [
                    'invoice_id' => $this->invoice->id,
                    'error' => $e->getMessage()
                ]);

                $this->message = 'Votre paiement a été traité, mais la création de licence est en cours. Vous recevrez un email de confirmation.';
                $this->messageType = 'warning';
            }
        } else {
            $this->message = 'Votre paiement est en cours de traitement. Vous recevrez une confirmation par email.';
            $this->messageType = 'info';
        }
    }

    public function downloadLicense()
    {
        if (!$this->license) {
            session()->flash('error', 'Aucune licence disponible pour téléchargement.');
            return;
        }

        // Logique de téléchargement de licence
        // Vous pouvez implémenter la génération d'un fichier de licence ici

        session()->flash('success', 'Licence téléchargée avec succès.');
    }

    public function goToDashboard()
    {
        return redirect()->route('client.dashboard');
    }

    public function goToLicenses()
    {
        return redirect()->route('client.licenses');
    }

    public function render()
    {
        return view('livewire.client.order-success');
    }
}
