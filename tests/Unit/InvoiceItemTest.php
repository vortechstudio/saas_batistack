<?php

use App\Models\InvoiceItem;
use App\Models\Invoice;
use App\Models\Customer;

beforeEach(function () {
    $this->customer = Customer::factory()->create();
    $this->invoice = Invoice::factory()->create(['customer_id' => $this->customer->id]);
    $this->invoiceItem = InvoiceItem::factory()->create([
        'invoice_id' => $this->invoice->id,
        'description' => 'Test Product',
        'quantity' => 2,
        'unit_price' => 50.00,
        'total_price' => 100.00,
    ]);
});

describe('InvoiceItem Model', function () {
    test('can create an invoice item', function () {
        expect($this->invoiceItem)->toBeInstanceOf(InvoiceItem::class)
            ->and($this->invoiceItem->description)->toBe('Test Product')
            ->and($this->invoiceItem->quantity)->toBe(2)
            ->and($this->invoiceItem->unit_price)->toBe('50.00')
            ->and($this->invoiceItem->total_price)->toBe('100.00');
    });

    test('has correct fillable attributes', function () {
        $fillable = [
            'invoice_id',
            'product_id',
            'license_id',
            'description',
            'quantity',
            'unit_price',
            'total_price',
            'tax_rate',
            'tax_amount',
        ];

        expect($this->invoiceItem->getFillable())->toBe($fillable);
    });

    test('casts attributes correctly', function () {
        expect($this->invoiceItem->quantity)->toBe(2)
            ->and($this->invoiceItem->unit_price)->toBe('50.00')
            ->and($this->invoiceItem->total_price)->toBe('100.00');
    });

    test('belongs to an invoice', function () {
        expect($this->invoiceItem->invoice)->toBeInstanceOf(Invoice::class)
            ->and($this->invoiceItem->invoice->id)->toBe($this->invoice->id);
    });

    test('calculates total price correctly', function () {
        $item = InvoiceItem::factory()->create([
            'quantity' => 3,
            'unit_price' => 25.50,
        ]);

        expect($item->quantity * $item->unit_price)->toBe(76.5);
    });

    test('getFormattedUnitPriceAttribute returns formatted price', function () {
        expect($this->invoiceItem->formatted_unit_price)->toBe('50.00 €');
    });

    test('getFormattedTotalPriceAttribute returns formatted total', function () {
        expect($this->invoiceItem->formatted_total_price)->toBe('100.00 €');
    });
});
