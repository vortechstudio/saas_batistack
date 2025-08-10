<?php

use App\Models\Module;
use App\Models\Product;
use App\Models\License;
use App\Enums\ModuleCategory;

beforeEach(function () {
    $this->module = Module::factory()->create([
        'key' => 'test-module',
        'name' => 'Test Module',
        'category' => ModuleCategory::CORE,
        'base_price' => 25.00,
        'is_active' => true,
        'sort_order' => 1,
    ]);
});

describe('Module Model', function () {
    test('can create a module', function () {
        expect($this->module)->toBeInstanceOf(Module::class)
            ->and($this->module->key)->toBe('test-module')
            ->and($this->module->name)->toBe('Test Module')
            ->and($this->module->base_price)->toBe('25.00');
    });

    test('has correct fillable attributes', function () {
        $fillable = [
            'key', 'name', 'description', 'category',
            'base_price', 'is_active', 'sort_order'
        ];

        expect($this->module->getFillable())->toBe($fillable);
    });

    test('casts attributes correctly', function () {
        expect($this->module->category)->toBeInstanceOf(ModuleCategory::class)
            ->and($this->module->is_active)->toBeTrue()
            ->and($this->module->sort_order)->toBe(1);
    });

    test('can belong to many products', function () {
        $product = Product::factory()->create();

        $this->module->products()->attach($product->id, [
            'included' => true,
            'price_override' => 20.00
        ]);

        expect($this->module->products)->toHaveCount(1)
            ->and($this->module->products->first()->pivot->included)->toBe(1); // Base de données stocke comme entier
    });

    test('can belong to many licenses', function () {
        $license = License::factory()->create();

        $this->module->licenses()->attach($license->id, [
            'enabled' => true,
            'expires_at' => now()->addDays(30)
        ]);

        expect($this->module->licenses)->toHaveCount(1)
            ->and($this->module->licenses->first()->pivot->enabled)->toBe(1); // Base de données stocke comme entier
    });

    test('active scope filters active modules', function () {
        Module::factory()->create(['is_active' => false]);

        $activeModules = Module::active()->get();

        expect($activeModules)->toHaveCount(1)
            ->and($activeModules->first()->is_active)->toBeTrue();
    });

    test('byCategory scope filters by category', function () {
        Module::factory()->create(['category' => ModuleCategory::ADVANCED]);

        $coreModules = Module::byCategory(ModuleCategory::CORE)->get();

        expect($coreModules)->toHaveCount(1)
            ->and($coreModules->first()->category)->toBe(ModuleCategory::CORE);
    });

    test('ordered scope orders by sort_order then name', function () {
        Module::factory()->create(['name' => 'B Module', 'sort_order' => 2]);
        Module::factory()->create(['name' => 'A Module', 'sort_order' => 0]);
        
        $orderedModules = Module::ordered()->get();
        
        expect($orderedModules->first()->name)->toBe('A Module')
            ->and($orderedModules->last()->name)->toBe('B Module');
    });

    test('isActive returns correct boolean', function () {
        expect($this->module->isActive())->toBeTrue();

        $inactiveModule = Module::factory()->create(['is_active' => false]);
        expect($inactiveModule->isActive())->toBeFalse();
    });
});
