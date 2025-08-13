<?php

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;

beforeEach(function () {
    $this->customer = Customer::factory()->create();
    $this->invoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'invoice_number' => 'INV-202401-0001',
        'status' => InvoiceStatus::PENDING,
        'subtotal_amount' => 100.00,
        'tax_amount' => 20.00,
        'total_amount' => 120.00,
        'currency' => 'eur',
        'due_date' => now()->addDays(30),
    ]);
});

describe('Invoice Model', function () {
    test('can create an invoice', function () {
        expect($this->invoice)->toBeInstanceOf(Invoice::class)
            ->and($this->invoice->invoice_number)->toBe('INV-202401-0001')
            ->and($this->invoice->total_amount)->toBe('120.00')
            ->and($this->invoice->currency)->toBe('eur');
    });

    test('has correct fillable attributes', function () {
        $fillable = [
            'customer_id', 'invoice_number', 'stripe_invoice_id', 'status',
            'subtotal_amount', 'tax_amount', 'total_amount', 'currency',
            'due_date', 'paid_at', 'description', 'notes', 'metadata'
        ];

        expect($this->invoice->getFillable())->toBe($fillable);
    });

    test('casts attributes correctly', function () {
        expect($this->invoice->status)->toBeInstanceOf(InvoiceStatus::class)
            ->and($this->invoice->due_date)->toBeInstanceOf(\Carbon\Carbon::class)
            ->and($this->invoice->subtotal_amount)->toBe('100.00')
            ->and($this->invoice->tax_amount)->toBe('20.00')
            ->and($this->invoice->total_amount)->toBe('120.00');
    });

    test('belongs to a customer', function () {
        expect($this->invoice->customer)->toBeInstanceOf(Customer::class)
            ->and($this->invoice->customer->id)->toBe($this->customer->id);
    });

    test('can have many invoice items', function () {
        InvoiceItem::factory()->count(3)->create(['invoice_id' => $this->invoice->id]);

        expect($this->invoice->invoiceItems)->toHaveCount(3)
            ->and($this->invoice->invoiceItems->first())->toBeInstanceOf(InvoiceItem::class);
    });

    test('can have many payments', function () {
        Payment::factory()->count(2)->create(['invoice_id' => $this->invoice->id]);

        expect($this->invoice->payments)->toHaveCount(2)
            ->and($this->invoice->payments->first())->toBeInstanceOf(Payment::class);
    });

    test('has one successful payment', function () {
        Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'status' => PaymentStatus::SUCCEEDED
        ]);
        Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'status' => PaymentStatus::FAILED
        ]);

        expect($this->invoice->payment)->toBeInstanceOf(Payment::class)
            ->and($this->invoice->payment->status)->toBe(PaymentStatus::SUCCEEDED);
    });

    test('overdue scope filters overdue invoices', function () {
        Invoice::factory()->create([
            'due_date' => now()->subDays(5),
            'status' => InvoiceStatus::PENDING
        ]);
        Invoice::factory()->create([
            'due_date' => now()->addDays(5),
            'status' => InvoiceStatus::PENDING
        ]);

        $overdueInvoices = Invoice::overdue()->get();

        expect($overdueInvoices)->toHaveCount(1);
    });

    test('paid scope filters paid invoices', function () {
        Invoice::factory()->create(['status' => InvoiceStatus::PAID]);
        Invoice::factory()->create(['status' => InvoiceStatus::PENDING]);

        $paidInvoices = Invoice::paid()->get();

        expect($paidInvoices)->toHaveCount(1)
            ->and($paidInvoices->first()->status)->toBe(InvoiceStatus::PAID);
    });

    test('pending scope filters pending invoices', function () {
        Invoice::factory()->create(['status' => InvoiceStatus::PAID]);

        $pendingInvoices = Invoice::pending()->get();

        expect($pendingInvoices)->toHaveCount(1)
            ->and($pendingInvoices->first()->status)->toBe(InvoiceStatus::PENDING);
    });

    test('isOverdue returns true for overdue invoice', function () {
        $overdueInvoice = Invoice::factory()->create([
            'due_date' => now()->subDays(5),
            'status' => InvoiceStatus::PENDING
        ]);

        expect($overdueInvoice->isOverdue())->toBeTrue()
            ->and($this->invoice->isOverdue())->toBeFalse();
    });

    test('isPaid returns correct boolean', function () {
        expect($this->invoice->isPaid())->toBeFalse();

        $paidInvoice = Invoice::factory()->create(['status' => InvoiceStatus::PAID]);
        expect($paidInvoice->isPaid())->toBeTrue();
    });

    test('markAsPaid updates status and paid_at', function () {
        $this->invoice->markAsPaid();

        expect($this->invoice->fresh()->status)->toBe(InvoiceStatus::PAID)
            ->and($this->invoice->fresh()->paid_at)->not->toBeNull();
    });

    test('markAsOverdue updates status', function () {
        $this->invoice->markAsOverdue();

        expect($this->invoice->fresh()->status)->toBe(InvoiceStatus::OVERDUE);
    });

    test('generateInvoiceNumber creates unique number', function () {
        $number = Invoice::generateInvoiceNumber();

        expect($number)->toMatch('/^INV-\d{4}\d{2}-\d{4}$/');
    });

    test('getFormattedTotalAttribute returns formatted amount', function () {
        expect($this->invoice->formatted_total)->toBe('120.00 EUR');
    });

    test('getStatusLabelAttribute returns status label', function () {
        expect($this->invoice->status_label)->toBe($this->invoice->status->label());
    });

    test('getStatusColorAttribute returns status color', function () {
        expect($this->invoice->status_color)->toBe($this->invoice->status->color());
    });

    test('getStatusBadgeClass returns correct CSS class', function () {
        // Test pour facture en attente
        expect($this->invoice->getStatusBadgeClass())->toBe('badge-warning');

        // Test pour facture payée
        $paidInvoice = Invoice::factory()->create(['status' => InvoiceStatus::PAID]);
        expect($paidInvoice->getStatusBadgeClass())->toBe('badge-success');

        // Test pour facture en retard
        $overdueInvoice = Invoice::factory()->create(['status' => InvoiceStatus::OVERDUE]);
        expect($overdueInvoice->getStatusBadgeClass())->toBe('badge-error');
    });
});
