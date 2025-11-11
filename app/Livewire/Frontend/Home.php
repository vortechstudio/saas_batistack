<?php

namespace App\Livewire\Frontend;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.frontend')]
#[Title('Accueil')]
class Home extends Component
{
    public function render()
    {
        return view('livewire.frontend.home');
    }
}
