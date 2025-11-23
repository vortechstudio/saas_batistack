<?php

namespace App\Livewire\Frontend;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.frontend')]
class Ressource extends Component
{
    public function render()
    {
        return view('livewire.frontend.ressource');
    }
}
