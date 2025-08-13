<?php

namespace App\Livewire\Client;

use App\Models\Invoice;
use App\Models\Customer;
use App\Enums\InvoiceStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class Invoices extends Component
{
    use WithPagination;

    public $customer;
    public $selectedInvoice = null;
    public $showInvoiceModal = false;
    public $search = '';
    public $statusFilter = 'all';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    // Statistiques
    public $stats = [];

    public function mount()
    {
        $this->customer = Auth::user()->customer;
        $this->loadStats();
    }

    public function loadStats()
    {
        if (!$this->customer) return;

        $invoices = $this->customer->invoices();

        $this->stats = [
            'total_invoices' => $invoices->count(),
            'paid_invoices' => $invoices->where('status', InvoiceStatus::PAID)->count(),
            'pending_invoices' => $invoices->where('status', InvoiceStatus::PENDING)->count(),
            'overdue_invoices' => $invoices->where('status', InvoiceStatus::OVERDUE)->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'paid_amount' => $invoices->where('status', InvoiceStatus::PAID)->sum('total_amount'),
            'pending_amount' => $invoices->whereIn('status', [InvoiceStatus::PENDING, InvoiceStatus::OVERDUE])->sum('total_amount')
        ];
    }

    public function getInvoicesProperty()
    {
        if (!$this->customer) {
            return collect();
        }

        return $this->customer->invoices()
            ->with(['invoiceItems.product', 'payments'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('invoice_number', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== 'all', function($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    public function showInvoiceDetails($invoiceId)
    {
        $this->selectedInvoice = Invoice::with(['invoiceItems.product', 'payments', 'customer'])
            ->where('customer_id', $this->customer->id)
            ->find($invoiceId);

        if ($this->selectedInvoice) {
            $this->showInvoiceModal = true;
        }
    }

    public function closeInvoiceModal()
    {
        $this->showInvoiceModal = false;
        $this->selectedInvoice = null;
    }

    public function downloadInvoice($invoiceId)
    {
        $invoice = Invoice::where('customer_id', $this->customer->id)->find($invoiceId);

        if ($invoice) {
            return redirect()->route('invoice.pdf', $invoice);
        }
    }

    public function payInvoice($invoiceId)
    {
        // Redirection vers le système de paiement (Stripe)
        $invoice = Invoice::where('customer_id', $this->customer->id)->find($invoiceId);

        if ($invoice && $invoice->status !== InvoiceStatus::PAID) {
            // Logique de redirection vers Stripe ou autre système de paiement
            session()->flash('info', 'Redirection vers le système de paiement...');
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    #[Title("Mes Factures")]
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.client.invoices', [
            'invoices' => $this->invoices
        ]);
    }
}
