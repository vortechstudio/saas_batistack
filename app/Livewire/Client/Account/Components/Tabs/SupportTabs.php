<?php

namespace App\Livewire\Client\Account\Components\Tabs;

use App\Models\Product\Product;
use App\Models\Product\ProductPrice;
use App\Services\Stripe\StripeService;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class SupportTabs extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public $products;

    public function mount()
    {
        $this->products = Product::with('prices')->where('category', 'support')->get();
    }

    public function subscribe(int $price_id)
    {
        $price = ProductPrice::find($price_id);
        $stripe = app(StripeService::class)->client;

        $subscription = $stripe->subscriptions->create([
            'customer' => Auth::user()->customer->stripe_customer_id,
            'items' => [['price' => $price->stripe_price_id]],
        ]);

        try {
            $subscription = $stripe->subscriptions->create([
                'customer' => Auth::user()->customer->stripe_customer_id,
                'items' => [['price' => $price->stripe_price_id]],
            ]);

            if($subscription->status === 'active') {
                Auth::user()->customer->update(['support_type' => Str::lower($price->info_stripe->nickname)]);
                Notification::make()
                    ->success()
                    ->title('Souscription effectuée avec succès')
                    ->send();
            } else {
                Notification::make()
                    ->danger()
                    ->title('Erreur lors de la création de la subscription')
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Erreur lors de la création de la subscription')
                ->send();
            return;
        }
    }

    public function render()
    {
        return view('livewire.client.account.components.tabs.support-tabs');
    }
}
