<?php

use App\Models\Option;
use App\Models\Product;
use App\Models\License;
use App\Enums\OptionType;
use App\Enums\BillingCycle;

beforeEach(function () {
    $this->option = Option::factory()->create([
        'key' => 'test-option',
        'name' => 'Test Option',
        'type' => OptionType::FEATURE,
        'price' => 15.00,
        'billing_cycle' => BillingCycle::MONTHLY,
        'is_active' => true,
    ]);
});

describe('Option Model', function () {
    test('can create an option', function () {
        expect($this->option)->toBeInstanceOf(Option::class)
            ->and($this->option->key)->toBe('test-option')
            ->and($this->option->name)->toBe('Test Option')
            ->and($this->option->price)->toBe('15.00');
    });

    test('has correct fillable attributes', function () {
        $fillable = [
            'key', 'name', 'description', 'type',
            'price', 'billing_cycle', 'is_active'
        ];

        expect($this->option->getFillable())->toBe($fillable);
    });

    test('casts attributes correctly', function () {
        expect($this->option->type)->toBeInstanceOf(OptionType::class)
            ->and($this->option->billing_cycle)->toBeInstanceOf(BillingCycle::class)
            ->and($this->option->is_active)->toBeTrue();
    });

    test('can belong to many products', function () {
        $product = Product::factory()->create();
        $this->option->products()->attach($product->id);

        expect($this->option->products)->toHaveCount(1);
    });

    test('can belong to many licenses', function () {
        $license = License::factory()->create();

        $this->option->licenses()->attach($license->id, [
            'enabled' => true,
            'expires_at' => now()->addDays(30)
        ]);

        expect($this->option->licenses)->toHaveCount(1)
            ->and($this->option->licenses->first()->pivot->enabled)->toBe(1); // Base de données stocke comme entier
    });

    test('active scope filters active options', function () {
        Option::factory()->create(['is_active' => false]);

        $activeOptions = Option::active()->get();

        expect($activeOptions)->toHaveCount(1)
            ->and($activeOptions->first()->is_active)->toBeTrue();
    });

    test('byType scope filters by type', function () {
        Option::factory()->create(['type' => OptionType::SUPPORT]);

        $featureOptions = Option::byType(OptionType::FEATURE)->get();

        expect($featureOptions)->toHaveCount(1)
            ->and($featureOptions->first()->type)->toBe(OptionType::FEATURE);
    });

    test('byBillingCycle scope filters by billing cycle', function () {
        Option::factory()->create(['billing_cycle' => BillingCycle::YEARLY]);

        $monthlyOptions = Option::byBillingCycle(BillingCycle::MONTHLY)->get();

        expect($monthlyOptions)->toHaveCount(1)
            ->and($monthlyOptions->first()->billing_cycle)->toBe(BillingCycle::MONTHLY);
    });

    test('isActive returns correct boolean', function () {
        expect($this->option->isActive())->toBeTrue();

        $inactiveOption = Option::factory()->create(['is_active' => false]);
        expect($inactiveOption->isActive())->toBeFalse();
    });
});
