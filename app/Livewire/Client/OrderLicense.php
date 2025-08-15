<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;


#[Layout('components.layouts.app')]
#[Title('Commander une license')]
class OrderLicense extends Component
{
    public function render()
    {
        return view('livewire.client.order-license');
    }
}
