<?php

namespace App\Livewire\Client\Widgets;

use App\Models\Customer;
use App\Enums\InvoiceStatus;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class InvoicesSummary extends Component
{
    public $customer;
    public $recentInvoices;
    public $stats;

    public function mount()
    {
        $this->customer = Auth::user()->customer;
        $this->loadData();
    }

    public function loadData()
    {
        if (!$this->customer) return;

        // Dernières factures
        $this->recentInvoices = $this->customer->invoices()
            ->latest()
            ->take(3)
            ->get();

        // Statistiques rapides
        $this->stats = [
            'pending_count' => $this->customer->invoices()->where('status', InvoiceStatus::PENDING)->count(),
            'pending_amount' => $this->customer->invoices()->where('status', InvoiceStatus::PENDING)->sum('total_amount'),
            'overdue_count' => $this->customer->invoices()->where('status', InvoiceStatus::OVERDUE)->count(),
        ];
    }

    public function render()
    {
        return view('livewire.client.widgets.invoices-summary');
    }
}
