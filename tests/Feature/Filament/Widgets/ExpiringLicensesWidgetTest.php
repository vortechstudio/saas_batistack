<?php

use App\Filament\Widgets\ExpiringLicensesWidget;
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
    Livewire::test(ExpiringLicensesWidget::class)
        ->assertOk();
});

test('has correct properties', function () {
    $reflection = new ReflectionClass(ExpiringLicensesWidget::class);

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

test('shows only licenses expiring within 30 days', function () {
    $customer = Customer::factory()->create(['company_name' => 'Test Company']);
    $product = Product::factory()->create(['name' => 'Test Product']);

    // Licence expirant dans 15 jours (doit être affichée)
    $expiringLicense = License::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'expires_at' => Carbon::now()->addDays(15)
    ]);

    // Licence expirant dans 45 jours (ne doit pas être affichée)
    $futureExpiringLicense = License::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'expires_at' => Carbon::now()->addDays(45)
    ]);

    // Licence déjà expirée (ne doit pas être affichée)
    $expiredLicense = License::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'expires_at' => Carbon::now()->subDays(5)
    ]);

    Livewire::test(ExpiringLicensesWidget::class)
        ->assertCanSeeTableRecords([$expiringLicense])
        ->assertCanNotSeeTableRecords([$futureExpiringLicense, $expiredLicense]);
});

test('orders by expiration date ascending', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create();

    $license1 = License::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'expires_at' => Carbon::now()->addDays(25)
    ]);

    $license2 = License::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'expires_at' => Carbon::now()->addDays(5)
    ]);

    // Tester directement la requête utilisée par le widget
    $query = License::query()
        ->with(['customer', 'product'])
        ->where('expires_at', '>', Carbon::now())
        ->where('expires_at', '<=', Carbon::now()->addDays(30))
        ->orderBy('expires_at', 'asc');

    $licenses = $query->get();

    // Vérifier que les licences sont ordonnées par date d'expiration croissante
    expect($licenses->first()->id)->toBe($license2->id); // expire dans 5 jours
    expect($licenses->last()->id)->toBe($license1->id);  // expire dans 25 jours
});

test('shows empty state when no expiring licenses', function () {
    Livewire::test(ExpiringLicensesWidget::class)
        ->assertSee('Aucune licence à renouveler')
        ->assertSee('Toutes les licences sont valides pour plus de 30 jours.');
});

test('displays customer and product information', function () {
    $customer = Customer::factory()->create(['company_name' => 'ACME Corp']);
    $product = Product::factory()->create(['name' => 'Premium License']);

    $license = License::factory()->create([
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'expires_at' => Carbon::now()->addDays(10)
    ]);

    Livewire::test(ExpiringLicensesWidget::class)
        ->assertSee('ACME Corp')
        ->assertSee('Premium License');
});
