<?php

namespace App\Livewire\Client;

use App\Enum\Product\ProductCategoryEnum;
use App\Enum\Product\ProductPriceFrequencyEnum;
use App\Models\Product\Product;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.client')]
#[Title('Tableau de bord')]
class Catalogue extends Component
{
    public bool $isAnnual = false;
    public $licences = [];

    public function mount()
    {
        $this->loadLicences();
    }

    public function toggleFrequency()
    {
        $this->isAnnual = !$this->isAnnual;
        $this->loadLicences();
    }

    public function loadLicences()
    {
        $frequency = $this->isAnnual ? ProductPriceFrequencyEnum::ANNUAL : ProductPriceFrequencyEnum::MONTHLY;

        $this->licences = Product::where('category', ProductCategoryEnum::LICENSE)
            ->with(['prices' => function ($query) use ($frequency) {
                $query->where('frequency', $frequency);
            }, 'features'])
            ->get()
            ->map(function ($licence) {
                $price = $licence->prices->first();
                $amount = $price ? $price->price : 0;

                return [
                    'id' => $licence->id,
                    'name' => $licence->name,
                    'description' => $licence->description,
                    'slug' => $licence->slug,
                    'features' => $licence->features->pluck('name')->toArray(),
                    'price_id' => $price?->id,
                    'price' => $amount,
                    'price_formatted' => $price ? number_format($amount, 2) . ' €' : 'Prix non disponible',
                    'monthly_equivalent' => $this->isAnnual && $price ? number_format($amount / 12, 2) . ' €/mois' : null,
                    'savings' => $this->isAnnual && $price ? $this->calculateSavings($licence) : null,
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
    
    public function render()
    {
        return view('livewire.client.catalogue');
    }
}
