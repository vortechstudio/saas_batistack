<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer quelques clients existants
        $customers = Customer::take(3)->get();
        $products = Product::take(2)->get();

        if ($customers->isEmpty()) {
            $this->command->warn('Aucun client trouvé. Veuillez d\'abord exécuter le seeder des clients.');
            return;
        }

        foreach ($customers as $customer) {
            // Créer 2-3 factures par client
            for ($i = 1; $i <= rand(2, 3); $i++) {
                $invoice = Invoice::create([
                    'customer_id' => $customer->id,
                    'invoice_number' => 'INV-' . date('Y') . '-' . str_pad(($customer->id * 10) + $i, 4, '0', STR_PAD_LEFT),
                    'status' => collect(InvoiceStatus::cases())->random(),
                    'subtotal_amount' => $subtotal = rand(50000, 200000) / 100, // 500€ à 2000€
                    'tax_amount' => $tax = round($subtotal * 0.20, 2), // TVA 20%
                    'total_amount' => $subtotal + $tax,
                    'currency' => 'EUR',
                    'due_date' => now()->addDays(rand(15, 45)),
                    'paid_at' => rand(0, 1) ? now()->subDays(rand(1, 30)) : null,
                    'description' => 'Facture pour services SaaS BatiStack',
                    'notes' => 'Merci pour votre confiance.',
                ]);

                // Créer 1-3 lignes de facture
                for ($j = 1; $j <= rand(1, 3); $j++) {
                    $quantity = rand(1, 5);
                    $unitPrice = rand(2000, 10000) / 100; // 20€ à 100€
                    $totalPrice = $quantity * $unitPrice;

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $products->isNotEmpty() ? $products->random()->id : null,
                        'description' => 'Service SaaS BatiStack - Module ' . $j,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'tax_rate' => 20.00,
                        'tax_amount' => round($totalPrice * 0.20, 2),
                    ]);
                }

                // Créer un paiement pour certaines factures
                if (rand(0, 1)) {
                    Payment::create([
                        'customer_id' => $customer->id,
                        'invoice_id' => $invoice->id,
                        'stripe_payment_intent_id' => 'pi_test_' . uniqid(),
                        'stripe_charge_id' => 'ch_test_' . uniqid(),
                        'amount' => $invoice->total_amount,
                        'currency' => 'EUR',
                        'status' => collect(PaymentStatus::cases())->random(),
                        'payment_method' => collect(PaymentMethod::cases())->random(),
                        'payment_method_details' => json_encode([
                            'last4' => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                            'brand' => collect(['visa', 'mastercard', 'amex'])->random(),
                        ]),
                        'processed_at' => rand(0, 1) ? now()->subDays(rand(1, 30)) : null,
                        'metadata' => json_encode([
                            'source' => 'admin_panel',
                            'created_by' => 'system',
                        ]),
                    ]);
                }
            }
        }

        $this->command->info('Données de facturation créées avec succès !');
    }
}
