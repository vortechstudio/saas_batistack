<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use LaravelDaily\Invoices\Invoice as InvoicePDF;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Classes\InvoiceItem;

class InvoicePdfController extends Controller
{
    public function download(Invoice $invoice)
    {
        // Fonction helper pour nettoyer l'encodage UTF-8
        $cleanUtf8 = function($text) {
            if (is_null($text)) return '';
            return mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        };

        try {
            // Informations de l'entreprise (vendeur)
            $seller = new Party([
                'name' => $cleanUtf8(config('app.name', 'BatiStack SaaS')),
                'phone' => '+33 1 23 45 67 89',
                'custom_fields' => [
                    'email' => 'contact@batistack.com',
                    'SIRET' => '12345678901234',
                    'TVA' => 'FR12345678901',
                ],
            ]);

            // Informations du client (acheteur)
            $customerName = $invoice->customer->name ?? "Client #{$invoice->customer->id}";
            $customer = new Party([
                'name' => $cleanUtf8($customerName),
                'phone' => $cleanUtf8($invoice->customer->phone ?? ''),
                'address' => $cleanUtf8($invoice->customer->address ?? ''),
                'custom_fields' => [
                    'email' => $cleanUtf8($invoice->customer->email ?? ''),
                    'code_client' => "#{$invoice->customer->id}",
                ],
            ]);

            // Création des lignes de facture
            $items = [];
            foreach ($invoice->invoiceItems as $item) {
                $items[] = InvoiceItem::make($cleanUtf8($item->description ?? 'Article'))
                    ->pricePerUnit($item->unit_price ?? 0)
                    ->quantity($item->quantity ?? 1)
                    ->discount(0);
            }

            // Génération du PDF
            $invoicePdf = InvoicePDF::make('facture')
                ->series('FACT')
                ->sequence($invoice->id)
                ->serialNumberFormat('{SERIES}-{SEQUENCE}')
                ->seller($seller)
                ->buyer($customer)
                ->date($invoice->created_at)
                ->dateFormat('d/m/Y')
                ->payUntilDays($invoice->due_date ? $invoice->due_date->diffInDays($invoice->created_at) : 30)
                ->currencySymbol('EUR')
                ->currencyCode($invoice->currency ?? 'EUR')
                ->currencyFormat('{VALUE} {SYMBOL}')
                ->currencyThousandsSeparator(' ')
                ->currencyDecimalPoint(',')
                ->filename($cleanUtf8("Facture_{$invoice->invoice_number}"))
                ->addItems($items);

            // Ajout des notes si présentes
            if ($invoice->notes) {
                $invoicePdf->notes($cleanUtf8($invoice->notes));
            }

            // Ajout du statut si payé
            if ($invoice->status === \App\Enums\InvoiceStatus::PAID) {
                $invoicePdf->status('PAYEE');
            }

            return $invoicePdf->stream();

        } catch (\Exception $e) {
            // En cas d'erreur, retourner une page d'erreur
            abort(500, 'Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }
}