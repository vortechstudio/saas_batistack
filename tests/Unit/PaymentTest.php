<?php

use App\Models\Payment;
use App\Models\Customer;
use App\Models\Invoice;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;
use App\Enums\InvoiceStatus;

beforeEach(function () {
    $this->customer = Customer::factory()->create();
    $this->invoice = Invoice::factory()->create(['customer_id' => $this->customer->id]);
    $this->payment = Payment::factory()->create([
        'customer_id' => $this->customer->id,
        'invoice_id' => $this->invoice->id,
        'amount' => 120.00,
        'currency' => 'eur',
        'status' => PaymentStatus::SUCCEEDED,
        'payment_method' => PaymentMethod::CARD,
    ]);
});

describe('Payment Model', function () {
    test('can create a payment', function () {
        expect($this->payment)->toBeInstanceOf(Payment::class)
            ->and($this->payment->amount)->toBe('120.00')
            ->and($this->payment->currency)->toBe('eur')
            ->and($this->payment->status)->toBe(PaymentStatus::SUCCEEDED);
    });

    test('has correct fillable attributes', function () {
        $fillable = [
            'customer_id', 'invoice_id', 'stripe_payment_intent_id', 'stripe_charge_id',
            'amount', 'currency', 'status', 'payment_method', 'payment_method_details',
            'failure_reason', 'processed_at', 'refunded_at', 'refund_amount', 'metadata'
        ];
        
        expect($this->payment->getFillable())->toBe($fillable);
    });

    test('casts attributes correctly', function () {
        expect($this->payment->amount)->toBe('120.00')
            ->and($this->payment->status)->toBeInstanceOf(PaymentStatus::class)
            ->and($this->payment->payment_method)->toBeInstanceOf(PaymentMethod::class);
    });

    test('belongs to a customer', function () {
        expect($this->payment->customer)->toBeInstanceOf(Customer::class)
            ->and($this->payment->customer->id)->toBe($this->customer->id);
    });

    test('belongs to an invoice', function () {
        expect($this->payment->invoice)->toBeInstanceOf(Invoice::class)
            ->and($this->payment->invoice->id)->toBe($this->invoice->id);
    });

    test('succeeded scope filters successful payments', function () {
        Payment::factory()->create(['status' => PaymentStatus::FAILED]);
        
        $succeededPayments = Payment::succeeded()->get();
        
        expect($succeededPayments)->toHaveCount(1)
            ->and($succeededPayments->first()->status)->toBe(PaymentStatus::SUCCEEDED);
    });

    test('failed scope filters failed payments', function () {
        Payment::factory()->create(['status' => PaymentStatus::FAILED]);
        
        $failedPayments = Payment::failed()->get();
        
        expect($failedPayments)->toHaveCount(1)
            ->and($failedPayments->first()->status)->toBe(PaymentStatus::FAILED);
    });

    test('pending scope filters pending payments', function () {
        Payment::factory()->create(['status' => PaymentStatus::PENDING]);
        
        $pendingPayments = Payment::pending()->get();
        
        expect($pendingPayments)->toHaveCount(1)
            ->and($pendingPayments->first()->status)->toBe(PaymentStatus::PENDING);
    });

    test('isSucceeded returns correct boolean', function () {
        expect($this->payment->isSucceeded())->toBeTrue();
        
        $failedPayment = Payment::factory()->create(['status' => PaymentStatus::FAILED]);
        expect($failedPayment->isSucceeded())->toBeFalse();
    });

    test('isFailed returns correct boolean', function () {
        expect($this->payment->isFailed())->toBeFalse();
        
        $failedPayment = Payment::factory()->create(['status' => PaymentStatus::FAILED]);
        expect($failedPayment->isFailed())->toBeTrue();
    });

    test('isPending returns correct boolean', function () {
        expect($this->payment->isPending())->toBeFalse();
        
        $pendingPayment = Payment::factory()->create(['status' => PaymentStatus::PENDING]);
        expect($pendingPayment->isPending())->toBeTrue();
    });

    test('isRefunded returns correct boolean', function () {
        expect($this->payment->isRefunded())->toBeFalse();
        
        $refundedPayment = Payment::factory()->create(['status' => PaymentStatus::REFUNDED]);
        expect($refundedPayment->isRefunded())->toBeTrue();
        
        $partiallyRefundedPayment = Payment::factory()->create(['status' => PaymentStatus::PARTIALLY_REFUNDED]);
        expect($partiallyRefundedPayment->isRefunded())->toBeTrue();
    });

    test('markAsSucceeded updates status and processed_at', function () {
        $pendingPayment = Payment::factory()->create([
            'status' => PaymentStatus::PENDING,
            'invoice_id' => $this->invoice->id
        ]);
        
        $pendingPayment->markAsSucceeded();
        
        expect($pendingPayment->fresh()->status)->toBe(PaymentStatus::SUCCEEDED)
            ->and($pendingPayment->fresh()->processed_at)->not->toBeNull()
            ->and($pendingPayment->invoice->fresh()->status)->toBe(InvoiceStatus::PAID);
    });

    test('markAsFailed updates status and failure reason', function () {
        $pendingPayment = Payment::factory()->create(['status' => PaymentStatus::PENDING]);
        
        $pendingPayment->markAsFailed('Card declined');
        
        expect($pendingPayment->fresh()->status)->toBe(PaymentStatus::FAILED)
            ->and($pendingPayment->fresh()->failure_reason)->toBe('Card declined');
    });

    test('getFormattedAmountAttribute returns formatted amount', function () {
        expect($this->payment->formatted_amount)->toBe('120.00 EUR');
    });

    test('getStatusLabelAttribute returns status label', function () {
        expect($this->payment->status_label)->toBe($this->payment->status->label());
    });

    test('getStatusColorAttribute returns status color', function () {
        expect($this->payment->status_color)->toBe($this->payment->status->color());
    });

    test('getPaymentMethodLabelAttribute returns payment method label', function () {
        expect($this->payment->payment_method_label)->toBe($this->payment->payment_method->label());
    });
});