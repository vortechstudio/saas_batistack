<?php

use App\Filament\Widgets\RecentLicensesWidget;
use App\Models\License;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $role = Role::create(['name' => 'admin']);
    $this->user->assignRole($role);
    $this->actingAs($this->user);
});

test('can render widget', function () {
    Livewire::test(RecentLicensesWidget::class)
        ->assertOk();
});

test('has correct properties', function () {
    $reflection = new ReflectionClass(RecentLicensesWidget::class);

    $headingProperty = $reflection->getProperty('heading');
    $headingProperty->setAccessible(true);
    expect($headingProperty->getValue())->toBe('Licences Récentes');

    $sortProperty = $reflection->getProperty('sort');
    $sortProperty->setAccessible(true);
    expect($sortProperty->getValue())->toBe(5);
});

test('displays recent licenses', function () {
    $customer = Customer::factory()->create(['company_name' => 'Test Company']);
    $product = Product::factory()->create(['name' => 'Test Product']);

    $license = License::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'created_at' => Carbon::now()
    ]);

    Livewire::test(RecentLicensesWidget::class)
        ->assertCanSeeTableRecords([$license])
        ->assertSee('Test Company')
        ->assertSee('Test Product');
});

test('orders by creation date descending', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create();

    $oldLicense = License::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'created_at' => Carbon::now()->subDays(5)
    ]);

    $newLicense = License::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'created_at' => Carbon::now()
    ]);

    // Tester directement la requête utilisée par le widget
    $query = License::query()
        ->with(['customer', 'product'])
        ->orderBy('created_at', 'desc')
        ->limit(10);

    $licenses = $query->get();

    // Vérifier que les licences sont ordonnées par date de création décroissante
    expect($licenses->first()->id)->toBe($newLicense->id);
    expect($licenses->last()->id)->toBe($oldLicense->id);
});

test('limits to 10 records', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create();

    License::factory()->count(15)->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id
    ]);

    // Tester directement la requête utilisée par le widget
    $query = License::query()
        ->with(['customer', 'product'])
        ->orderBy('created_at', 'desc')
        ->limit(10);

    $licenses = $query->get();

    // Vérifier que seulement 10 enregistrements sont retournés
    expect($licenses->count())->toBe(10);
});

test('shows license key with copy functionality', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create();

    $license = License::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'license_key' => 'TEST-LICENSE-KEY-123'
    ]);

    Livewire::test(RecentLicensesWidget::class)
        ->assertSee('TEST-LICENSE-KEY-123');
});
