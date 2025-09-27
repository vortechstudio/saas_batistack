<?php

namespace App\Livewire\Client\Account;

use App\Enum\Commerce\OrderStatusEnum;
use App\Enum\Commerce\OrderTypeEnum;
use App\Enum\Product\ProductCategoryEnum;
use App\Enum\Product\ProductPriceFrequencyEnum;
use App\Jobs\Commerce\CreateInvoiceByOrder;
use App\Models\Commerce\Order;
use App\Models\Product\Product;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Licences - Souscription')]
class CartLicense extends Component
{
    public bool $isAnnual = false;
    public $selectedLicense = null;
    public $licenses = [];
    public $selectedLicenseData = null;

    public function mount()
    {
        $this->loadLicenses();
    }

    public function toggleFrequency()
    {
        $this->isAnnual = !$this->isAnnual;
        $this->selectedLicense = null;
        $this->selectedLicenseData = null;
        $this->loadLicenses();
    }

    public function loadLicenses()
    {
        $frequency = $this->isAnnual ? ProductPriceFrequencyEnum::ANNUAL : ProductPriceFrequencyEnum::MONTHLY;

        $this->licenses = Product::where('category', ProductCategoryEnum::LICENSE)
            ->with(['prices' => function ($query) use ($frequency) {
                $query->where('frequency', $frequency);
            }, 'features'])
            ->get()
            ->map(function ($license) {
                $price = $license->prices->first();
                $amount = $price ? $price->price : 0;

                return [
                    'id' => $license->id,
                    'name' => $license->name,
                    'description' => $license->description,
                    'slug' => $license->slug,
                    'features' => $license->features->pluck('name')->toArray(),
                    'price_id' => $price?->id,
                    'price' => $amount,
                    'price_formatted' => $price ? number_format($amount, 2) . ' €' : 'Prix non disponible',
                    'monthly_equivalent' => $this->isAnnual && $price ? number_format($amount / 12, 2) . ' €/mois' : null,
                    'savings' => $this->isAnnual && $price ? $this->calculateSavings($license) : null,
                ];
            })
            ->toArray();
    }

    private function calculateSavings($license)
    {
        $monthlyPrice = $license->prices->where('frequency', ProductPriceFrequencyEnum::MONTHLY)->first();
        $annualPrice = $license->prices->where('frequency', ProductPriceFrequencyEnum::ANNUAL)->first();

        if ($monthlyPrice && $annualPrice) {
            $yearlyMonthly = $monthlyPrice->price * 12;
            $savings = $yearlyMonthly - $annualPrice->price;
            $savingsPercent = round(($savings / $yearlyMonthly) * 100);

            return [
                'amount' => number_format($savings, 2) . ' €',
                'percent' => $savingsPercent . '%'
            ];
        }

        return null;
    }

    public function selectLicense($licenseId)
    {
        $this->selectedLicense = $licenseId;
        $this->selectedLicenseData = collect($this->licenses)->firstWhere('id', $licenseId);
    }

    public function subscribe()
    {
        if (!$this->selectedLicense) {
            Notification::make()
                ->warning()
                ->title('Sélection requise')
                ->body('Veuillez sélectionner une licence avant de continuer.')
                ->send();
            return;
        }

        $product = Product::find($this->selectedLicense);
        $frequency = $this->isAnnual ? ProductPriceFrequencyEnum::ANNUAL : ProductPriceFrequencyEnum::MONTHLY;
        $price = $product->prices->where('frequency', $frequency)->first();

        if (!$price) {
            Notification::make()
                ->danger()
                ->title('Erreur')
                ->body('Prix non disponible pour cette licence.')
                ->send();
            return;
        }

        try {
            // Calcul des montants
            $subtotal = $price->price / 1.2; // Prix HT
            $taxAmount = $price->price - $subtotal; // TVA
            $totalAmount = $price->price; // Prix TTC

            // Création de la commande
            $order = Order::create([
                'type' => OrderTypeEnum::SUBSCRIPTION,
                'status' => OrderStatusEnum::PENDING,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'customer_id' => Auth::user()->customer->id,
            ]);

            $order->items()->create([
                'unit_price' => $subtotal,
                'quantity' => 1,
                'total_price' => $totalAmount,
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_price_id' => $price->id,
            ]);

            $order->logs()->create([
                'libelle' => 'Création de votre commande de licence',
            ]);

            dispatch(new CreateInvoiceByOrder($order));

            Notification::make()
                ->success()
                ->title('Commande créée')
                ->body('Votre commande de licence a été créée avec succès.')
                ->send();

            return $this->redirect(route('client.account.order.show', $order->id));

        } catch (\Exception $e) {
            Log::error("Erreur lors de la création de la commande : " . $e->getMessage());

            Notification::make()
                ->danger()
                ->title('Erreur')
                ->body('Une erreur est survenue lors de la création de votre commande.')
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.client.account.cart-license');
    }
}
