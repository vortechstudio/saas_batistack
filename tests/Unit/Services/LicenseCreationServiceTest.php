<?php

use App\Services\LicenseCreationService;
use App\Models\Customer;
use App\Models\Product;
use App\Models\License;
use App\Models\Module;
use App\Models\Option;
use App\Enums\LicenseStatus;

beforeEach(function () {
    $this->customer = Customer::factory()->create();
    $this->product = Product::factory()->create();
    $this->licenseService = new LicenseCreationService();
});

describe('LicenseCreationService', function () {
    test('can create license with modules and options', function () {
        $modules = Module::factory()->count(2)->create();
        $options = Option::factory()->count(2)->create();

        $licenseData = [
            'customer_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'domain' => 'test-domain.com',
            'modules' => $modules->pluck('id')->toArray(),
            'options' => $options->pluck('id')->toArray()
        ];

        $license = $this->licenseService->createLicense($licenseData);

        expect($license)->toBeInstanceOf(License::class)
            ->and($license->status)->toBe(LicenseStatus::ACTIVE)
            ->and($license->modules)->toHaveCount(2)
            ->and($license->options)->toHaveCount(2);
    });

    test('generates unique license key', function () {
        $licenseData1 = [
            'customer_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'domain' => 'test-domain-1.com'
        ];

        $licenseData2 = [
            'customer_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'domain' => 'test-domain-2.com'
        ];

        $license1 = $this->licenseService->createLicense($licenseData1);
        $license2 = $this->licenseService->createLicense($licenseData2);

        expect($license1->license_key)->not->toBe($license2->license_key);
    });
});
