<?php
namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class SolutionsPage extends Component
{
    #[Layout('livewire.public.main-layout')]
    #[Title('Solutions BTP - Batistack')]

    public function render()
    {
        return view('livewire.public.solutions-page');
    }
}
