<?php

namespace App\Livewire\Client\Account;

use App\Models\Commerce\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Mon Compte')]
class Dashboard extends Component
{
    public $activeTab = 'general';
    public $latestInvoice;

    public function mount()
    {
        // Récupérer la dernière facture
        $this->latestInvoice = Order::where('customer_id', Auth::user()->customer->id)
            ->whereNotNull('delivered_at')
            ->latest('delivered_at')
            ->first();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.client.account.dashboard');
    }
}
