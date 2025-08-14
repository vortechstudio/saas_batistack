<?php
namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class PricingPage extends Component
{
    #[Layout('livewire.public.main-layout')]
    #[Title('Tarifs et Plans - Batistack')]

    public $isAnnual = false; // Changé à false pour commencer en mode mensuel

    public function toggleBilling()
    {
        $this->isAnnual = !$this->isAnnual;
    }

    public function getStarterPrice()
    {
        return $this->isAnnual ? 41.66 : 49.99;
    }

    public function getProfessionalPrice()
    {
        return $this->isAnnual ? 83.33 : 99.99;
    }

    public function getEnterprisePrice()
    {
        return $this->isAnnual ? 166.66 : 199.99;
    }

    public function render()
    {
        return view('livewire.public.pricing-page');
    }
}
