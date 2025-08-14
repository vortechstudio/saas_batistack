<?php
namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class FacturationBtpPage extends Component
{
    #[Layout('livewire.public.main-layout')]
    #[Title('Facturation BTP - Batistack')]

    public function render()
    {
        return view('livewire.public.facturation-btp-page');
    }
}
