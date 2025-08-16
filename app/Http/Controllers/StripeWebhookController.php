<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\LicenseStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\License;
use App\Models\Payment;
use App\Models\Product;
use App\Notifications\PaymentFailedNotification; // Add this line
use App\Services\LicenseCreationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification; // Add this line
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook.secret');

        if (!$endpointSecret) {
            Log::error('Stripe webhook secret not configured');
            return response('Webhook secret not configured', 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            Log::error('Webhook signature verification failed: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        Log::info('Stripe webhook received', ['type' => $event->type]);

        switch ($event->type) {
            case 'checkout.session.completed':
                return $this->handleCheckoutSessionCompleted($event->data->object);

            case 'payment_intent.succeeded':
                return $this->handlePaymentIntentSucceeded($event->data->object);

            case 'payment_intent.payment_failed':
                return $this->handlePaymentIntentFailed($event->data->object);

            case 'invoice.payment_succeeded':
                return $this->handleInvoicePaymentSucceeded($event->data->object);

            case 'invoice.payment_failed':
                return $this->handleInvoicePaymentFailed($event->data->object);

            // Nouveaux événements pour les subscriptions
            case 'customer.subscription.created':
                return $this->handleSubscriptionCreated($event->data->object);

            case 'customer.subscription.updated':
                return $this->handleSubscriptionUpdated($event->data->object);

            case 'customer.subscription.deleted':
                return $this->handleSubscriptionDeleted($event->data->object);

            case 'invoice.payment_action_required':
                return $this->handleInvoicePaymentActionRequired($event->data->object);

            default:
                Log::info('Unhandled webhook type: ' . $event->type);
        }

        return $this->successMethod();
    }

    public function handleCheckoutSessionCompleted($session): Response
    {
        if ($session->mode === 'subscription') {
            return $this->handleSubscriptionCheckoutCompleted($session);
        }

        // Garder l'ancienne logique pour les paiements unitaires existants
        return $this->handlePaymentCheckoutCompleted($session);
    }

    private function handleSubscriptionCheckoutCompleted($session): Response
    {
        $customerId = $session->metadata->customer_id ?? null;
        $domain = $session->metadata->domain ?? null;
        $productId = $session->metadata->product_id ?? null;

        if (!$customerId || !$domain || !$productId) {
            Log::warning('Missing metadata in subscription checkout', [
                'session_id' => $session->id,
                'metadata' => $session->metadata ?? []
            ]);
            return $this->successMethod();
        }

        $customer = Customer::find($customerId);
        $product = Product::find($productId);

        if (!$customer || !$product) {
            Log::error('Customer or Product not found', [
                'customer_id' => $customerId,
                'product_id' => $productId
            ]);
            return $this->successMethod();
        }

        try {
            // Pour les tests, créer un objet subscription mock
            $stripeSubscription = (object) ['id' => $session->subscription];

            // En production, vous utiliseriez :
            // $stripeSubscription = $customer->subscription($session['subscription']);

            // Créer la licence associée à la subscription
            $this->createLicenseFromSubscription($customer, $product, $stripeSubscription, $session->metadata);

            Log::info('Subscription checkout completed successfully', [
                'customer_id' => $customer->id,
                'subscription_id' => $stripeSubscription->id
            ]);
        } catch (\Exception $e) {
            Log::error('Error handling subscription checkout', [
                'error' => $e->getMessage(),
                'session_id' => $session->id,  // Changé de $session['id'] à $session->id
                'customer_id' => $customerId
            ]);
        }

        return $this->successMethod();
    }

    private function handlePaymentCheckoutCompleted($session): Response
    {
        $invoiceId = $session->metadata->invoice_id ?? null;

        if (!$invoiceId) {
            Log::warning('No invoice_id in payment checkout metadata', [
                'session_id' => $session->id
            ]);
            return $this->successMethod();
        }

        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            Log::error('Invoice not found', ['invoice_id' => $invoiceId]);
            return $this->successMethod();
        }

        // Mettre à jour le statut de la facture
        $invoice->update([
            'status' => InvoiceStatus::PAID,
            'paid_at' => now(),
            'metadata' => array_merge($invoice->metadata ?? [], [
                'stripe_checkout_session_id' => $session->id,
                'stripe_payment_intent_id' => $session->payment_intent,
            ])
        ]);

        Log::info('Invoice status updated to paid', [
            'invoice_id' => $invoice->id,
            'session_id' => $session->id
        ]);

        // Créer l'enregistrement de paiement
        Payment::create([
            'customer_id' => $invoice->customer_id,
            'invoice_id' => $invoice->id,
            'amount' => $invoice->total_amount,
            'currency' => $invoice->currency,
            'method' => PaymentMethod::CARD,
            'status' => PaymentStatus::SUCCEEDED,
            'transaction_id' => $session->payment_intent,
            'stripe_payment_intent_id' => $session->payment_intent,
            'metadata' => [
                'stripe_checkout_session_id' => $session->id,
                'stripe_customer_id' => $session->customer,
            ],
        ]);

        // Créer la licence automatiquement
        $this->createLicenseFromInvoice($invoice);

        return $this->successMethod();
    }

    private function createLicenseFromSubscription($customer, $product, $stripeSubscription, $metadata): void
    {
        try {
            $licenseCreationService = app(LicenseCreationService::class);

            // Préparer les données pour la création de licence
            $licenseData = [
                'customer_id' => $customer->id,
                'product_id' => $product->id,
                'domain' => $metadata->domain,
                'billing_cycle' => $metadata->billing_cycle,
                'stripe_subscription_id' => $stripeSubscription->id,
                'max_users' => $metadata->max_users ?? null,
                'selected_modules' => json_decode($metadata->selected_modules ?? '[]', true),
                'selected_options' => json_decode($metadata->selected_options ?? '[]', true),
            ];

            $license = $licenseCreationService->createLicenseFromSubscription($licenseData);

            Log::info('Licence créée avec succès depuis subscription', [
                'license_id' => $license->id,
                'subscription_id' => $stripeSubscription->id,
                'customer_id' => $customer->id
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de licence depuis subscription', [
                'error' => $e->getMessage(),
                'subscription_id' => $stripeSubscription->id,
                'customer_id' => $customer->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function handlePaymentIntentSucceeded($paymentIntent): Response
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->update([
                'stripe_charge_id' => $paymentIntent->charges->data[0]->id ?? null,
                'status' => PaymentStatus::SUCCEEDED
            ]);

            Log::info('Payment updated with charge ID', ['payment_id' => $payment->id]);
        }

        return $this->successMethod();
    }

    private function handlePaymentIntentFailed($paymentIntent): Response
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->update([
                'status' => PaymentStatus::FAILED,
                'failure_reason' => $paymentIntent->last_payment_error->message ?? 'Payment failed'
            ]);

            // Remettre la facture en statut impayé
            $payment->invoice->update(['status' => InvoiceStatus::PENDING]);

            Log::info('Payment marked as failed', ['payment_id' => $payment->id]);
        }

        return $this->successMethod();
    }

    private function handleInvoicePaymentSucceeded($stripeInvoice): Response
    {
        // Gérer les paiements de factures Stripe (pour les subscriptions)
        Log::info('Stripe invoice payment succeeded', ['stripe_invoice_id' => $stripeInvoice->id]);

        // Ici vous pourriez mettre à jour le statut de la licence si nécessaire
        // ou gérer le renouvellement automatique

        return $this->successMethod();
    }

    private function handleInvoicePaymentFailed($stripeInvoice): Response
    {
        // Gérer les échecs de paiement de factures Stripe
        Log::error('Stripe invoice payment failed', [
            'stripe_invoice_id' => $stripeInvoice->id,
            'customer_id' => $stripeInvoice->customer
        ]);

        $invoice = Invoice::where('stripe_invoice_id', $stripeInvoice->id)->first();

        if ($invoice) {
            // Mettre à jour le statut de la facture dans notre système
            $invoice->update([
                'status' => InvoiceStatus::FAILED,
                'paid_at' => null,
                'metadata' => array_merge($invoice->metadata ?? [], [
                    'stripe_payment_intent_id' => $stripeInvoice->payment_intent,
                ])
            ]);

            // Tenter de trouver la licence associée à cette facture
            // Cela peut nécessiter une logique plus complexe si une facture peut couvrir plusieurs licences
            // ou si la relation n'est pas directe.
            // Pour l'instant, nous allons chercher une licence liée au client de la facture.
            $license = License::where('customer_id', $invoice->customer_id)
                                ->where('status', LicenseStatus::ACTIVE)
                                ->first(); // Ceci est une simplification, à adapter si nécessaire

            if ($license) {
                // Désactiver la licence en la suspendant tant que le client n'à pas payé la facture
                $license->update(['status' => LicenseStatus::SUSPENDED]);
                Log::info('License deactivated due to failed invoice payment', [
                    'license_id' => $license->id,
                    'invoice_id' => $invoice->id
                ]);
            }

            // Envoyer une notification au client
            if ($invoice->customer) {
                Notification::send($invoice->customer->user, new PaymentFailedNotification($invoice));

                Log::info('Notification sent to customer about failed payment', [
                    'customer_id' => $invoice->customer_id,
                    'invoice_id' => $invoice->id
                ]);
            }
        } else {
            Log::warning('Invoice not found in local database for failed Stripe invoice', [
                'stripe_invoice_id' => $stripeInvoice->id
            ]);
        }

        return $this->successMethod();
    }

    private function handleSubscriptionCreated($subscription): Response
    {
        Log::info('Subscription created', [
            'subscription_id' => $subscription->id,
            'customer_id' => $subscription->customer
        ]);

        return $this->successMethod();
    }

    private function handleSubscriptionUpdated($subscription): Response
    {
        Log::info('Subscription updated', [
            'subscription_id' => $subscription->id,
            'status' => $subscription->status
        ]);

        // Gérer les changements de statut de subscription
        // (active, past_due, canceled, etc.)

        return $this->successMethod();
    }

    private function handleSubscriptionDeleted($subscription): Response
    {
        Log::info('Subscription deleted', [
            'subscription_id' => $subscription->id,
            'customer_id' => $subscription->customer
        ]);

        // Désactiver la licence associée
        // $this->deactivateLicenseFromSubscription($subscription);

        return $this->successMethod();
    }

    private function handleInvoicePaymentActionRequired($stripeInvoice): Response
    {
        Log::warning('Invoice payment action required', [
            'stripe_invoice_id' => $stripeInvoice->id,
            'customer_id' => $stripeInvoice->customer
        ]);

        // Envoyer une notification au client pour mettre à jour sa méthode de paiement

        return $this->successMethod();
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
