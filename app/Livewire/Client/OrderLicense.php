<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class OrderLicense extends Component
{
    #[Title('Commander une licence')]
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.client.order-license');
    }
}
