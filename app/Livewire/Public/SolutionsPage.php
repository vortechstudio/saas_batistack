<?php
namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('livewire.public.main-layout')]
#[Title('Solutions BTP - Batistack')]
class SolutionsPage extends Component
{
    public function render()
    {
        return view('livewire.public.solutions-page');
    }
}
