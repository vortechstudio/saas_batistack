<?php

use App\Models\Customer;
use App\Models\User;
use App\Models\License;
use App\Enums\CustomerStatus;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->customer = Customer::factory()->create([
        'company_name' => 'Test Company',
        'contact_name' => 'John Doe',
        'email' => 'contact@testcompany.com',
        'status' => CustomerStatus::ACTIVE,
        'user_id' => $this->user->id,
    ]);
});

describe('Customer Model', function () {
    test('can create a customer', function () {
        expect($this->customer)->toBeInstanceOf(Customer::class)
            ->and($this->customer->company_name)->toBe('Test Company')
            ->and($this->customer->contact_name)->toBe('John Doe')
            ->and($this->customer->email)->toBe('contact@testcompany.com');
    });

    test('has correct fillable attributes', function () {
        $fillable = [
            'company_name', 'contact_name', 'email', 'phone', 'address',
            'city', 'postal_code', 'country', 'siret', 'vat_number',
            'status', 'stripe_customer_id', 'user_id'
        ];
        
        expect($this->customer->getFillable())->toBe($fillable);
    });

    test('casts status to CustomerStatus enum', function () {
        expect($this->customer->status)->toBeInstanceOf(CustomerStatus::class)
            ->and($this->customer->status)->toBe(CustomerStatus::ACTIVE);
    });

    test('belongs to a user', function () {
        expect($this->customer->user)->toBeInstanceOf(User::class)
            ->and($this->customer->user->id)->toBe($this->user->id);
    });

    test('can have many licenses', function () {
        $license1 = License::factory()->create(['customer_id' => $this->customer->id]);
        $license2 = License::factory()->create(['customer_id' => $this->customer->id]);
        
        expect($this->customer->licenses)->toHaveCount(2)
            ->and($this->customer->licenses->first())->toBeInstanceOf(License::class);
    });

    test('active scope filters active customers', function () {
        Customer::factory()->create(['status' => CustomerStatus::INACTIVE]);
        Customer::factory()->create(['status' => CustomerStatus::SUSPENDED]);
        
        $activeCustomers = Customer::active()->get();
        
        expect($activeCustomers)->toHaveCount(1)
            ->and($activeCustomers->first()->status)->toBe(CustomerStatus::ACTIVE);
    });

    test('isActive returns true for active customer', function () {
        expect($this->customer->isActive())->toBeTrue();
    });

    test('isActive returns false for inactive customer', function () {
        $inactiveCustomer = Customer::factory()->create(['status' => CustomerStatus::INACTIVE]);
        
        expect($inactiveCustomer->isActive())->toBeFalse();
    });

    test('getDisplayNameAttribute returns formatted name', function () {
        expect($this->customer->display_name)->toBe('Test Company (John Doe)');
    });
});
