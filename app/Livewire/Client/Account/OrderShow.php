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

    public function refreshData()
    {
        $this->order = Order::with('items', 'logs')->findOrFail($this->order->id);
        if ($this->order->status->value === 'delivered') {
            // redirection vers le services générer.
        }
    }

    public function render()
    {
        return view('livewire.client.account.order-show');
    }
}
