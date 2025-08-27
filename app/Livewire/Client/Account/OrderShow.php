<?php

namespace App\Livewire\Client\Account;

use App\Models\Commerce\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Souscription')]
class OrderShow extends Component
{
    public Order $order;

    public function mount(int $id)
    {
        $this->order = Order::with('items', 'logs')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.client.account.order-show');
    }
}
