<?php

namespace App\Livewire\Client\Account;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Souscription')]
class CartIndex extends Component
{
    public function render()
    {
        return view('livewire.client.account.cart-index');
    }
}
