<?php
namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;


#[Layout('livewire.public.main-layout')]
#[Title('Comptabilité BTP - Batistack')]
class ComptabiliteBtpPage extends Component
{
    public function render()
    {
        return view('livewire.public.comptabilite-btp-page');
    }
}
