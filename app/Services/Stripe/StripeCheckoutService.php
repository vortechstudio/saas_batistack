<?php

namespace App\Services\Stripe;

class StripeCheckoutService extends StripeService
{
    public function createSession($invoice)
    {
        $currentUrl = request()->url();

        return $this->client->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $invoice->total,
                    'product_data' => [
                        'name' => 'Facture n°' . $invoice->id,
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $currentUrl . '?state=success&invoice_id=' . $invoice->id,
            'cancel_url' => $currentUrl . '?state=cancel&invoice_id=' . $invoice->id,
        ]);
    }
}
