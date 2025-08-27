<?php

namespace App\Livewire\Client\Account;

use App\Enum\Commerce\OrderStatusEnum;
use App\Enum\Commerce\OrderTypeEnum;
use App\Enum\Product\ProductCategoryEnum;
use App\Enum\Product\ProductPriceFrequencyEnum;
use App\Jobs\Commerce\CreateInvoiceByOrder;
use App\Models\Commerce\Order;
use App\Models\Product\Product;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Souscription')]
class CartLicense extends Component implements HasSchemas
{
    use InteractsWithSchemas;
    public ?array $data = [];

    public function mount()
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Toggle pour la fréquence de paiement
                Toggle::make('is_annual')
                    ->label('Abonnement annuel')
                    ->helperText('Économisez avec l\'abonnement annuel')
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark')
                    ->onColor('success')
                    ->offColor('gray')
                    ->inline()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Réinitialiser la sélection de licence quand on change la fréquence
                        $set('license_id', null);
                    }),

                // Sélection de licence avec prix dynamiques
                Radio::make('product_id')
                    ->label('Sélectionnez votre licence')
                    ->options(function (callable $get) {
                        $isAnnual = $get('is_annual') ?? false;
                        $frequency = $isAnnual ? ProductPriceFrequencyEnum::ANNUAL : ProductPriceFrequencyEnum::MONTHLY;

                        return Product::where('category', ProductCategoryEnum::LICENSE)
                            ->with(['prices' => function ($query) use ($frequency) {
                                $query->where('frequency', $frequency);
                            }])
                            ->get()
                            ->mapWithKeys(function ($license) use ($isAnnual) {
                                $price = $license->prices->first();
                                if ($price) {
                                    $amount = $price->price;
                                    $priceText = number_format($amount, 2) . ' €';
                                    if ($isAnnual) {
                                        $monthlyEquivalent = $amount / 12;
                                        $priceText .= ' /an (soit ' . number_format($monthlyEquivalent, 2) . ' €/mois)';
                                    } else {
                                        $priceText .= ' /mois';
                                    }
                                } else {
                                    $priceText = 'Prix non disponible';
                                }

                                return [
                                    $license->id => $license->name . ' - ' . $priceText
                                ];
                            })
                            ->toArray();
                    })
                    ->descriptions(function () {
                        return Product::where('category', ProductCategoryEnum::LICENSE)
                            ->with(['features'])
                            ->get()
                            ->mapWithKeys(function ($license) {
                                $description = $license->description;

                                // Ajouter la liste des features incluses
                                if ($license->features->isNotEmpty()) {
                                    $description .= "\n\n**Fonctionnalités incluses :**\n";
                                    foreach ($license->features as $feature) {
                                        $description .= "• " . $feature->name . "\n";
                                    }
                                }

                                return [$license->id => $description];
                            })
                            ->toArray();
                    })
                    ->extraFieldWrapperAttributes([
                        'class' => 'bg-white rounded-lg border border-gray-200 p-4 space-y-3'
                    ])
                    ->columns(1)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('additional_modules', []);
                        $set('options', []);
                        $set('support_level', null);
                    })
                    ->required()
                    ->live(),
            ])
            ->statePath('data');
    }

    public function render()
    {
        return view('livewire.client.account.cart-license');
    }

    public function subscribe()
    {
        $data = $this->form->getState();
        $product = Product::find($data['product_id']);
        if($data['is_annual']){
            $price = $product->prices->where('frequency', ProductPriceFrequencyEnum::ANNUAL)->first();
        }else{
            $price = $product->prices->where('frequency', ProductPriceFrequencyEnum::MONTHLY)->first();
        }

        try {
            // Création de la commande
            $order = Order::create([
                'type' => OrderTypeEnum::SUBSCRIPTION,
                'status' => OrderStatusEnum::PENDING,
                'subtotal' => $price->price - ($price->price * 0.2),
                'tax_amount' => $price->price * 0.2,
                'total_amount' => $price->price,
                'customer_id' => Auth::user()->customer->id,
            ]);

            $order->items()->create([
                'unit_price' => $price->price - ($price->price * 0.2),
                'quantity' => 1,
                'total_price' => $price->price,
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_price_id' => $price->id,
            ]);

            $order->logs()->create([
                'libelle' => 'Création de votre commande',
            ]);
            dispatch(new CreateInvoiceByOrder($order));
            return $this->redirect(route('client.account.order.show', $order->id));
        }catch (\Exception $e) {
            Log::error("Erreur lors de la création de la commande : " . $e->getMessage());
            throw $e;
        }
    }
}
