<?php

namespace App\Http\Controllers;

use App\Models\License;
use Illuminate\Http\Request;
use LaravelDaily\Invoices\Invoice as InvoicePDF;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use Illuminate\Support\Facades\Auth;

class LicensePdfController extends Controller
{
    public function download(License $license)
    {
        // Vérifier que l'utilisateur a accès à cette licence
        if (!Auth::user()->customer || $license->customer_id !== Auth::user()->customer->id) {
            abort(403, 'Accès non autorisé à cette licence');
        }

        // Fonction helper pour nettoyer l'encodage UTF-8
        $cleanUtf8 = function($text) {
            if (is_null($text)) return '';
            return mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        };

        try {
            // Informations de l'entreprise (émetteur de la licence)
            $issuer = new Party([
                'name' => $cleanUtf8(config('app.name', 'BatiStack SaaS')),
                'phone' => '+33 1 23 45 67 89',
                'address' => 'Siège social BatiStack',
                'custom_fields' => [
                    'email' => 'contact@batistack.com',
                    'SIRET' => '12345678901234',
                    'TVA' => 'FR12345678901',
                    'website' => 'www.batistack.com',
                ],
            ]);

            // Informations du client (détenteur de la licence)
            $customerName = $license->customer->name ?? "Client #{$license->customer->id}";
            $licensee = new Party([
                'name' => $cleanUtf8($customerName),
                'phone' => $cleanUtf8($license->customer->phone ?? ''),
                'address' => $cleanUtf8($license->customer->address ?? ''),
                'custom_fields' => [
                    'email' => $cleanUtf8($license->customer->email ?? ''),
                    'code_client' => "#{$license->customer->id}",
                    'licence_key' => $license->license_key,
                ],
            ]);

            // Création des éléments de la licence
            $items = [];

            // Produit principal
            $items[] = InvoiceItem::make($cleanUtf8($license->product->name ?? 'Produit BatiStack'))
                ->description($cleanUtf8($license->product->description ?? 'Licence logiciel BatiStack'))
                ->pricePerUnit(0) // Pas de prix sur un certificat
                ->quantity(1);

            // Modules actifs
            foreach ($license->activeModules as $module) {
                $items[] = InvoiceItem::make($cleanUtf8("Module: {$module->name}"))
                    ->description($cleanUtf8($module->description ?? ''))
                    ->pricePerUnit(0)
                    ->quantity(1);
            }

            // Options actives
            foreach ($license->activeOptions as $option) {
                $items[] = InvoiceItem::make($cleanUtf8("Option: {$option->name}"))
                    ->description($cleanUtf8($option->description ?? ''))
                    ->pricePerUnit(0)
                    ->quantity(1);
            }

            // Génération du certificat PDF
            $certificatePdf = InvoicePDF::make('certificat')
                ->series('CERT')
                ->sequence($license->id)
                ->serialNumberFormat('{SERIES}-{SEQUENCE}')
                ->seller($issuer)
                ->buyer($licensee)
                ->date($license->created_at)
                ->dateFormat('d/m/Y')
                ->payUntilDays($license->expires_at ? $license->expires_at->diffInDays(now()) : 365)
                ->currencySymbol('')
                ->currencyCode('')
                ->currencyFormat('')
                ->filename($cleanUtf8("Certificat_Licence_{$license->license_key}"))
                ->addItems($items);

            // Informations spécifiques à la licence
            $notes = "CERTIFICAT DE LICENCE LOGICIEL\n\n";
            $notes .= "Clé de licence: {$license->license_key}\n";
            $notes .= "Statut: " . $license->status->label() . "\n";
            $notes .= "Date d'activation: " . ($license->starts_at ? $license->starts_at->format('d/m/Y') : 'Non activée') . "\n";
            $notes .= "Date d'expiration: " . ($license->expires_at ? $license->expires_at->format('d/m/Y') : 'Illimitée') . "\n";
            $notes .= "Utilisateurs maximum: " . ($license->max_users ?? 'Illimité') . "\n";
            $notes .= "Utilisateurs actuels: " . ($license->current_users ?? 0) . "\n\n";
            $notes .= "Ce certificat atteste que le client mentionné ci-dessus dispose d'une licence valide pour utiliser le logiciel BatiStack selon les termes et conditions définies.";

            $certificatePdf->notes($cleanUtf8($notes));

            // Statut de la licence
            if ($license->isValid()) {
                $certificatePdf->status('VALIDE');
            } elseif ($license->isExpired()) {
                $certificatePdf->status('EXPIREE');
            } else {
                $certificatePdf->status($license->status->label());
            }

            return $certificatePdf->stream();

        } catch (\Exception $e) {
            // En cas d'erreur, retourner une page d'erreur
            abort(500, 'Erreur lors de la génération du certificat: ' . $e->getMessage());
        }
    }
}
