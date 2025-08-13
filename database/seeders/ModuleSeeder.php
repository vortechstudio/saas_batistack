<?php

namespace Database\Seeders;

use App\Enums\ModuleCategory;
use App\Models\Module;
use Illuminate\Database\Seeder;
use Stripe\StripeClient;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stripe = new StripeClient(config('services.stripe.secret'));
        $products = $stripe->products->all(['limit' => 100]);
        $pp = collect($products)->filter(function ($prod) {
            return $prod->metadata->type === 'module';
        })->groupBy('metadata.module_id')->reverse()->toArray();


        foreach ($pp as $module) {
            $prices = $stripe->prices->all(['product' => $module[0]['id']]);
            Module::create([
                'id' => $module[0]['metadata']['module_id'],
                'key' => $module[0]['metadata']['module_key'],
                'name' => $module[0]['name'],
                'description' => $module[0]['description'],
                'category' => $module[0]['metadata']['category'],
                'base_price' => collect($prices)->filter(function ($price) {
                    return $price['recurring']['interval'] === 'month';
                })->first()['unit_amount'] / 100,
                'stripe_price_id_monthly' => collect($prices)->filter(function ($price) {
                    return $price['recurring']['interval'] === 'month';
                })->first()['id'],
                'stripe_price_id_yearly' => collect($prices)->filter(function ($price) {
                    return $price['recurring']['interval'] === 'year';
                })->first()['id'],
                'sort_order' => $module[0]['metadata']['module_id'],
            ]);
        }
    }
}
