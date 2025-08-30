<?php

namespace App\Jobs\Service;

use App\Models\Commerce\Order;
use App\Models\Product\ProductPrice;
use App\Services\Stripe\StripeService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateService implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private Order $order, private string $subscription_id)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Création du service en fonction de la commande
        $subscription = app(StripeService::class)->client->subscriptions->retrieve($this->subscription_id);

        foreach ($subscription->items->data as $item) {
            // Création du service

            $product = ProductPrice::where('stripe_price_id', $item->price->id)->first();
            $this->order->logs()->create(['libelle' => 'Création du service ' . $product->product->name]);
            $service = $this->order->customer->services()->create([
                'creationDate' => Carbon::createFromTimestamp($item->created),
                'expirationDate' => Carbon::createFromTimestamp($item->current_period_end),
                'nextBillingDate' => Carbon::createFromTimestamp($item->current_period_end)->addDay(),
                'status' => "pending",
                'stripe_subscription_id' => $subscription->id,
                'customer_id' => $this->order->customer->id,
                'product_id' => $product->product_id,
            ]);
            $this->order->logs()->create(['libelle' => 'Service ' . $product->product->name . ' créé']);

            match ($product->product->category->value) {
                'license' => dispatch(new InitService($service, $this->order)), // Déploiement de la license,
                'modules' => '', // Activation du module pour le service
                'options' => '', // Ajout et activation de l'option pour le service
                'support' => '', // Mise à jour du support de service
            };
            $this->order->logs()->create(['libelle' => 'Service ' . $product->product->name . ' configuré']);
        }
    }
}
