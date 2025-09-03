<?php

namespace App\Livewire\Client\Account;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Souscription')]
class CartIndex extends Component
{
    public bool $hasPaymentMethod;

    public function mount()
    {
        $this->hasPaymentMethod = Auth::user()->customer->hasPaymentMethods();
    }

    public function render()
    {
        return view('livewire.client.account.cart-index');
    }
}
