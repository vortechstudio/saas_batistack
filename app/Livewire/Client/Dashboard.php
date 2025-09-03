<?php

namespace App\Livewire\Client;

use App\Models\Commerce\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Tableau de bord')]
class Dashboard extends Component
{
    public $latestOrder;
    public $countOrder;

    public function mount()
    {
        $this->latestOrder = Order::where('customer_id', Auth::user()->customer->id)->latest()->first();
        $this->countOrder = Order::where('customer_id', Auth::user()->customer->id)->count();
    }

    public function render()
    {
        return view('livewire.client.dashboard');
    }
}
