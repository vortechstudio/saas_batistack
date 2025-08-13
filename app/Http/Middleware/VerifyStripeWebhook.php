<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class VerifyStripeWebhook
{
    public function handle(Request $request, Closure $next)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        if (empty($endpointSecret)) {
            return response('Webhook secret not configured', 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException $e) {
             Log::warning('Invalid Stripe webhook signature', ['error' => $e->getMessage()]);
             return response('Invalid signature', 400);
        }

        $request->attributes->set('stripe_event', $event);
        return $next($request);
    }
}
