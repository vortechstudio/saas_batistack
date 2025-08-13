<?php

use App\Livewire\Client\Forms\OrderLicenseForm;
use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Module;
use App\Models\Option;
use App\Enums\BillingCycle;
use App\Enums\OptionType;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->customer = Customer::factory()->create(['user_id' => $this->user->id]);
    $this->product = Product::factory()->create();
    $this->actingAs($this->user);
});

describe('OrderLicenseForm Component', function () {
    test('can select product and calculate total', function () {
        Livewire::test(OrderLicenseForm::class)
            ->set('data.product_id', $this->product->id)
            ->assertSet('selectedProduct.id', $this->product->id)
            ->assertSet('totalPrice', $this->product->base_price);
    });

    test('can add optional modules', function () {
        $module = Module::factory()->create();
        $this->product->optionalModules()->attach($module->id, ['price_override' => 50.00]);

        Livewire::test(OrderLicenseForm::class)
            ->set('data.product_id', $this->product->id)
            ->set('data.optional_modules', [$module->id])
            ->assertSee($module->id, false); // Vérifier que l'ID du module est présent
    });

    test('can add options', function () {
        $option = Option::factory()->create([
            'price' => 25.00,
            'type' => OptionType::STORAGE
        ]);
        $this->product->options()->attach($option->id);

        Livewire::test(OrderLicenseForm::class)
            ->set('data.product_id', $this->product->id)
            ->set('data.storage_options', [$option->id])
            ->assertSee($option->id, false); // Vérifier que l'ID de l'option est présent
    });

    test('applies yearly discount correctly', function () {
        $monthlyPrice = $this->product->base_price;

        Livewire::test(OrderLicenseForm::class)
            ->set('data.product_id', $this->product->id)
            ->set('data.billing_cycle', BillingCycle::YEARLY->value)
            ->assertSet('totalPrice', $monthlyPrice * 10); // 2 mois gratuits
    });

    test('validates required fields before payment', function () {
        Livewire::test(OrderLicenseForm::class)
            ->call('proceedToPayment')
            ->assertHasErrors(['data.product_id', 'data.domain']);
    });

    test('can set domain information', function () {
        Livewire::test(OrderLicenseForm::class)
            ->set('data.product_id', $this->product->id)
            ->set('data.domain', 'example.com')
            ->set('data.domain_notes', 'Test domain notes')
            ->assertSet('data.domain', 'example.com')
            ->assertSet('data.domain_notes', 'Test domain notes');
    });

    test('can set billing cycle', function () {
        Livewire::test(OrderLicenseForm::class)
            ->set('data.product_id', $this->product->id)
            ->set('data.billing_cycle', BillingCycle::MONTHLY->value)
            ->assertSet('data.billing_cycle', BillingCycle::MONTHLY->value);
    });
});
