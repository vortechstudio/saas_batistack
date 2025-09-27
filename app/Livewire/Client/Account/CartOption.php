<?php

namespace App\Livewire\Client\Account;

use App\Models\Customer\CustomerService;
use App\Models\Product\Product;
use App\Models\Commerce\Order;
use App\Models\Commerce\OrderItem;
use App\Enum\Product\ProductCategoryEnum;
use App\Enum\Commerce\OrderStatusEnum;
use App\Enum\Commerce\OrderTypeEnum;
use App\Jobs\Commerce\CreateInvoiceByOrder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Options - Panier')]
class CartOption extends Component
{
    public bool $hasServices;
    public $selectedService = null;
    public $availableOptions = [];
    public $cart = [];
    public $cartTotal = 0;

    public function mount()
    {
        $this->hasServices = Auth::user()->customer->services->count() > 0;
        if ($this->hasServices) {
            $this->selectedService = Auth::user()->customer->services->first()->id;
            $this->loadAvailableOptions();
        }
    }

    public function selectService($serviceId)
    {
        $this->selectedService = $serviceId;
        $this->cart = []; // Vider le panier lors du changement de service
        $this->loadAvailableOptions();
        $this->calculateTotal();
    }

    public function loadAvailableOptions()
    {
        if (!$this->selectedService) {
            $this->availableOptions = [];
            return;
        }

        $service = CustomerService::with('options.product')->find($this->selectedService);

        if (!$service) {
            $this->availableOptions = [];
            return;
        }

        // Récupérer toutes les options disponibles (produits de catégorie OPTION)
        $allOptions = Product::with('prices')
            ->where('category', ProductCategoryEnum::OPTION)
            ->where('active', true)
            ->get();

        // Récupérer les options déjà associées au service
        $serviceOptions = $service->options->pluck('product_id')->toArray();

        // Filtrer pour obtenir seulement les options non encore associées
        $availableOptions = $allOptions->whereNotIn('id', $serviceOptions);

        $this->availableOptions = $availableOptions->map(function ($option) {
            $price = $option->prices->first();

            return [
                'id' => $option->id,
                'name' => $option->name,
                'description' => $option->description ?? 'Option ' . $option->name,
                'slug' => $option->slug,
                'media' => $option->media ?? '/images/default-option.png',
                'price' => $price?->price ?? 0,
                'price_formatted' => $price ? number_format($price->price, 2) . ' €' : 'Prix non disponible',
                'stripe_price_id' => $price?->stripe_price_id
            ];
        })->toArray();
    }

    public function addToCart($optionId)
    {
        $option = collect($this->availableOptions)->firstWhere('id', $optionId);

        if ($option && !isset($this->cart[$optionId])) {
            $this->cart[$optionId] = $option;
            $this->calculateTotal();

            Notification::make()
                ->success()
                ->title('Option ajoutée')
                ->body($option['name'] . ' a été ajoutée au panier')
                ->send();
        }
    }

    public function removeFromCart($optionId)
    {
        if (isset($this->cart[$optionId])) {
            $optionName = $this->cart[$optionId]['name'];
            unset($this->cart[$optionId]);
            $this->calculateTotal();

            Notification::make()
                ->success()
                ->title('Option retirée')
                ->body($optionName . ' a été retirée du panier')
                ->send();
        }
    }

    public function calculateTotal()
    {
        $this->cartTotal = collect($this->cart)->sum('price');
    }

    public function getTaxAmount()
    {
        return $this->cartTotal * 0.20; // TVA 20%
    }

    public function getTotalWithTax()
    {
        return $this->cartTotal + $this->getTaxAmount();
    }

    public function subscribeOptions()
    {
        if (empty($this->cart)) {
            Notification::make()
                ->warning()
                ->title('Panier vide')
                ->body('Veuillez sélectionner au moins une option')
                ->send();
            return;
        }

        try {
            DB::transaction(function () {
                // Créer la commande
                $order = Order::create([
                    'customer_id' => Auth::user()->customer->id,
                    'customer_service_id' => $this->selectedService,
                    'order_number' => 'OPT-' . strtoupper(uniqid()),
                    'status' => OrderStatusEnum::PENDING,
                    'type' => OrderTypeEnum::PURCHASE,
                    'subtotal' => $this->cartTotal,
                    'tax_amount' => $this->getTaxAmount(),
                    'total_amount' => $this->getTotalWithTax(),
                ]);

                // Créer les éléments de commande
                foreach ($this->cart as $option) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $option['id'],
                        'quantity' => 1,
                        'unit_price' => $option['price'],
                        'total_price' => $option['price'],
                    ]);
                }

                $order->logs()->create([
                    'libelle' => 'Création de votre commande d\'options',
                ]);

                // Déclencher la création de la facture
                dispatch(new CreateInvoiceByOrder($order));

                // Vider le panier
                $this->cart = [];
                $this->calculateTotal();
                $this->loadAvailableOptions();

                Notification::make()
                    ->success()
                    ->title('Commande créée')
                    ->body('Votre commande d\'options a été créée avec succès')
                    ->send();

                return $this->redirect(route('client.account.order.show', $order->id));
            });
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Erreur')
                ->body('Une erreur est survenue lors de la création de la commande')
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.client.account.cart-option');
    }
}
