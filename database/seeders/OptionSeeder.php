<?php

namespace Database\Seeders;

use App\Enums\BillingCycle;
use App\Enums\OptionType;
use App\Models\Option;
use Illuminate\Database\Seeder;
use Stripe\StripeClient;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stripe = new StripeClient(config('services.stripe.secret'));
        $products = $stripe->products->all(['limit' => 100]);
        $pp = collect($products)->filter(function ($prod) {
            return $prod->metadata->type === 'option';
        })->groupBy('metadata.option_id')->reverse()->toArray();


        foreach ($pp as $option) {
            $prices = $stripe->prices->all(['product' => $option[0]['id']]);
            Option::create([
                'id' => $option[0]['metadata']['option_id'],
                'key' => $option[0]['metadata']['option_key'],
                'name' => $option[0]['name'],
                'description' => $option[0]['description'],
                'type' => OptionType::from($option[0]['metadata']['category']),
                'price' => collect($prices)->filter(function ($price) {
                    return $price['recurring']['interval'] === 'month';
                })->first() ? collect($prices)->filter(function ($price) {
                    return $price['recurring']['interval'] === 'month';
                })->first()['unit_amount'] / 100 : collect($prices)->filter(function ($price) {
                    return $price['recurring']['interval'] === 'year';
                })->first()['unit_amount'] / 100,
                'stripe_price_id_monthly' => collect($prices)->filter(function ($price) {
                    return $price['recurring']['interval'] === 'month';
                })->first() ? collect($prices)->filter(function ($price) {
                    return $price['recurring']['interval'] === 'month';
                })->first()['id'] : null,
                'stripe_price_id_yearly' => collect($prices)->filter(function ($price) {
                    return $price['recurring']['interval'] === 'year';
                })->first()['id'],
                'billing_cycle' => BillingCycle::from(collect($prices)->filter(function ($price) {
                    return $price['recurring']['interval'] === 'month';
                })->first() ? 'monthly' : 'yearly'),
            ]);
        }
    }
}
