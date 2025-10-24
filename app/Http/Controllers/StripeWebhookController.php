<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $secret
            );
        } catch (SignatureVerificationException $ex) {
            Log::emergency("Stripe webhook signature verification failed: {$ex->getMessage()}");
            return response()->json(['error' => 'Stripe webhook signature verification failed'], 400);
        } catch (UnexpectedValueException $ex) {
            Log::emergency("Stripe webhook payload is invalid: {$ex->getMessage()}");
            return response()->json(['error' => 'Stripe webhook payload is invalid'], 400);
        }

        Log::driver('stripe_log_webhook')->info("Stripe webhook event: {$event->type}");
        match ($event->type) {
            "payment_intent.succeeded" => $this->handlePaymentIntentSucceeded($event),
            "payment_intent.payment_failed" => $this->handlePaymentIntentPaymentFailed($event),
        };

        return response()->json(['status' => 'ok']);
    }

    private function handlePaymentIntentSucceeded($event)
    {
        $paymentIntent = $event->data->object;
        Log::driver('stripe_log_webhook')->info("Payment Intent {$paymentIntent->id} succeeded");
    }

    private function handlePaymentIntentPaymentFailed($event)
    {
        $paymentIntent = $event->data->object;
        Log::driver('stripe_log_webhook')->info("Payment Intent {$paymentIntent->id} payment failed");
    }
}
