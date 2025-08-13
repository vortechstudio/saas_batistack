<?php

namespace App\Livewire\Client;

use App\Models\Invoice;
use App\Models\Customer;
use App\Enums\InvoiceStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        // Gestion des retours de paiement
        if (request('payment') === 'success' && request('invoice')) {
            session()->flash('success', 'Paiement effectué avec succès !');
        } elseif (request('payment') === 'cancelled') {
            session()->flash('info', 'Paiement annulé.');
        }
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
            try {
                // Créer ou récupérer le client Stripe
                $stripeCustomer = $this->getOrCreateStripeCustomer();

                // Créer une session de paiement Stripe Checkout
                $checkoutSession = \Stripe\Checkout\Session::create([
                    'customer' => $stripeCustomer->id,
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => strtolower($invoice->currency),
                            'product_data' => [
                                'name' => 'Facture ' . $invoice->invoice_number,
                                'description' => $invoice->description,
                            ],
                            'unit_amount' => (int) ($invoice->total_amount * 100), // Montant en centimes
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => route('client.invoices') . '?payment=success&invoice=' . $invoice->id,
                    'cancel_url' => route('client.invoices') . '?payment=cancelled',
                    'metadata' => [
                        'invoice_id' => $invoice->id,
                        'customer_id' => $this->customer->id,
                    ],
                ]);

                // Sauvegarder l'ID de session pour le suivi
                $invoice->update([
                    'metadata' => array_merge($invoice->metadata ?? [], [
                        'stripe_checkout_session_id' => $checkoutSession->id
                    ])
                ]);

                // Rediriger vers Stripe Checkout
                return redirect($checkoutSession->url);

            } catch (\Exception $e) {
                session()->flash('error', 'Erreur lors de la création du paiement : ' . $e->getMessage());
                Log::error('Erreur paiement Stripe', [
                    'invoice_id' => $invoice->id,
                    'customer_id' => $this->customer->id,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            session()->flash('warning', 'Cette facture ne peut pas être payée.');
        }
    }

    /**
     * Créer ou récupérer le client Stripe
     */
    private function getOrCreateStripeCustomer()
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        // Si le client a déjà un ID Stripe, le récupérer
        if ($this->customer->stripe_customer_id) {
            try {
                return \Stripe\Customer::retrieve($this->customer->stripe_customer_id);
            } catch (\Exception $e) {
                // Si le client n'existe plus sur Stripe, en créer un nouveau
            }
        }

        // Créer un nouveau client Stripe
        $stripeCustomer = \Stripe\Customer::create([
            'name' => $this->customer->stripeName(),
            'email' => $this->customer->stripeEmail(),
            'address' => $this->customer->stripeAddress(),
            'metadata' => [
                'customer_id' => $this->customer->id,
                'company_name' => $this->customer->company_name,
            ],
        ]);

        // Sauvegarder l'ID Stripe dans la base de données
        $this->customer->update([
            'stripe_customer_id' => $stripeCustomer->id
        ]);

        return $stripeCustomer;
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
