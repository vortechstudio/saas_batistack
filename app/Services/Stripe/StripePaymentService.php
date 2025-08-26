<?php

namespace App\Services\Stripe;

class StripePaymentService extends StripeService
{
    public function getPaymentIntent($id)
    {
        return $this->client->paymentIntents->retrieve($id);
    }

    public function confirmPaymentIntent($id)
    {
        return $this->client->paymentIntents->confirm($id);
    }
}
