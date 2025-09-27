<?php

namespace App\Livewire\Client\Account;

use App\Enum\Commerce\OrderTypeEnum;
use App\Jobs\Commerce\CreateInvoiceByOrder;
use App\Models\Commerce\Order;
use App\Models\Customer\CustomerService;
use App\Models\Product\Feature;
use App\Models\Product\Product;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Modules - Panier')]
class CartModule extends Component
{
    public bool $hasServices;
    public $selectedService = null;
    public $availableFeatures = [];
    public $cart = [];
    public $cartTotal = 0;

    public function mount()
    {
        $this->hasServices = Auth::user()->customer->services->count() > 0;
        if ($this->hasServices) {
            $this->selectedService = Auth::user()->customer->services->first()->id;
            $this->loadAvailableFeatures();
        }
    }

    public function selectService($serviceId)
    {
        $this->selectedService = $serviceId;
        $this->cart = []; // Vider le panier lors du changement de service
        $this->loadAvailableFeatures();
        $this->calculateTotal();
    }

    public function loadAvailableFeatures()
    {
        if (!$this->selectedService) {
            $this->availableFeatures = [];
            return;
        }

        $service = CustomerService::with('product.features')->find($this->selectedService);

        if (!$service || !$service->product) {
            $this->availableFeatures = [];
            return;
        }

        // Récupérer toutes les features disponibles avec leurs produits associés
        $allFeatures = Feature::with(['products' => function($query) {
            $query->with('prices');
        }])->get();

        // Récupérer les features du produit (celles qui sont déjà disponibles)
        $productFeatures = $service->product->features;

        // Filtrer pour obtenir seulement les features non disponibles
        $unavailableFeatures = $allFeatures->whereNotIn('id', $productFeatures->pluck('id'));

        $this->availableFeatures = $unavailableFeatures->map(function ($feature) {
            $product = Product::where('slug', $feature->slug)->with('prices')->first();
            $price = $product ? $product->prices->first() : null;

            return [
                'id' => $feature->id,
                'name' => $feature->name,
                'description' => $feature->description ?? 'Module ' . $feature->name,
                'slug' => $feature->slug,
                'media' => $feature->media,
                'product_id' => $product?->id,
                'price' => $price?->price ?? 0,
                'price_formatted' => $price ? number_format($price->price, 2) . ' €' : 'Prix non disponible'
            ];
        })->toArray();
    }

    public function addToCart($featureId)
    {
        $feature = collect($this->availableFeatures)->firstWhere('id', $featureId);

        if (!$feature || isset($this->cart[$featureId])) {
            return;
        }

        $this->cart[$featureId] = $feature;
        $this->calculateTotal();
    }

    public function removeFromCart($featureId)
    {
        if (isset($this->cart[$featureId])) {
            $featureName = $this->cart[$featureId]['name'];
            unset($this->cart[$featureId]);
            $this->calculateTotal();
        }
    }

    public function calculateTotal()
    {
        $this->cartTotal = collect($this->cart)->sum('price');
    }

    public function checkout()
    {
        if (empty($this->cart)) {
            Notification::make()
                ->warning()
                ->title('Panier vide')
                ->body('Veuillez ajouter au moins un module à votre panier.')
                ->send();
            return;
        }

        $service = CustomerService::with('product', 'product.prices')->find($this->selectedService);

        if (!$service) {
            Notification::make()
                ->danger()
                ->title('Erreur')
                ->body('Service introuvable.')
                ->send();
            return;
        }

        // Calculer les montants
        $subtotal = $this->cartTotal;
        $taxAmount = $subtotal * 0.2; // TVA 20%
        $totalAmount = $subtotal + $taxAmount;

        // Créer la commande
        $order = Order::create([
            'type' => OrderTypeEnum::SUBSCRIPTION,
            'status' => 'pending',
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'customer_id' => Auth::user()->customer->id,
            'customer_service_id' => $this->selectedService,
        ]);

        // Ajouter les items à la commande
        foreach ($this->cart as $item) {
            $product = Product::find($item['product_id']);
            if ($product && $product->prices->first()) {
                $order->items()->create([
                    "unit_price" => $item['price'],
                    "total_price" => $item['price'],
                    "order_id" => $order->id,
                    "product_id" => $product->id,
                    "product_price_id" => $product->prices->first()->id,
                ]);
            }
        }

        $order->logs()->create([
            'libelle' => 'Création de votre commande de modules',
        ]);

        dispatch(new CreateInvoiceByOrder($order));

        // Vider le panier
        $this->cart = [];
        $this->calculateTotal();

        Notification::make()
            ->success()
            ->title('Commande créée')
            ->body('Votre commande a été créée avec succès.')
            ->send();

        return $this->redirect(route('client.account.order.show', $order->id));
    }

    public function render()
    {
        return view('livewire.client.account.cart-module');
    }
}
