<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\LicenseCreationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            Log::error('Webhook signature verification failed: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        Log::info('Stripe webhook received', ['type' => $event->type]);

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;

            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;

            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event->data->object);
                break;

            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($event->data->object);
                break;

            default:
                Log::info('Unhandled webhook type: ' . $event->type);
        }

        return response('Webhook handled', 200);
    }

    public function handleCheckoutSessionCompleted(array $payload): Response
    {
        $session = $payload['data']['object'];
        $invoiceId = $session['metadata']['invoice_id'] ?? null;

        if (!$invoiceId) {
            return $this->successMethod();
        }

        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            return $this->successMethod();
        }

        // Mettre à jour le statut de la facture
        $invoice->update([
            'status' => InvoiceStatus::PAID,
            'paid_at' => now(),
            'metadata' => array_merge($invoice->metadata ?? [], [
                'stripe_checkout_session_id' => $session['id'],
                'stripe_payment_intent_id' => $session['payment_intent'],
            ])
        ]);

        // Créer l'enregistrement de paiement
        Payment::create([
            'customer_id' => $invoice->customer_id,
            'invoice_id' => $invoice->id,
            'amount' => $invoice->total_amount, // Corriger le nom du champ
            'currency' => $invoice->currency,
            'method' => PaymentMethod::CARD,
            'status' => PaymentStatus::SUCCEEDED,
            'transaction_id' => $session['payment_intent'],
            'stripe_payment_intent_id' => $session['payment_intent'],
            'metadata' => [
                'stripe_checkout_session_id' => $session['id'],
                'stripe_customer_id' => $session['customer'],
            ],
        ]);

        // Créer la licence automatiquement
        $this->createLicenseFromInvoice($invoice);

        return $this->successMethod();
    }

    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->update([
                'stripe_charge_id' => $paymentIntent->charges->data[0]->id ?? null,
                'status' => 'succeeded'
            ]);

            Log::info('Payment updated with charge ID', ['payment_id' => $payment->id]);
        }
    }

    private function handlePaymentIntentFailed($paymentIntent)
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->update([
                'status' => 'failed',
                'failure_reason' => $paymentIntent->last_payment_error->message ?? 'Payment failed'
            ]);

            // Remettre la facture en statut impayé
            $payment->invoice->update(['status' => 'pending']);

            Log::info('Payment marked as failed', ['payment_id' => $payment->id]);
        }
    }

    private function handleInvoicePaymentSucceeded($stripeInvoice)
    {
        // Gérer les paiements de factures Stripe (si vous utilisez Stripe Billing)
        Log::info('Stripe invoice payment succeeded', ['stripe_invoice_id' => $stripeInvoice->id]);
    }

    private function handleInvoicePaymentFailed($stripeInvoice)
    {
        // Gérer les échecs de paiement de factures Stripe
        Log::info('Stripe invoice payment failed', ['stripe_invoice_id' => $stripeInvoice->id]);
    }

    /**
     * Retourne une réponse de succès pour les webhooks
     */
    private function successMethod(): Response
    {
        return response('Webhook handled successfully', 200);
    }

    /**
     * Crée une licence à partir d'une facture payée
     */
    private function createLicenseFromInvoice(Invoice $invoice): void
    {
        try {
            $licenseService = new LicenseCreationService();
            $license = $licenseService->createLicenseFromInvoice($invoice);

            Log::info('License created successfully', [
                'license_id' => $license->id,
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create license from invoice', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Optionnel : vous pourriez vouloir notifier l'équipe support
            // ou marquer la facture avec un flag pour traitement manuel
        }
    }
}
