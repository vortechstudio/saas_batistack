<?php

namespace App\Services\Stripe;

use App\Models\Customer\Customer;
use Illuminate\Support\Str;

class CustomerService extends StripeService
{
    public function create(Customer $customer)
    {
        try {
            $stripeCustomer = $this->client->customers->create([
                'name' => $customer->entreprise,
                'email' => $customer->user->email,
                'phone' => $customer->tel ? $customer->tel : $customer->portable,
                'metadata' => [
                    'customer_id' => $customer->id,
                ],
                'address' => [
                    'line1' => $customer->adresse,
                    'postal_code' => $customer->code_postal,
                    'city' => $customer->ville,
                    'country' => Str::upper(Str::limit($customer->pays, 2, '')),
                ]
            ]);

            $customer->stripe_customer_id = $stripeCustomer->id;
            $customer->save();
        }catch(\Throwable $e) {
            report($e);
            throw $e;
        }
    }

    public function listPaymentMethods(Customer $customer)
    {
        try {
            return collect($this->client->customers->allPaymentMethods($customer->stripe_customer_id));
        }catch(\Throwable $e) {
            report($e);
            throw $e;
        }
    }
}
