<?php
namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class ComptabiliteBtpPage extends Component
{
    #[Layout('livewire.public.main-layout')]
    #[Title('Comptabilité BTP - Batistack')]

    public function render()
    {
        return view('livewire.public.comptabilite-btp-page');
    }
}
