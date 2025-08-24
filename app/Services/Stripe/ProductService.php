<?php

namespace App\Services\Stripe;

class ProductService extends StripeService
{
    public function list()
    {
        return collect($this->client->products->all(['limit' => 50]))->sortBy('id');
    }

    public function listWithPrices()
    {
        $products = collect($this->client->products->all(['limit' => 50]))->sortBy('id');

        return $products->map(function ($product) {
            $prices = $this->client->prices->all([
                'product' => $product->id,
                'limit' => 100
            ]);

            $product->prices = collect($prices->data)->sortBy('unit_amount');

            return $product;
        });
    }

    public function listWithModules()
    {
        return $this->listWithPrices()->filter(function ($product) {
            return $product->metadata->category === 'modules';
        });
    }
}
