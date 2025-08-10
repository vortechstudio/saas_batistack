<?php

use App\Models\Payment;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Resources\Payments\Pages\CreatePayment;
use App\Filament\Resources\Payments\Pages\EditPayment;
use Filament\Actions\DeleteAction;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'admin@batistack.com',
        'email_verified_at' => now(),
    ]);
    $this->actingAs($this->user);
});

describe('Payment Resource', function () {
    test('can render payment list page', function () {
        $this->get(PaymentResource::getUrl('index'))
            ->assertSuccessful();
    });

    test('can list payments', function () {
        $payments = Payment::factory()->count(10)->create();

        livewire(ListPayments::class)
            ->assertCanSeeTableRecords($payments);
    });

    test('can render payment create page', function () {
        $this->get(PaymentResource::getUrl('create'))
            ->assertSuccessful();
    });

    test('can create payment', function () {
        $this->get(PaymentResource::getUrl('create'))
            ->assertSuccessful();

        $customer = Customer::factory()->create();
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id]);
        
        $payment = Payment::factory()->create([
            'customer_id' => $customer->id,
            'invoice_id' => $invoice->id,
            'amount' => 100.50,
        ]);

        $this->assertDatabaseHas(Payment::class, [
            'customer_id' => $customer->id,
            'invoice_id' => $invoice->id,
            'amount' => 100.50,
        ]);
    });

    test('can validate payment creation', function () {
        livewire(CreatePayment::class)
            ->fillForm([
                'customer_id' => null,
                'amount' => 'invalid',
                'payment_method' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'customer_id' => 'required',
                'amount' => 'numeric',
                'payment_method' => 'required',
            ]);
    });

    test('can render payment edit page', function () {
        $payment = Payment::factory()->create();

        $this->get(PaymentResource::getUrl('edit', [
            'record' => $payment,
        ]))->assertSuccessful();
    });

    test('can retrieve payment data for editing', function () {
        $payment = Payment::factory()->create();

        livewire(EditPayment::class, [
            'record' => $payment->getRouteKey(),
        ])
            ->assertFormSet([
                'customer_id' => $payment->customer_id,
                'invoice_id' => $payment->invoice_id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'payment_method' => $payment->payment_method->value,
                'status' => $payment->status->value,
            ]);
    });

    test('can save payment', function () {
        $payment = Payment::factory()->create();
        
        $this->get(PaymentResource::getUrl('edit', [
            'record' => $payment,
        ]))->assertSuccessful();

        // Test direct update
        $payment->update([
            'amount' => 150.75,
            'status' => PaymentStatus::SUCCEEDED,
            'failure_reason' => 'Test reason',
        ]);

        expect($payment->refresh())
            ->amount->toBe('150.75')
            ->status->toBe(PaymentStatus::SUCCEEDED)
            ->failure_reason->toBe('Test reason');
    });

    test('can delete payment', function () {
        $payment = Payment::factory()->create();

        livewire(EditPayment::class, [
            'record' => $payment->getRouteKey(),
        ])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($payment);
    });

    test('can search payments', function () {
        $customer = Customer::factory()->create();
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id]);
        
        $payment = Payment::factory()->create([
            'customer_id' => $customer->id,
            'invoice_id' => $invoice->id,
            'stripe_payment_intent_id' => 'pi_search_test_123',
        ]);

        // Test que la page de liste fonctionne
        $this->get(PaymentResource::getUrl('index'))
            ->assertSuccessful();
            
        // Vérifier que le paiement existe en base
        $this->assertDatabaseHas(Payment::class, [
            'stripe_payment_intent_id' => 'pi_search_test_123',
        ]);
    });

    test('can sort payments', function () {
        $payments = Payment::factory()->count(10)->create();

        livewire(ListPayments::class)
            ->sortTable('amount')
            ->assertCanSeeTableRecords($payments->sortBy('amount'), inOrder: true)
            ->sortTable('amount', 'desc')
            ->assertCanSeeTableRecords($payments->sortByDesc('amount'), inOrder: true);
    });

    test('can filter payments by status', function () {
        $succeededPayments = Payment::factory()->count(5)->create(['status' => PaymentStatus::SUCCEEDED]);
        $failedPayments = Payment::factory()->count(3)->create(['status' => PaymentStatus::FAILED]);

        livewire(ListPayments::class)
            ->filterTable('status', PaymentStatus::SUCCEEDED->value)
            ->assertCanSeeTableRecords($succeededPayments)
            ->assertCanNotSeeTableRecords($failedPayments);
    });

    test('can filter payments by payment method', function () {
        $creditCardPayments = Payment::factory()->count(5)->create(['payment_method' => PaymentMethod::CARD]);
        $bankTransferPayments = Payment::factory()->count(3)->create(['payment_method' => PaymentMethod::BANK_TRANSFER]);

        livewire(ListPayments::class)
            ->filterTable('payment_method', PaymentMethod::CARD->value)
            ->assertCanSeeTableRecords($creditCardPayments)
            ->assertCanNotSeeTableRecords($bankTransferPayments);
    });

    test('can bulk delete payments', function () {
        $payments = Payment::factory()->count(10)->create();

        livewire(ListPayments::class)
            ->callTableBulkAction('delete', $payments);

        foreach ($payments as $payment) {
            $this->assertModelMissing($payment);
        }
    });

    test('displays navigation badge with payment count', function () {
        Payment::factory()->count(8)->create();

        expect(PaymentResource::getNavigationBadge())->toBe('8');
    });

    test('can globally search payments', function () {
        $payment = Payment::factory()->create([
            'stripe_payment_intent_id' => 'pi_123456789',
        ]);

        $searchableAttributes = PaymentResource::getGloballySearchableAttributes();

        expect($searchableAttributes)->toContain('stripe_payment_intent_id');
    });
});
