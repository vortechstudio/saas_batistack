<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
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

    private function handleCheckoutSessionCompleted($session)
    {
        $invoiceId = $session->metadata->invoice_id ?? null;

        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);
            if ($invoice) {
                $invoice->update([
                    'status' => 'paid',
                    'stripe_checkout_session_id' => $session->id,
                    'paid_at' => now()
                ]);

                // Créer l'enregistrement de paiement
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'customer_id' => $invoice->customer_id,
                    'amount' => $session->amount_total / 100, // Stripe utilise les centimes
                    'currency' => strtoupper($session->currency),
                    'method' => 'stripe',
                    'status' => 'succeeded',
                    'stripe_payment_intent_id' => $session->payment_intent,
                    'stripe_charge_id' => null, // Sera mis à jour lors du payment_intent.succeeded
                    'processed_at' => now()
                ]);

                Log::info('Invoice marked as paid', ['invoice_id' => $invoiceId]);
            }
        }
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
}
