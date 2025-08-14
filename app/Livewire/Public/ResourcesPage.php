<?php
namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class ResourcesPage extends Component
{
    #[Layout('livewire.public.main-layout')]
    #[Title('Ressources')]

    public function render()
    {
        return view('livewire.public.resources-page');
    }
}
