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
        $fillable = [
            'name',
            'email',
            'password',
            'two_factor_enabled',
            'last_login_at',
            'last_login_ip',
            'failed_login_attempts',
            'locked_until',
        ];
        expect($this->user->getFillable())->toBe($fillable);
    });

    test('has hidden attributes', function () {
        $hidden = [
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ];
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

    test('can check if user is admin', function () {
        $adminUser = User::factory()->create(['email' => 'admin@batistack.com']);
        $regularUser = User::factory()->create(['email' => 'user@example.com']);

        expect($adminUser->isAdmin())->toBeTrue()
            ->and($regularUser->isAdmin())->toBeFalse();
    });

    test('can check if two factor is enabled', function () {
        $userWith2FA = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'secret123'
        ]);
        $userWithout2FA = User::factory()->create([
            'two_factor_enabled' => false,
            'two_factor_secret' => null
        ]);

        expect($userWith2FA->hasTwoFactorEnabled())->toBeTrue()
            ->and($userWithout2FA->hasTwoFactorEnabled())->toBeFalse();
    });

    test('can check if user is locked', function () {
        $lockedUser = User::factory()->create([
            'locked_until' => now()->addMinutes(30)
        ]);
        $unlockedUser = User::factory()->create([
            'locked_until' => null
        ]);

        expect($lockedUser->isLocked())->toBeTrue()
            ->and($unlockedUser->isLocked())->toBeFalse();
    });

    test('can lock and unlock user', function () {
        $user = User::factory()->create();

        // Test lock
        $user->lockUser(15);
        expect($user->fresh()->isLocked())->toBeTrue()
            ->and($user->fresh()->failed_login_attempts)->toBe(0);

        // Test unlock
        $user->unlockUser();
        expect($user->fresh()->isLocked())->toBeFalse()
            ->and($user->fresh()->locked_until)->toBeNull();
    });

    test('can increment failed login attempts', function () {
        $user = User::factory()->create(['failed_login_attempts' => 0]);

        $user->incrementFailedAttempts();
        expect($user->fresh()->failed_login_attempts)->toBe(1);

        // Test auto-lock after 5 attempts
        $user->update(['failed_login_attempts' => 4]);
        $user->incrementFailedAttempts();
        expect($user->fresh()->isLocked())->toBeTrue();
    });

    test('casts two_factor_enabled to boolean', function () {
        expect($this->user->getCasts())->toHaveKey('two_factor_enabled', 'boolean');
    });

    test('casts last_login_at to datetime', function () {
        expect($this->user->getCasts())->toHaveKey('last_login_at', 'datetime');
    });
});
