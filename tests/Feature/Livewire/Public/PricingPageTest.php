<?php

use App\Livewire\Public\PricingPage;
use Livewire\Livewire;

describe('PricingPage Component', function () {
    test('renders pricing page successfully', function () {
        Livewire::test(PricingPage::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.public.pricing-page');
    });

    test('initializes with monthly billing by default', function () {
        Livewire::test(PricingPage::class)
            ->assertSet('isAnnual', false);
    });

    test('toggles billing period correctly', function () {
        Livewire::test(PricingPage::class)
            ->assertSet('isAnnual', false)
            ->call('toggleBilling')
            ->assertSet('isAnnual', true)
            ->call('toggleBilling')
            ->assertSet('isAnnual', false);
    });

    test('calculates starter prices correctly', function () {
        $component = Livewire::test(PricingPage::class);

        // Test prix mensuel
        $component->assertSet('isAnnual', false);
        expect($component->instance()->getStarterPrice())->toBe(49.99);

        // Test prix annuel
        $component->set('isAnnual', true);
        expect($component->instance()->getStarterPrice())->toBe(41.66);
    });

    test('calculates professional prices correctly', function () {
        $component = Livewire::test(PricingPage::class);

        // Test prix mensuel
        $component->assertSet('isAnnual', false);
        expect($component->instance()->getProfessionalPrice())->toBe(99.99);

        // Test prix annuel
        $component->set('isAnnual', true);
        expect($component->instance()->getProfessionalPrice())->toBe(83.33);
    });

    test('calculates enterprise prices correctly', function () {
        $component = Livewire::test(PricingPage::class);

        // Test prix mensuel
        $component->assertSet('isAnnual', false);
        expect($component->instance()->getEnterprisePrice())->toBe(199.99);

        // Test prix annuel
        $component->set('isAnnual', true);
        expect($component->instance()->getEnterprisePrice())->toBe(166.66);
    });

    test('has correct title', function () {
        $component = new PricingPage();

        $reflection = new ReflectionClass($component);
        $titleAttribute = $reflection->getAttributes(\Livewire\Attributes\Title::class)[0] ?? null;

        expect($titleAttribute?->getArguments()[0])
            ->toBe('Tarifs et Plans - Batistack');
    });
});
