<?php

namespace App\Jobs\Service;

use App\Enum\Commerce\OrderStatusEnum;
use App\Enum\Customer\CustomerServiceStatusEnum;
use App\Models\Commerce\Order;
use App\Models\Customer\CustomerService;
use App\Models\Product\ProductPrice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class InitOption implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private CustomerService $service,
        private Order $order,
        private $subscription
    )
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $this->passOrderToDelivered();
        $this->passServiceToPending($this->service);
        $this->initStep($this->service);

        foreach ($this->subscription->items->data as $item) {
            $product = ProductPrice::with('product')->where('stripe_price_id', $item->price->id)->first();

            try {
                $options = $this->defineSettingsOptions($product, $this->service);
                $this->service->options()->create([
                    'customer_service_id' => $this->service->id,
                    'product_id' => $product->product_id,
                    'settings' => $options,
                ]);

                if($product->product->slug == 'extension-stockages') {
                    $this->service->update([
                        'storage_limit' => $options['storage_extension']['storage_limit'],
                    ]);
                }

                $this->service->steps()->where('step', "Initialisation de l'options")->first()?->update([
                    'done' => true,
                ]);

                $this->passServiceToOk($this->service);
            } catch (\Throwable $th) {
                 $this->service->update([
                'status' => 'error',
            ]);
                $this->service->steps()->where('step', "Initialisation de l'options")->first()?->update([
                    'done' => false,
                    'comment' => $th->getMessage(),
                ]);
            }
        }
    }

    private function initStep(CustomerService $service)
    {
        $service->steps()->create([
            'type' => 'options',
            'step' => 'Initialisation de l\'options',
            'customer_service_id' => $service->id,
        ]);
    }

    private function passOrderToDelivered()
    {
        $this->order->update([
            'status' => OrderStatusEnum::DELIVERED,
            'delivered_at' => now(),
        ]);
    }

    private function passServiceToPending(CustomerService $service)
    {
        $service->update([
            'status' => CustomerServiceStatusEnum::PENDING,
        ]);
    }

    private function passServiceToOk(CustomerService $service)
    {
        $service->update([
            'status' => CustomerServiceStatusEnum::OK,
        ]);
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
                    'storage_limit' => 25,
                ]);                
                break;
        }

        return $settings->toArray();
    }
}
