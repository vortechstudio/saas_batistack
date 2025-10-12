<?php

namespace App\Jobs\Service;

use App\Models\Commerce\Order;
use App\Models\Product\Feature;
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

            $product = ProductPrice::with('product')->where('stripe_price_id', $item->price->id)->first();
            $this->order->logs()->create(['libelle' => 'Création du service ' . $product->product->name]);

            if($this->order->customerService) {
                $service = $this->order->customerService;

                $this->order->logs()->create(['libelle' => 'Service ' . $product->product->name . ' mise à jours']);
            } else {
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

                $this->order->customer_service_id = $service->id;
                $this->order->save();
            }

            match ($product->product->category->value) {
                'license' => dispatch(new InitService($service, $this->order)), // Déploiement de la license,
                'module' => dispatch(new InitModule($service, $this->order)), // Activation du module pour le service
                'option' => dispatch(new InitOption($service, $this->order, $subscription)), // Ajout et activation de l'option pour le service
                'support' => '', // Mise à jour du support de service
            };
            $this->order->logs()->create(['libelle' => 'Service ' . $product->product->name . ' configuré']);
        }
    }

    private function defineSettingsOptions($product, $service)
    {
        $settings = collect();

        switch($product->product->slug) {
            case 'aggregation-bancaire':
                $settings->put('bank_account', [
                    'bank_name' => 'Banque du Nord',
                    'account_number' => '12345678901234567890123456',
                    'iban' => 'FR7630001007941234567890185',
                ]);
                break;

            case 'pack-signature':
                $settings->put('signature_pack', [
                    'validity' => $service->expirationDate,
                    'value' => 100,
                ]);
                break;

            case 'sauvegarde-et-retentions':
                $settings->put('retention_pack', [
                    'validity' => $service->expirationDate,
                    'retention_day' => 365,
                    'saving_at_day' => 2
                ]);
                break;

            case 'extension-stockages':
                $settings->put('storage_extension', [
                    'validity' => $service->expirationDate,
                    'extension_day' => 30,
                ]);
                break;
        }

        return $settings->toArray();
    }
}
