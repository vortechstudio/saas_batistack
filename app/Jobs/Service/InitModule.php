<?php

namespace App\Jobs\Service;

use App\Enum\Commerce\OrderStatusEnum;
use App\Enum\Customer\CustomerServiceStatusEnum;
use App\Models\Commerce\Order;
use App\Models\Customer\CustomerService;
use App\Models\Product\Feature;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class InitModule implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private CustomerService $service,
        private Order $order
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
        // Installation du module

        try {
            $this->service->modules()->create([
                'customer_service_id' => $this->service->id,
                'feature_id' => Feature::where('slug', $this->order->items->first()->product->slug)->first()->id,
            ]);

            $this->service->steps()->where('step', 'Initialisation du module')->first()?->update([
                'done' => true,
            ]);
        } catch (\Throwable $th) {
            $this->service->update([
                'status' => 'error',
            ]);
            $this->service->steps()->where('step', 'Initialisation du module')->first()?->update([
                'done' => false,
                'comment' => $th->getMessage(),
            ]);
        }

        // Activation du module

        try {
            $this->service->modules()->where('feature_id', Feature::where('slug', $this->order->items->first()->product->slug)->first()->id)->first()?->update([
                'is_active' => true,
            ]);

            $this->service->steps()->where('step', 'Activation du module')->first()?->update([
                'done' => true,
            ]);
            $this->passServiceToOk($this->service);
        } catch (\Throwable $th) {
            $this->service->update([
                'status' => 'error',
            ]);
            $this->service->steps()->where('step', 'Activation du module')->first()?->update([
                'done' => false,
                'comment' => $th->getMessage(),
            ]);
        }

    }

    private function initStep(CustomerService $service)
    {
        $service->steps()->create([
            'type' => 'modules',
            'step' => 'Initialisation du module',
            'customer_service_id' => $service->id,
        ]);

        $service->steps()->create([
            'type' => 'modules',
            'step' => 'Activation du module',
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
}
