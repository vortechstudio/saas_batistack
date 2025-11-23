<?php

namespace App\Livewire\Client\Support;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.client')]
class ShowTicket extends Component
{
    public function render()
    {
        return view('livewire.client.support.show-ticket');
    }
}
