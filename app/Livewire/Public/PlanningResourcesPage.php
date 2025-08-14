<?php
namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class PlanningResourcesPage extends Component
{
    #[Layout('livewire.public.main-layout')]
    #[Title('Planning & Resources - Batistack')]

    public function render()
    {
        return view('livewire.public.planning-resources-page');
    }
}