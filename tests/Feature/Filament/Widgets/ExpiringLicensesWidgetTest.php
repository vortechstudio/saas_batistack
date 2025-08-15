<?php

use App\Filament\Widgets\ExpiringLicensesWidget;
use App\Models\User;
use App\Models\Customer;
use App\Models\License;
use App\Models\Product;
use App\Enums\LicenseStatus;
use Livewire\Livewire;

describe('ExpiringLicensesWidget', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    test('can render widget', function () {
        Livewire::test(ExpiringLicensesWidget::class)
            ->assertSuccessful();
    });

    test('has correct widget properties', function () {
        $widget = new ExpiringLicensesWidget();
        $reflection = new \ReflectionClass($widget);

        $headingProperty = $reflection->getProperty('heading');
        $headingProperty->setAccessible(true);
        expect($headingProperty->getValue())->toBe('Licences à Renouveler');

        $descriptionProperty = $reflection->getProperty('description');
        $descriptionProperty->setAccessible(true);
        expect($descriptionProperty->getValue())->toBe('Licences expirant dans les 30 prochains jours');

        $sortProperty = $reflection->getProperty('sort');
        $sortProperty->setAccessible(true);
        expect($sortProperty->getValue())->toBe(6);
    });

    test('displays licenses expiring within 30 days', function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        // Licence expirant dans 15 jours
        $expiringLicense = License::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'status' => LicenseStatus::ACTIVE,
            'expires_at' => now()->addDays(15),
            'max_users' => 10
        ]);

        // Licence expirant dans 45 jours (ne doit pas apparaître)
        License::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'status' => LicenseStatus::ACTIVE,
            'expires_at' => now()->addDays(45),
            'max_users' => 10
        ]);

        // Licence déjà expirée (ne doit pas apparaître)
        License::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'status' => LicenseStatus::EXPIRED,
            'expires_at' => now()->subDays(5),
            'max_users' => 10
        ]);

        Livewire::test(ExpiringLicensesWidget::class)
            ->assertCanSeeTableRecords([$expiringLicense])
            ->assertSeeText($customer->company_name)
            ->assertSeeText($product->name);
    });

    test('shows correct expiration information', function () {
        $customer = Customer::factory()->create(['company_name' => 'Test Company']);
        $product = Product::factory()->create(['name' => 'Test Product']);

        // Licence expirant dans 7 jours (critique)
        $criticalLicense = License::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'status' => LicenseStatus::ACTIVE,
            'expires_at' => now()->addDays(7),
            'current_users' => 5,
            'max_users' => 10
        ]);

        // Licence expirant dans 20 jours (warning)
        $warningLicense = License::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'status' => LicenseStatus::ACTIVE,
            'expires_at' => now()->addDays(20),
            'current_users' => 3,
            'max_users' => 0 // 0 pour illimité au lieu de null
        ]);

        $widget = Livewire::test(ExpiringLicensesWidget::class);

        $widget->assertCanSeeTableRecords([$criticalLicense, $warningLicense])
            ->assertSeeText('Test Company')
            ->assertSeeText('Test Product')
            ->assertSeeText('5')
            ->assertSeeText('/ 10 max')
            ->assertSeeText('3')
            ->assertSeeText('Illimité');
    });

    test('sorts licenses by expiration date', function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        $license1 = License::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'expires_at' => now()->addDays(25),
            'max_users' => 10
        ]);

        $license2 = License::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'expires_at' => now()->addDays(10),
            'max_users' => 10
        ]);

        $license3 = License::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'expires_at' => now()->addDays(5),
            'max_users' => 10
        ]);

        Livewire::test(ExpiringLicensesWidget::class)
            ->assertCanSeeTableRecords([$license1, $license2, $license3])
            ->assertTableColumnFormattedStateSet('expires_at', $license3->expires_at->format('d/m/Y'), $license3)
            ->assertTableColumnFormattedStateSet('expires_at', $license2->expires_at->format('d/m/Y'), $license2)
            ->assertTableColumnFormattedStateSet('expires_at', $license1->expires_at->format('d/m/Y'), $license1);
    });

    test('shows empty state when no expiring licenses', function () {
        Livewire::test(ExpiringLicensesWidget::class)
            ->assertSeeText('Aucune licence à renouveler')
            ->assertSeeText('Toutes les licences sont valides pour plus de 30 jours.');
    });

    test('displays license status badges', function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        $license = License::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'status' => LicenseStatus::ACTIVE,
            'expires_at' => now()->addDays(15),
            'max_users' => 10
        ]);

        Livewire::test(ExpiringLicensesWidget::class)
            ->assertCanSeeTableRecords([$license])
            ->assertTableColumnFormattedStateSet('status', $license->status->label(), $license);
    });
});
