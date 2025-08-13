<?php

namespace App\Livewire\Client;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Module;
use App\Models\Option;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\License;
use App\Enums\BillingCycle;
use App\Enums\InvoiceStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class OrderLicense extends Component
{
    // Étapes du processus
    public $currentStep = 1;
    public $totalSteps = 6;

    // Données de commande
    public $selectedProduct = null;
    public $billingCycle = 'monthly';
    public $domain = '';
    public $selectedModules = [];
    public $selectedOptions = [];

    // Données calculées
    public $subtotal = 0;
    public $total = 0;
    public $invoice = null;

    // Données du client
    public $customer;

    // Collections
    public $products;
    public $availableModules = [];
    public $availableOptions = [];

    public function mount()
    {
        $this->customer = Auth::user()->customer;
        $this->products = Product::active()->get();
    }

    public function selectProduct($productId)
    {
        $this->selectedProduct = Product::find($productId);
        $this->loadProductModulesAndOptions();
        $this->calculateTotal();
        $this->nextStep();
    }

    public function setBillingCycle($cycle)
    {
        $this->billingCycle = $cycle;
        $this->calculateTotal();
        $this->nextStep();
    }

    public function setDomain()
    {
        $this->validate([
            'domain' => 'required|string|max:255|unique:licenses,domain'
        ], [
            'domain.unique' => 'Ce domaine est déjà utilisé par une autre licence.'
        ]);

        $this->nextStep();
    }

    public function toggleModule($moduleId)
    {
        if (in_array($moduleId, $this->selectedModules)) {
            $this->selectedModules = array_filter($this->selectedModules, fn($id) => $id != $moduleId);
        } else {
            $this->selectedModules[] = $moduleId;
        }

        $this->calculateTotal();
    }

    public function toggleOption($optionId)
    {
        if (in_array($optionId, $this->selectedOptions)) {
            $this->selectedOptions = array_filter($this->selectedOptions, fn($id) => $id != $optionId);
        } else {
            $this->selectedOptions[] = $optionId;
        }

        $this->calculateTotal();
    }

    public function confirmModulesAndOptions()
    {
        $this->calculateTotal();
        $this->nextStep();
    }

    public function generateInvoice()
    {
        DB::transaction(function () {
            // Créer la facture
            $this->invoice = Invoice::create([
                'customer_id' => $this->customer->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'status' => InvoiceStatus::PENDING,
                'subtotal' => $this->subtotal,
                'tax_amount' => 0, // À adapter selon vos besoins
                'total' => $this->total,
                'currency' => 'EUR',
                'due_date' => now()->addDays(30),
                'issued_at' => now(),
            ]);

            // Ajouter les éléments de facture
            $this->addInvoiceItems();
        });

        $this->nextStep();
    }

    public function payInvoice()
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            // Créer ou récupérer le client Stripe
            $stripeCustomer = $this->getOrCreateStripeCustomer();

            // Créer la session de paiement Stripe
            $session = Session::create([
                'customer' => $stripeCustomer->id,
                'payment_method_types' => ['card'],
                'line_items' => $this->buildStripeLineItems(),
                'mode' => 'payment',
                'success_url' => route('client.order.success', ['invoice' => $this->invoice->id]),
                'cancel_url' => route('client.order.cancel', ['invoice' => $this->invoice->id]),
                'metadata' => [
                    'invoice_id' => $this->invoice->id,
                    'customer_id' => $this->customer->id,
                ],
            ]);

            // Sauvegarder l'ID de session
            $this->invoice->update([
                'stripe_checkout_session_id' => $session->id
            ]);

            // Rediriger vers Stripe Checkout
            return redirect($session->url);

        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la création du paiement : ' . $e->getMessage());
        }
    }

    public function nextStep()
    {
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    private function loadProductModulesAndOptions()
    {
        if (!$this->selectedProduct) return;

        // Charger les modules optionnels (non inclus)
        $this->availableModules = $this->selectedProduct
            ->optionalModules()
            ->active()
            ->ordered()
            ->get();

        // Charger les options disponibles
        $this->availableOptions = $this->selectedProduct
            ->options()
            ->active()
            ->get();
    }

    private function calculateTotal()
    {
        if (!$this->selectedProduct) return;

        $this->subtotal = $this->selectedProduct->calculateTotalPrice(
            $this->selectedModules,
            $this->selectedOptions
        );

        // Ajuster selon le cycle de facturation
        if ($this->billingCycle === 'yearly') {
            $this->subtotal *= 10; // 10 mois au lieu de 12 (2 mois gratuits)
        }

        $this->total = $this->subtotal; // + taxes si nécessaire
    }

    private function addInvoiceItems()
    {
        // Produit principal
        InvoiceItem::create([
            'invoice_id' => $this->invoice->id,
            'description' => $this->selectedProduct->name . ' (' . ucfirst($this->billingCycle) . ')',
            'quantity' => 1,
            'unit_price' => $this->selectedProduct->base_price,
            'total' => $this->selectedProduct->base_price,
        ]);

        // Modules optionnels
        foreach ($this->selectedModules as $moduleId) {
            $module = Module::find($moduleId);
            $price = $this->selectedProduct->modules()
                ->where('modules.id', $moduleId)
                ->first()
                ->pivot
                ->price_override ?? $module->base_price;

            InvoiceItem::create([
                'invoice_id' => $this->invoice->id,
                'description' => 'Module: ' . $module->name,
                'quantity' => 1,
                'unit_price' => $price,
                'total' => $price,
            ]);
        }

        // Options
        foreach ($this->selectedOptions as $optionId) {
            $option = Option::find($optionId);

            InvoiceItem::create([
                'invoice_id' => $this->invoice->id,
                'description' => 'Option: ' . $option->name,
                'quantity' => 1,
                'unit_price' => $option->price,
                'total' => $option->price,
            ]);
        }
    }

    private function getOrCreateStripeCustomer()
    {
        if ($this->customer->stripe_customer_id) {
            return \Stripe\Customer::retrieve($this->customer->stripe_customer_id);
        }

        $stripeCustomer = \Stripe\Customer::create([
            'email' => $this->customer->email,
            'name' => $this->customer->company_name,
            'metadata' => [
                'customer_id' => $this->customer->id,
            ],
        ]);

        $this->customer->update([
            'stripe_customer_id' => $stripeCustomer->id
        ]);

        return $stripeCustomer;
    }

    private function buildStripeLineItems()
    {
        $lineItems = [];

        // Produit principal
        $lineItems[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $this->selectedProduct->name . ' (' . ucfirst($this->billingCycle) . ')',
                ],
                'unit_amount' => $this->selectedProduct->base_price * 100, // Stripe utilise les centimes
            ],
            'quantity' => 1,
        ];

        // Modules et options...
        // (Code similaire pour ajouter modules et options)

        return $lineItems;
    }

    private function generateInvoiceNumber()
    {
        $year = date('Y');
        $lastInvoice = Invoice::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastInvoice ? (int)substr($lastInvoice->invoice_number, -4) + 1 : 1;

        return $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    #[Title('Commander une licence')]
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.client.order-license');
    }
}
