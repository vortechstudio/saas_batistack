<?php

namespace App\Livewire\Frontend;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.frontend')]
class Company extends Component
{
    public function render()
    {
        return view('livewire.frontend.company');
    }
}
