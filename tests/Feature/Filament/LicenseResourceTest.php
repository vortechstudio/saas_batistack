<?php

use App\Models\License;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Enums\LicenseStatus;
use App\Filament\Resources\Licenses\LicenseResource;
use App\Filament\Resources\Licenses\Pages\ListLicenses;
use App\Filament\Resources\Licenses\Pages\CreateLicense;
use App\Filament\Resources\Licenses\Pages\EditLicense;
use Filament\Actions\DeleteAction;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'admin@batistack.com',
        'email_verified_at' => now(),
    ]);
    $this->actingAs($this->user);
});

describe('License Resource', function () {
    test('can render license list page', function () {
        $this->get(LicenseResource::getUrl('index'))
            ->assertSuccessful();
    });

    test('can list licenses', function () {
        $licenses = License::factory()->count(10)->create();

        livewire(ListLicenses::class)
            ->assertCanSeeTableRecords($licenses);
    });

    test('can render license create page', function () {
        $this->get(LicenseResource::getUrl('create'))
            ->assertSuccessful();
    });

    test('can create license', function () {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $newData = License::factory()->make([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        livewire(CreateLicense::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'product_id' => $product->id,
                'license_key' => $newData->license_key,
                'status' => LicenseStatus::ACTIVE,
                'starts_at' => $newData->starts_at->format('Y-m-d'),
                'expires_at' => $newData->expires_at->format('Y-m-d'),
                'max_activations' => $newData->max_activations,
                'current_activations' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(License::class, [
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'license_key' => $newData->license_key,
        ]);
    });

    test('can validate license creation', function () {
        livewire(CreateLicense::class)
            ->fillForm([
                'customer_id' => null,
                'product_id' => null,
                'license_key' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'customer_id' => 'required',
                'product_id' => 'required',
                'license_key' => 'required',
            ]);
    });

    test('can render license edit page', function () {
        $license = License::factory()->create();

        $this->get(LicenseResource::getUrl('edit', [
            'record' => $license,
        ]))->assertSuccessful();
    });

    test('can retrieve license data for editing', function () {
        $license = License::factory()->create();

        livewire(EditLicense::class, [
            'record' => $license->getRouteKey(),
        ])
            ->assertFormSet([
                'customer_id' => $license->customer_id,
                'product_id' => $license->product_id,
                'license_key' => $license->license_key,
                'status' => $license->status->value,
                'max_activations' => $license->max_activations,
            ]);
    });

    test('can save license', function () {
        $license = License::factory()->create();
        $newData = License::factory()->make();

        livewire(EditLicense::class, [
            'record' => $license->getRouteKey(),
        ])
            ->fillForm([
                'status' => LicenseStatus::SUSPENDED,
                'max_activations' => $newData->max_activations,
                'current_activations' => $newData->current_activations,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($license->refresh())
            ->status->toBe(LicenseStatus::SUSPENDED)
            ->max_activations->toBe($newData->max_activations)
            ->current_activations->toBe($newData->current_activations);
    });

    test('can delete license', function () {
        $license = License::factory()->create();

        livewire(EditLicense::class, [
            'record' => $license->getRouteKey(),
        ])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($license);
    });

    test('can search licenses', function () {
        $licenses = License::factory()->count(10)->create();
        $searchLicense = $licenses->first();

        livewire(ListLicenses::class)
            ->searchTable($searchLicense->license_key)
            ->assertCanSeeTableRecords([$searchLicense])
            ->assertCanNotSeeTableRecords($licenses->skip(1));
    });

    test('can sort licenses', function () {
        $licenses = License::factory()->count(10)->create();

        livewire(ListLicenses::class)
            ->sortTable('license_key')
            ->assertCanSeeTableRecords($licenses->sortBy('license_key'), inOrder: true)
            ->sortTable('license_key', 'desc')
            ->assertCanSeeTableRecords($licenses->sortByDesc('license_key'), inOrder: true);
    });

    test('can filter licenses by status', function () {
        $activeLicenses = License::factory()->count(5)->create(['status' => LicenseStatus::ACTIVE]);
        $expiredLicenses = License::factory()->count(3)->create(['status' => LicenseStatus::EXPIRED]);

        livewire(ListLicenses::class)
            ->filterTable('status', LicenseStatus::ACTIVE->value)
            ->assertCanSeeTableRecords($activeLicenses)
            ->assertCanNotSeeTableRecords($expiredLicenses);
    });

    test('can filter expired licenses', function () {
        $expiredLicenses = License::factory()->count(3)->create([
            'expires_at' => now()->subDays(5),
            'status' => LicenseStatus::EXPIRED,
        ]);
        $activeLicenses = License::factory()->count(5)->create([
            'expires_at' => now()->addDays(30),
            'status' => LicenseStatus::ACTIVE,
        ]);

        livewire(ListLicenses::class)
            ->filterTable('expired', true)
            ->assertCanSeeTableRecords($expiredLicenses)
            ->assertCanNotSeeTableRecords($activeLicenses);
    });

    test('can bulk delete licenses', function () {
        $licenses = License::factory()->count(10)->create();

        livewire(ListLicenses::class)
            ->callTableBulkAction('delete', $licenses);

        foreach ($licenses as $license) {
            $this->assertModelMissing($license);
        }
    });

    test('displays navigation badge with license count', function () {
        License::factory()->count(15)->create();

        expect(LicenseResource::getNavigationBadge())->toBe('15');
    });

    test('can globally search licenses', function () {
        $license = License::factory()->create([
            'license_key' => 'LIC-UNIQUE-KEY-123',
        ]);

        $searchableAttributes = LicenseResource::getGloballySearchableAttributes();

        expect($searchableAttributes)->toContain('license_key');
    });
});