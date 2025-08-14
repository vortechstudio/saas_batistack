<?php
namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class PricingPage extends Component
{
    #[Layout('livewire.public.main-layout')]
    #[Title('Tarifs et Plans - Batistack')]

    public function render()
    {
        return view('livewire.public.pricing-page');
    }
}
