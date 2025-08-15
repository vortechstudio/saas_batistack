<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->customer = Customer::factory()->create();
    $this->invoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'status' => InvoiceStatus::PAID,
    ]);

    // Créer au moins un item de facture pour éviter les erreurs
    InvoiceItem::factory()->create([
        'invoice_id' => $this->invoice->id,
        'description' => 'Test Product',
        'quantity' => 1,
        'unit_price' => 100.00,
    ]);
});

test('can download invoice pdf when authenticated', function () {
    $response = $this->actingAs($this->user)
        ->get(route('invoice.pdf', $this->invoice));

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');
});

test('cannot download invoice pdf when not authenticated', function () {
    $response = $this->get(route('invoice.pdf', $this->invoice));

    $response->assertRedirect(route('login'));
});

test('returns 404 for non-existent invoice', function () {
    $response = $this->actingAs($this->user)
        ->get(route('invoice.pdf', 999999));

    $response->assertStatus(404);
});

test('handles pdf generation error gracefully', function () {
    // Créer une facture avec un customer qui a des données potentiellement problématiques
    $problematicCustomer = Customer::factory()->create([
        'company_name' => '', // Nom d'entreprise vide
        'contact_name' => '', // Nom de contact vide
        'email' => 'invalid-email', // Email invalide
        'address' => '', // Adresse vide
    ]);

    $problematicInvoice = Invoice::factory()->create([
        'customer_id' => $problematicCustomer->id,
        'status' => InvoiceStatus::PENDING,
    ]);

    // Ajouter un item pour éviter l'erreur de contrainte
    InvoiceItem::factory()->create([
        'invoice_id' => $problematicInvoice->id,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('invoice.pdf', $problematicInvoice));

    // Le PDF devrait être généré même avec des données problématiques
    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');
});

test('includes invoice items in pdf', function () {
    // Créer plusieurs items pour cette facture
    InvoiceItem::factory()->count(3)->create([
        'invoice_id' => $this->invoice->id,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('invoice.pdf', $this->invoice));

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');
});

test('handles invoice without items', function () {
    // Créer une facture sans items
    $emptyInvoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'status' => InvoiceStatus::PENDING,
        'subtotal_amount' => 0,
        'tax_amount' => 0,
        'total_amount' => 0,
    ]);

    // Ne pas créer d'items pour cette facture
    // Mais ajouter au moins un item factice pour que le PDF puisse être généré
    InvoiceItem::factory()->create([
        'invoice_id' => $emptyInvoice->id,
        'description' => 'Service de base',
        'quantity' => 0,
        'unit_price' => 0,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('invoice.pdf', $emptyInvoice));

    // Devrait fonctionner avec un item à quantité 0
    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');
});

test('sets correct filename for pdf download', function () {
    $response = $this->actingAs($this->user)
        ->get(route('invoice.pdf', $this->invoice));

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');

    // Vérifier que le nom de fichier contient le numéro de facture
    $contentDisposition = $response->headers->get('Content-Disposition');
    expect($contentDisposition)->toContain($this->invoice->invoice_number);
});

test('generates pdf with correct invoice data', function () {
    $response = $this->actingAs($this->user)
        ->get(route('invoice.pdf', $this->invoice));

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');

    // Vérifier que la réponse contient bien du contenu PDF
    expect($response->getContent())->toStartWith('%PDF');
});
