<?php

use App\Models\Product;
use App\Models\License;
use App\Models\Module;
use App\Models\Option;
use App\Enums\BillingCycle;

beforeEach(function () {
    $this->product = Product::factory()->create([
        'name' => 'Test Product',
        'slug' => 'test-product',
        'base_price' => 99.99,
        'billing_cycle' => BillingCycle::MONTHLY,
        'max_users' => 10,
        'is_active' => true,
        'is_featured' => false,
    ]);
});

describe('Product Model', function () {
    test('can create a product', function () {
        expect($this->product)->toBeInstanceOf(Product::class)
            ->and($this->product->name)->toBe('Test Product')
            ->and($this->product->slug)->toBe('test-product')
            ->and($this->product->base_price)->toBe('99.99');
    });

    test('has correct fillable attributes', function () {
        $fillable = [
            'name', 'slug', 'description', 'base_price', 'billing_cycle',
            'max_users', 'max_projects', 'storage_limit', 'is_active',
            'is_featured', 'stripe_price_id'
        ];

        expect($this->product->getFillable())->toBe($fillable);
    });

    test('casts attributes correctly', function () {
        expect($this->product->billing_cycle)->toBeInstanceOf(BillingCycle::class)
            ->and($this->product->is_active)->toBeTrue()
            ->and($this->product->is_featured)->toBeFalse()
            ->and($this->product->max_users)->toBe(10);
    });

    test('can have many licenses', function () {
        License::factory()->count(3)->create(['product_id' => $this->product->id]);

        expect($this->product->licenses)->toHaveCount(3);
    });

    test('can have many modules with pivot data', function () {
        $module = Module::factory()->create();

        $this->product->modules()->attach($module->id, [
            'included' => true,
            'price_override' => 50.00
        ]);

        expect($this->product->modules)->toHaveCount(1)
            ->and($this->product->modules->first()->pivot->included)->toBe(1) // Base de données stocke comme entier
            ->and($this->product->modules->first()->pivot->price_override)->toBe(50);
    });

    test('can have many options', function () {
        $option = Option::factory()->create();
        $this->product->options()->attach($option->id);

        expect($this->product->options)->toHaveCount(1);
    });

    test('includedModules returns only included modules', function () {
        $includedModule = Module::factory()->create();
        $optionalModule = Module::factory()->create();

        $this->product->modules()->attach([
            $includedModule->id => ['included' => true],
            $optionalModule->id => ['included' => false]
        ]);

        expect($this->product->includedModules)->toHaveCount(1)
            ->and($this->product->includedModules->first()->id)->toBe($includedModule->id);
    });

    test('optionalModules returns only optional modules', function () {
        $includedModule = Module::factory()->create();
        $optionalModule = Module::factory()->create();

        $this->product->modules()->attach([
            $includedModule->id => ['included' => true],
            $optionalModule->id => ['included' => false]
        ]);

        expect($this->product->optionalModules)->toHaveCount(1)
            ->and($this->product->optionalModules->first()->id)->toBe($optionalModule->id);
    });

    test('active scope filters active products', function () {
        Product::factory()->create(['is_active' => false]);

        $activeProducts = Product::active()->get();

        expect($activeProducts)->toHaveCount(1)
            ->and($activeProducts->first()->is_active)->toBeTrue();
    });

    test('featured scope filters featured products', function () {
        Product::factory()->create(['is_featured' => true]);

        $featuredProducts = Product::featured()->get();

        expect($featuredProducts)->toHaveCount(1)
            ->and($featuredProducts->first()->is_featured)->toBeTrue();
    });

    test('byBillingCycle scope filters by billing cycle', function () {
        Product::factory()->create(['billing_cycle' => BillingCycle::YEARLY]);

        $monthlyProducts = Product::byBillingCycle(BillingCycle::MONTHLY)->get();

        expect($monthlyProducts)->toHaveCount(1)
            ->and($monthlyProducts->first()->billing_cycle)->toBe(BillingCycle::MONTHLY);
    });

    test('calculateTotalPrice returns base price when no modules or options', function () {
        expect($this->product->calculateTotalPrice())->toBe(99.99);
    });

    test('calculateTotalPrice includes optional modules', function () {
        $module = Module::factory()->create(['base_price' => 25.00]);

        $this->product->modules()->attach($module->id, [
            'included' => false,
            'price_override' => null
        ]);

        $total = $this->product->calculateTotalPrice([$module->id]);

        expect($total)->toBe(124.99);
    });

    test('calculateTotalPrice uses price override for modules', function () {
        $module = Module::factory()->create(['base_price' => 25.00]);

        $this->product->modules()->attach($module->id, [
            'included' => false,
            'price_override' => 15.00
        ]);

        $total = $this->product->calculateTotalPrice([$module->id]);

        expect($total)->toBe(114.99);
    });

    test('calculateTotalPrice includes options', function () {
        $option = Option::factory()->create(['price' => 10.00]);
        $this->product->options()->attach($option->id);

        $total = $this->product->calculateTotalPrice([], [$option->id]);

        expect($total)->toBe(109.99);
    });

    test('isActive returns correct boolean', function () {
        expect($this->product->isActive())->toBeTrue();

        $inactiveProduct = Product::factory()->create(['is_active' => false]);
        expect($inactiveProduct->isActive())->toBeFalse();
    });

    test('isFeatured returns correct boolean', function () {
        expect($this->product->isFeatured())->toBeFalse();

        $featuredProduct = Product::factory()->create(['is_featured' => true]);
        expect($featuredProduct->isFeatured())->toBeTrue();
    });
});
