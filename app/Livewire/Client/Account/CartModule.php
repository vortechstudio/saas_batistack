<?php

namespace App\Livewire\Client\Account;

use App\Enum\Commerce\OrderTypeEnum;
use App\Jobs\Commerce\CreateInvoiceByOrder;
use App\Models\Commerce\Order;
use App\Models\Customer\CustomerService;
use App\Models\Product\Feature;
use App\Models\Product\Product;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Souscription')]
class CartModule extends Component implements HasSchemas
{
    use InteractsWithSchemas;
    public ?array $data = [];
    public bool $hasServices;

    public function mount()
    {
        $this->hasServices = Auth::user()->customer->services->count() > 0 ? false : true;
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('service_id')
                    ->label('Sélectionnez votre service')
                    ->options(Auth::user()->customer->services->pluck('service_code', 'id'))
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('feature_id', null);
                    }),
                
                Select::make('feature_id')
                    ->label('Fonctionnalités non disponibles')
                    ->options(function (callable $get) {
                        $serviceId = $get('service_id');
                        
                        if (!$serviceId) {
                            return [];
                        }
                        
                        // Récupérer le service sélectionné
                        $service = CustomerService::with('product.features')->find($serviceId);
                        
                        if (!$service || !$service->product) {
                            return [];
                        }
                        
                        // Récupérer toutes les features disponibles
                        $allFeatures = Feature::all();
                        
                        // Récupérer les features du produit (celles qui sont disponibles)
                        $productFeatures = $service->product->features;
                        
                        // Filtrer pour obtenir seulement les features non disponibles
                        $unavailableFeatures = $allFeatures->whereNotIn('id', $productFeatures->pluck('id'));
                        
                        return $unavailableFeatures->pluck('name', 'id')->toArray();
                    })
                    ->visible(fn (callable $get) => !empty($get('service_id')))
                    ->placeholder('Sélectionnez une fonctionnalité à ajouter'),
            ])
            ->statePath('data');
    }

    public function subscribeModule()
    {
        $data = $this->form->getState();
        
        // Validation des données
        if (empty($data['service_id'])) {
            $this->addError('service_id', 'Veuillez sélectionner un service.');
            return;
        }
        
        if (empty($data['feature_id'])) {
            $this->addError('feature_id', 'Veuillez sélectionner une fonctionnalité à ajouter.');
            return;
        }
        
        // Récupérer le service et la feature sélectionnés
        $service = CustomerService::with('product', 'product.prices')->find($data['service_id']);
        $feature = Feature::find($data['feature_id']);
        $product = Product::where('slug', $feature->slug)->first();
        
        if (!$service || !$feature) {
            session()->flash('error', 'Service ou fonctionnalité introuvable.');
            Notification::make()
                ->danger()
                ->color('error')
                ->title('Erreur')
                ->body('Service ou fonctionnalité introuvable.')
                ->send();
            return;
        }
        
        // Vérifier que la feature n'est pas déjà disponible pour ce produit
        if ($service->product->features->contains($feature->id)) {
            session()->flash('error', 'Cette fonctionnalité est déjà disponible pour ce service.');
            Notification::make()
                ->danger()
                ->color('error')
                ->title('Erreur')
                ->body('Cette fonctionnalité est déjà disponible pour ce service.')
                ->send();
            return;
        }
        
        // Ici vous pouvez ajouter la logique pour traiter l'ajout de la fonctionnalité
        // Par exemple, créer une commande, ajouter au panier, etc.
        $productPrice = $product->prices->first()->price;

        $order = Order::create([
            'type' => OrderTypeEnum::SUBSCRIPTION,
            'status' => 'pending',
            'subtotal' => $productPrice,
            'tax_amount' => $productPrice * 0.2,
            'total_amount' => $productPrice + ($productPrice * 0.2),
            'customer_id' => Auth::user()->customer->id,
            'customer_service_id' => $data['service_id'],
        ]);

        $order->items()->create([
            "unit_price" => $productPrice,
            "total_price" => $productPrice,
            "order_id" => $order->id,
            "product_id" => $product->id,
            "product_price_id" => $product->prices->first()->id,
        ]);

        $order->logs()->create([
            'libelle' => 'Création de votre commande',
        ]);

        dispatch(new CreateInvoiceByOrder($order));
        
        Notification::make()
                ->success()
                ->color('success')
                ->title('Succès')
                ->body("Fonctionnalité '{$feature->name}' ajoutée au service '{$service->service_code}'.")
                ->send();

        return $this->redirect(route('client.account.order.show', $order->id));
    }

    public function render()
    {
        return view('livewire.client.account.cart-module');
    }
}
