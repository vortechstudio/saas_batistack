<?php
namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('livewire.public.main-layout')]
#[Title('Devis & Métrés - Batistack')]
class DevisMetresPage extends Component
{

    public function render()
    {
        return view('livewire.public.devis-metres-page');
    }
}
