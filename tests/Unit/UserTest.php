<?php

use App\Models\User;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User Model', function () {
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

        $user = new User();
        expect($user->getFillable())->toBe($fillable);
    });

    test('has hidden attributes', function () {
        $hidden = [
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ];

        $user = new User();
        expect($user->getHidden())->toBe($hidden);
    });

    test('casts attributes correctly', function () {
        $user = new User();
        $casts = $user->getCasts();

        expect($casts['email_verified_at'])->toBe('datetime');
        expect($casts['password'])->toBe('hashed');
        expect($casts['two_factor_enabled'])->toBe('boolean');
        expect($casts['last_login_at'])->toBe('datetime');
        expect($casts['locked_until'])->toBe('datetime');
    });

    test('has customer relationship', function () {
        $user = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        expect($user->customer)->toBeInstanceOf(Customer::class);
        expect($user->customer->id)->toBe($customer->id);
    });

    test('hasCustomer method works correctly', function () {
        $userWithCustomer = User::factory()->create();
        Customer::factory()->create(['user_id' => $userWithCustomer->id]);

        $userWithoutCustomer = User::factory()->create();

        expect($userWithCustomer->hasCustomer())->toBeTrue();
        expect($userWithoutCustomer->hasCustomer())->toBeFalse();
    });

    test('generates initials correctly', function () {
        $user1 = User::factory()->make(['name' => 'John Doe']);
        $user2 = User::factory()->make(['name' => 'Jane']);
        $user3 = User::factory()->make(['name' => 'Jean Pierre Martin']);

        expect($user1->initials())->toBe('JD');
        expect($user2->initials())->toBe('J');
        expect($user3->initials())->toBe('JP'); // Only first two words
    });

    test('isAdmin method works correctly', function () {
        $admin = User::factory()->make(['email' => 'admin@batistack.com']);
        $user = User::factory()->make(['email' => 'user@example.com']);

        expect($admin->isAdmin())->toBeTrue();
        expect($user->isAdmin())->toBeFalse();
    });

    test('hasTwoFactorEnabled method works correctly', function () {
        $userWith2FA = User::factory()->make([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'secret'
        ]);

        $userWithout2FA = User::factory()->make([
            'two_factor_enabled' => false,
            'two_factor_secret' => null
        ]);

        expect($userWith2FA->hasTwoFactorEnabled())->toBeTrue();
        expect($userWithout2FA->hasTwoFactorEnabled())->toBeFalse();
    });

    test('isLocked method works correctly', function () {
        $lockedUser = User::factory()->create([
            'locked_until' => now()->addMinutes(10)
        ]);

        $unlockedUser = User::factory()->create([
            'locked_until' => now()->subMinutes(10)
        ]);

        expect($lockedUser->isLocked())->toBeTrue();
        expect($unlockedUser->isLocked())->toBeFalse();
    });

    test('lockUser method works correctly', function () {
        $user = User::factory()->create(['failed_login_attempts' => 3]);

        $user->lockUser(15);

        expect($user->fresh()->isLocked())->toBeTrue();
        expect($user->fresh()->failed_login_attempts)->toBe(0);
        expect($user->fresh()->locked_until)->not->toBeNull();
    });

    test('unlockUser method works correctly', function () {
        $user = User::factory()->create([
            'locked_until' => now()->addMinutes(10),
            'failed_login_attempts' => 5
        ]);

        $user->unlockUser();

        expect($user->fresh()->isLocked())->toBeFalse();
        expect($user->fresh()->failed_login_attempts)->toBe(0);
        expect($user->fresh()->locked_until)->toBeNull();
    });

    test('incrementFailedAttempts locks user after 5 attempts', function () {
        $user = User::factory()->create(['failed_login_attempts' => 4]);

        $user->incrementFailedAttempts();

        expect($user->fresh()->isLocked())->toBeTrue();
        expect($user->fresh()->failed_login_attempts)->toBe(0);
    });

    test('incrementFailedAttempts increments counter when under limit', function () {
        $user = User::factory()->create(['failed_login_attempts' => 2]);

        $user->incrementFailedAttempts();

        expect($user->fresh()->failed_login_attempts)->toBe(3);
        expect($user->fresh()->isLocked())->toBeFalse();
    });
});
