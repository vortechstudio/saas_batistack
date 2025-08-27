<?php

namespace App\Services\Stripe;

use Stripe\StripeClient;

class StripeService
{
    public $client;

    public function __construct()
    {
        $this->client = new StripeClient(config('services.stripe.secret'));
    }
}
