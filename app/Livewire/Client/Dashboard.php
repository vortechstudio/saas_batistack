<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Tableau de bord')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.client.dashboard');
    }
}
