<?php

use App\Models\License;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Module;
use App\Models\Option;
use App\Enums\LicenseStatus;

beforeEach(function () {
    $this->customer = Customer::factory()->create();
    $this->product = Product::factory()->create();
    $this->license = License::factory()->create([
        'customer_id' => $this->customer->id,
        'product_id' => $this->product->id,
        'status' => LicenseStatus::ACTIVE,
        'starts_at' => now()->subDays(30),
        'expires_at' => now()->addDays(30),
        'max_users' => 5,
        'current_users' => 2,
    ]);
});

describe('License Model', function () {
    test('can create a license', function () {
        expect($this->license)->toBeInstanceOf(License::class)
            ->and($this->license->status)->toBe(LicenseStatus::ACTIVE)
            ->and($this->license->max_users)->toBe(5)
            ->and($this->license->current_users)->toBe(2)
            ->and($this->license->domain)->not->toBeNull();
    });

    test('has correct fillable attributes', function () {
        $fillable = [
            'customer_id', 'product_id', 'license_key', 'domain', 'status',
            'starts_at', 'expires_at', 'max_users', 'current_users', 'last_used_at'
        ];

        expect($this->license->getFillable())->toBe($fillable);
    });

    test('casts attributes correctly', function () {
        expect($this->license->status)->toBeInstanceOf(LicenseStatus::class)
            ->and($this->license->starts_at)->toBeInstanceOf(Carbon\Carbon::class)
            ->and($this->license->expires_at)->toBeInstanceOf(Carbon\Carbon::class);
    });

    test('generates license key automatically on creation', function () {
        $newLicense = License::factory()->create();

        expect($newLicense->license_key)->not->toBeNull()
            ->and($newLicense->license_key)->toStartWith('LIC-');
    });

    test('generates domain automatically on creation', function () {
        $newLicense = License::factory()->create();

        expect($newLicense->domain)->not->toBeNull()
            ->and($newLicense->domain)->toBeString();
    });

    test('generates unique domain for each license', function () {
        $license1 = License::factory()->create();
        $license2 = License::factory()->create();

        expect($license1->domain)->not->toBe($license2->domain);
    });

    test('can create license with specific domain', function () {
        $customDomain = 'custom-test-domain';
        $license = License::factory()->withDomain($customDomain)->create();

        expect($license->domain)->toBe($customDomain);
    });

    test('domain must be unique', function () {
        $domain = 'unique-test-domain';
        License::factory()->withDomain($domain)->create();

        expect(function () use ($domain) {
            License::factory()->withDomain($domain)->create();
        })->toThrow(Exception::class);
    });

    test('belongs to customer', function () {
        expect($this->license->customer)->toBeInstanceOf(Customer::class)
            ->and($this->license->customer->id)->toBe($this->customer->id);
    });

    test('belongs to product', function () {
        expect($this->license->product)->toBeInstanceOf(Product::class)
            ->and($this->license->product->id)->toBe($this->product->id);
    });

    test('can have many modules with pivot data', function () {
        $module = Module::factory()->create();

        $this->license->modules()->attach($module->id, [
            'enabled' => true,
            'expires_at' => now()->addDays(30)
        ]);

        expect($this->license->modules)->toHaveCount(1)
            ->and($this->license->modules->first()->pivot->enabled)->toBe(1); // Base de données stocke comme entier
    });

    test('can have many options with pivot data', function () {
        $option = Option::factory()->create();

        $this->license->options()->attach($option->id, [
            'enabled' => true,
            'expires_at' => now()->addDays(30)
        ]);

        expect($this->license->options)->toHaveCount(1)
            ->and($this->license->options->first()->pivot->enabled)->toBe(1); // Base de données stocke comme entier
    });

    test('activeModules returns only enabled and non-expired modules', function () {
        $activeModule = Module::factory()->create();
        $expiredModule = Module::factory()->create();
        $disabledModule = Module::factory()->create();

        $this->license->modules()->attach([
            $activeModule->id => ['enabled' => true, 'expires_at' => now()->addDays(30)],
            $expiredModule->id => ['enabled' => true, 'expires_at' => now()->subDays(1)],
            $disabledModule->id => ['enabled' => false, 'expires_at' => now()->addDays(30)]
        ]);

        expect($this->license->activeModules)->toHaveCount(1)
            ->and($this->license->activeModules->first()->id)->toBe($activeModule->id);
    });

    test('activeOptions returns only enabled and non-expired options', function () {
        $activeOption = Option::factory()->create();
        $expiredOption = Option::factory()->create();

        $this->license->options()->attach([
            $activeOption->id => ['enabled' => true, 'expires_at' => now()->addDays(30)],
            $expiredOption->id => ['enabled' => true, 'expires_at' => now()->subDays(1)]
        ]);

        expect($this->license->activeOptions)->toHaveCount(1)
            ->and($this->license->activeOptions->first()->id)->toBe($activeOption->id);
    });

    test('active scope filters active licenses', function () {
        License::factory()->create(['status' => LicenseStatus::EXPIRED]);

        $activeLicenses = License::active()->get();

        expect($activeLicenses)->toHaveCount(1)
            ->and($activeLicenses->first()->status)->toBe(LicenseStatus::ACTIVE);
    });

    test('expired scope filters expired licenses', function () {
        License::factory()->create(['expires_at' => now()->subDays(1)]);

        $expiredLicenses = License::expired()->get();

        expect($expiredLicenses)->toHaveCount(1);
    });

    test('valid scope filters valid licenses', function () {
        License::factory()->create(['status' => LicenseStatus::EXPIRED]);
        License::factory()->create(['expires_at' => now()->subDays(1)]);

        $validLicenses = License::valid()->get();

        expect($validLicenses)->toHaveCount(1)
            ->and($validLicenses->first()->id)->toBe($this->license->id);
    });

    test('isValid returns true for active non-expired license', function () {
        expect($this->license->isValid())->toBeTrue();
    });

    test('isValid returns false for expired license', function () {
        $expiredLicense = License::factory()->create([
            'status' => LicenseStatus::ACTIVE,
            'expires_at' => now()->subDays(1)
        ]);

        expect($expiredLicense->isValid())->toBeFalse();
    });

    test('isValid returns false for inactive license', function () {
        $inactiveLicense = License::factory()->create([
            'status' => LicenseStatus::SUSPENDED,
            'expires_at' => now()->addDays(30)
        ]);

        expect($inactiveLicense->isValid())->toBeFalse();
    });

    test('isExpired returns true for expired license', function () {
        $expiredLicense = License::factory()->create(['expires_at' => now()->subDays(1)]);

        expect($expiredLicense->isExpired())->toBeTrue();
    });

    test('isExpired returns false for valid license', function () {
        expect($this->license->isExpired())->toBeFalse();
    });

    test('hasModule returns true when module is active', function () {
        $module = Module::factory()->create(['key' => 'test-module']);

        $this->license->modules()->attach($module->id, [
            'enabled' => true,
            'expires_at' => now()->addDays(30)
        ]);

        expect($this->license->hasModule('test-module'))->toBeTrue();
    });

    test('hasModule returns false when module is not active', function () {
        $module = Module::factory()->create(['key' => 'test-module']);

        $this->license->modules()->attach($module->id, [
            'enabled' => false,
            'expires_at' => now()->addDays(30)
        ]);

        expect($this->license->hasModule('test-module'))->toBeFalse();
    });

    test('hasOption returns true when option is active', function () {
        $option = Option::factory()->create(['key' => 'test-option']);

        $this->license->options()->attach($option->id, [
            'enabled' => true,
            'expires_at' => now()->addDays(30)
        ]);

        expect($this->license->hasOption('test-option'))->toBeTrue();
    });

    test('enableModule activates a module for the license', function () {
        $module = Module::factory()->create();

        $this->license->enableModule($module->id, now()->addDays(30));

        expect($this->license->modules()->wherePivot('enabled', true)->count())->toBe(1);
    });

    test('disableModule deactivates a module for the license', function () {
        $module = Module::factory()->create();

        $this->license->modules()->attach($module->id, ['enabled' => true]);
        $this->license->disableModule($module->id);

        $this->license->refresh();

        expect($this->license->modules()->wherePivot('enabled', false)->count())->toBe(1);
    });

    // Tests pour les nouvelles fonctionnalités de domaine
    test('generateDomain creates unique domain based on customer and product', function () {
        $customer = Customer::factory()->create(['company_name' => 'Test Customer']);
        $product = Product::factory()->create(['name' => 'Test Product']);

        // Créer une licence temporaire pour tester la génération de domaine
        $license = License::factory()->make([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        // Charger les relations
        $license->load(['customer', 'product']);

        $domain = License::generateDomain($license);

        expect($domain)->toContain('test-customer-test-product');
    });

    test('generateDomain handles duplicate domains by adding counter', function () {
        $customer = Customer::factory()->create(['company_name' => 'Duplicate Test']);
        $product = Product::factory()->create(['name' => 'Product']);

        // Créer une première licence avec ce domaine
        $firstLicense = License::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'domain' => 'duplicate-test-product'
        ]);

        // Créer une licence temporaire pour tester la génération de domaine
        $license = License::factory()->make([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        // Charger les relations
        $license->load(['customer', 'product']);

        $domain = License::generateDomain($license);

        expect($domain)->toBe('duplicate-test-product-1');
    });

    test('getServiceUrl returns correct URL format', function () {
        config(['app.service_domain' => 'example.com']);

        $license = License::factory()->withDomain('test-domain')->create();

        $serviceUrl = $license->getServiceUrl();

        expect($serviceUrl)->toBe('https://test-domain.example.com');
    });

    test('getServiceUrl uses default domain when config not set', function () {
        config(['app.service_domain' => 'batistack.com']);

        $license = License::factory()->withDomain('test-domain')->create();

        $serviceUrl = $license->getServiceUrl();

        expect($serviceUrl)->toBe('https://test-domain.batistack.com');
    });

    test('hasDomain returns true when domain is set', function () {
        $license = License::factory()->withDomain('test-domain')->create();

        expect($license->hasDomain())->toBeTrue();
    });

    test('findByKey returns license with matching key', function () {
        $licenseKey = 'TEST-KEY-1234';
        $license = License::factory()->create(['license_key' => $licenseKey]);

        $foundLicense = License::findByKey($licenseKey);

        expect($foundLicense)->not->toBeNull()
            ->and($foundLicense->id)->toBe($license->id);
    });

    test('findByKey returns null when no license found', function () {
        $foundLicense = License::findByKey('NON-EXISTENT-KEY');

        expect($foundLicense)->toBeNull();
    });

    test('updateLastUsed updates last_used_at timestamp', function () {
        $originalTime = $this->license->last_used_at;

        sleep(1); // Attendre une seconde pour s'assurer que le timestamp change
        $this->license->updateLastUsed();

        expect($this->license->fresh()->last_used_at)->not->toBe($originalTime);
    });
});
