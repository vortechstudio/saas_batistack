<?php

use App\Models\User;
use App\Models\Customer;

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

describe('User Model', function () {
    test('can create a user', function () {
        expect($this->user)->toBeInstanceOf(User::class)
            ->and($this->user->name)->toBe('John Doe')
            ->and($this->user->email)->toBe('john@example.com');
    });

    test('has fillable attributes', function () {
        $fillable = ['name', 'email', 'password'];
        expect($this->user->getFillable())->toBe($fillable);
    });

    test('has hidden attributes', function () {
        $hidden = ['password', 'remember_token'];
        expect($this->user->getHidden())->toBe($hidden);
    });

    test('casts email_verified_at to datetime', function () {
        expect($this->user->getCasts())->toHaveKey('email_verified_at', 'datetime');
    });

    test('casts password to hashed', function () {
        expect($this->user->getCasts())->toHaveKey('password', 'hashed');
    });

    test('can have a customer relationship', function () {
        $customer = Customer::factory()->create(['user_id' => $this->user->id]);

        expect($this->user->customer)->toBeInstanceOf(Customer::class)
            ->and($this->user->customer->id)->toBe($customer->id);
    });

    test('hasCustomer returns true when user has a customer', function () {
        Customer::factory()->create(['user_id' => $this->user->id]);

        expect($this->user->hasCustomer())->toBeTrue();
    });

    test('hasCustomer returns false when user has no customer', function () {
        expect($this->user->hasCustomer())->toBeFalse();
    });

    test('generates correct initials for single name', function () {
        $user = User::factory()->create(['name' => 'John']);

        expect($user->initials())->toBe('J');
    });

    test('generates correct initials for full name', function () {
        $user = User::factory()->create(['name' => 'John Doe']);

        expect($user->initials())->toBe('JD');
    });

    test('generates correct initials for multiple names', function () {
        $user = User::factory()->create(['name' => 'John Michael Doe']);

        expect($user->initials())->toBe('JM');
    });
});
