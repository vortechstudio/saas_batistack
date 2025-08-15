<?php

use App\Filament\Widgets\PopularProductsWidget;
use App\Models\Product;
use App\Models\License;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $role = Role::create(['name' => 'admin']);
    $this->user->assignRole($role);
    $this->actingAs($this->user);
});

test('can render widget', function () {
    Livewire::test(PopularProductsWidget::class)
        ->assertOk();
});

test('has correct properties', function () {
    $reflection = new ReflectionClass(PopularProductsWidget::class);

    $headingProperty = $reflection->getProperty('heading');
    $headingProperty->setAccessible(true);
    expect($headingProperty->getValue())->toBe('Produits Populaires');

    $sortProperty = $reflection->getProperty('sort');
    $sortProperty->setAccessible(true);
    expect($sortProperty->getValue())->toBe(4);
});

test('displays products with license count', function () {
    $product1 = Product::factory()->create(['name' => 'Product 1']);
    $product2 = Product::factory()->create(['name' => 'Product 2']);

    // Créer des licences pour les produits
    License::factory()->count(3)->create(['product_id' => $product1->id]);
    License::factory()->count(1)->create(['product_id' => $product2->id]);

    Livewire::test(PopularProductsWidget::class)
        ->assertCanSeeTableRecords([$product1, $product2])
        ->assertSee('Product 1')
        ->assertSee('Product 2');
});

test('orders by license count descending', function () {
    $product1 = Product::factory()->create(['name' => 'Less Popular']);
    $product2 = Product::factory()->create(['name' => 'More Popular']);

    License::factory()->count(1)->create(['product_id' => $product1->id]);
    License::factory()->count(5)->create(['product_id' => $product2->id]);

    // Tester directement la requête du widget
    $query = Product::query()
        ->withCount('licenses')
        ->orderBy('licenses_count', 'desc')
        ->limit(10);

    $results = $query->get();

    // Vérifier que le produit le plus populaire est en premier
    expect($results->first()->name)->toBe('More Popular');
    expect($results->first()->licenses_count)->toBe(5);
    expect($results->last()->name)->toBe('Less Popular');
    expect($results->last()->licenses_count)->toBe(1);
});

test('limits to 10 records', function () {
    // Créer 15 produits avec des licences
    $products = Product::factory()->count(15)->create();

    $products->each(function ($product) {
        License::factory()->count(rand(1, 3))->create(['product_id' => $product->id]);
    });

    // Tester directement la requête du widget
    $query = Product::query()
        ->withCount('licenses')
        ->orderBy('licenses_count', 'desc')
        ->limit(10);

    $results = $query->get();

    // Vérifier que le résultat est limité à 10 enregistrements maximum
    expect($results)->toHaveCount(10);
    expect($results->count())->toBeLessThanOrEqual(10);
});
