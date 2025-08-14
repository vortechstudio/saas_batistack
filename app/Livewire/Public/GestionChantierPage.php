<?php
namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class GestionChantierPage extends Component
{
    #[Layout('livewire.public.main-layout')]
    #[Title('Gestion de Chantier - Batistack')]

    public function render()
    {
        return view('livewire.public.gestion-chantier-page');
    }
}
