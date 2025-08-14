<?php
namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('livewire.public.main-layout')]
    #[Title('Planning & Resources - Batistack')]
class PlanningResourcesPage extends Component
{
    public function render()
    {
        return view('livewire.public.planning-resources-page');
    }
}
