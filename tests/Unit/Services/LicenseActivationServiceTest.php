<?php

use App\Services\LicenseActivationService;
use App\Models\License;
use App\Models\User;
use App\Models\Customer;
use App\Enums\LicenseStatus;
use App\Enums\CustomerStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->activationService = new LicenseActivationService();
    $this->user = User::factory()->create();
});

test('can activate license successfully', function () {
    $customer = Customer::factory()->create(['status' => CustomerStatus::ACTIVE]);
    $license = License::factory()->create([
        'customer_id' => $customer->id,
        'status' => LicenseStatus::PENDING,
        'expires_at' => now()->addYear(),
    ]);

    $result = $this->activationService->activate($license->id, $this->user);

    expect($result['success'])->toBeTrue()
        ->and($license->fresh()->status)->toBe(LicenseStatus::ACTIVE)
        ->and($license->fresh()->last_used_at)->not()->toBeNull();
});

test('cannot activate already active license', function () {
    $customer = Customer::factory()->create(['status' => CustomerStatus::ACTIVE]);
    $license = License::factory()->create([
        'customer_id' => $customer->id,
        'status' => LicenseStatus::ACTIVE,
    ]);

    expect(fn() => $this->activationService->activate($license->id, $this->user))
        ->toThrow(ValidationException::class);
});

test('cannot activate expired license', function () {
    $customer = Customer::factory()->create(['status' => CustomerStatus::ACTIVE]);
    $license = License::factory()->create([
        'customer_id' => $customer->id,
        'status' => LicenseStatus::PENDING,
        'expires_at' => now()->subDay(),
    ]);

    expect(fn() => $this->activationService->activate($license->id, $this->user))
        ->toThrow(ValidationException::class);
});

test('cannot activate license for inactive customer', function () {
    $customer = Customer::factory()->create(['status' => CustomerStatus::INACTIVE]);
    $license = License::factory()->create([
        'customer_id' => $customer->id,
        'status' => LicenseStatus::PENDING,
    ]);

    expect(fn() => $this->activationService->activate($license->id, $this->user))
        ->toThrow(ValidationException::class);
});

test('enforces rate limiting', function () {
    Cache::put('license_activation:' . $this->user->id, 5, now()->addHour());

    $customer = Customer::factory()->create(['status' => CustomerStatus::ACTIVE]);
    $license = License::factory()->create([
        'customer_id' => $customer->id,
        'status' => LicenseStatus::PENDING,
    ]);

    expect(fn() => $this->activationService->activate($license->id, $this->user))
        ->toThrow(ValidationException::class);
});

test('throws exception for non-existent license', function () {
    expect(fn() => $this->activationService->activate(999, $this->user))
        ->toThrow(ValidationException::class);
});
