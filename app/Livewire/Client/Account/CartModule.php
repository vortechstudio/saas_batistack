<?php

namespace App\Livewire\Client\Account;

use App\Models\Customer\CustomerService;
use App\Models\Product\Feature;
use Filament\Forms\Components\Select;
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
        $service = CustomerService::with('product')->find($data['service_id']);
        $feature = Feature::find($data['feature_id']);
        
        if (!$service || !$feature) {
            session()->flash('error', 'Service ou fonctionnalité introuvable.');
            return;
        }
        
        // Vérifier que la feature n'est pas déjà disponible pour ce produit
        if ($service->product->features->contains($feature->id)) {
            session()->flash('error', 'Cette fonctionnalité est déjà disponible pour ce service.');
            return;
        }
        
        // Ici vous pouvez ajouter la logique pour traiter l'ajout de la fonctionnalité
        // Par exemple, créer une commande, ajouter au panier, etc.
        
        session()->flash('success', "Fonctionnalité '{$feature->name}' ajoutée au service '{$service->service_code}'.");
        
        // Réinitialiser le formulaire
        $this->form->fill();
    }

    public function render()
    {
        return view('livewire.client.account.cart-module');
    }
}
