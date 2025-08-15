<?php

use App\Filament\Widgets\StatsOverviewWidget;
use App\Models\User;
use App\Models\Customer;
use App\Models\License;
use App\Models\Product;
use App\Enums\CustomerStatus;
use App\Enums\LicenseStatus;
use Livewire\Livewire;

describe('StatsOverviewWidget', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    test('can render widget', function () {
        Livewire::test(StatsOverviewWidget::class)
            ->assertSuccessful();
    });

    test('displays correct customer statistics', function () {
        // Créer des clients actifs et inactifs
        Customer::factory()->count(5)->create(['status' => CustomerStatus::ACTIVE]);
        Customer::factory()->count(2)->create(['status' => CustomerStatus::INACTIVE]);

        $widget = Livewire::test(StatsOverviewWidget::class);
        $instance = $widget->instance();

        // Utiliser la réflexion pour accéder à la méthode protégée
        $reflection = new \ReflectionClass($instance);
        $getStatsMethod = $reflection->getMethod('getStats');
        $getStatsMethod->setAccessible(true);
        $stats = $getStatsMethod->invoke($instance);

        $clientsActifsStat = collect($stats)->first(fn($stat) => str_contains($stat->getLabel(), 'Clients Actifs'));
        expect($clientsActifsStat->getValue())->toBe(5);
        expect($clientsActifsStat->getDescription())->toContain('2 clients inactifs');
    });

    test('displays correct license statistics', function () {
        $product = Product::factory()->create(['base_price' => 100]);

        // Créer des licences avec différents statuts
        License::factory()->count(3)->create([
            'product_id' => $product->id,
            'status' => LicenseStatus::ACTIVE,
            'expires_at' => now()->addMonths(2)
        ]);

        License::factory()->count(2)->create([
            'product_id' => $product->id,
            'status' => LicenseStatus::EXPIRED,
            'expires_at' => now()->subDays(10)
        ]);

        // Licences expirant bientôt
        License::factory()->count(1)->create([
            'product_id' => $product->id,
            'status' => LicenseStatus::ACTIVE,
            'expires_at' => now()->addDays(15)
        ]);

        $widget = Livewire::test(StatsOverviewWidget::class);
        $instance = $widget->instance();

        // Utiliser la réflexion pour accéder à la méthode protégée
        $reflection = new \ReflectionClass($instance);
        $getStatsMethod = $reflection->getMethod('getStats');
        $getStatsMethod->setAccessible(true);
        $stats = $getStatsMethod->invoke($instance);

        $licencesActivesStat = collect($stats)->first(fn($stat) => str_contains($stat->getLabel(), 'Licences Actives'));
        expect($licencesActivesStat->getValue())->toBe(4); // 3 + 1 active

        $aRenouvelerStat = collect($stats)->first(fn($stat) => str_contains($stat->getLabel(), 'À Renouveler'));
        expect($aRenouvelerStat->getValue())->toBe(1);
    });

    test('calculates monthly revenue correctly', function () {
        $product = Product::factory()->create(['base_price' => 150]);

        // Créer des licences pour ce mois
        License::factory()->count(2)->create([
            'product_id' => $product->id,
            'status' => LicenseStatus::ACTIVE,
            'created_at' => now()
        ]);

        // Créer une licence pour le mois dernier (ne doit pas être comptée)
        License::factory()->create([
            'product_id' => $product->id,
            'status' => LicenseStatus::ACTIVE,
            'created_at' => now()->subMonth()
        ]);

        $widget = Livewire::test(StatsOverviewWidget::class);
        $instance = $widget->instance();

        // Utiliser la réflexion pour accéder à la méthode protégée
        $reflection = new \ReflectionClass($instance);
        $getStatsMethod = $reflection->getMethod('getStats');
        $getStatsMethod->setAccessible(true);
        $stats = $getStatsMethod->invoke($instance);

        $revenusMenuelsStat = collect($stats)->first(fn($stat) => str_contains($stat->getLabel(), 'Revenus Mensuels'));
        expect($revenusMenuelsStat->getValue())->toBe('300.00 €');
    });

    test('displays product statistics', function () {
        Product::factory()->count(8)->create(['is_active' => true]);
        Product::factory()->count(3)->create(['is_active' => false]);

        $widget = Livewire::test(StatsOverviewWidget::class);
        $instance = $widget->instance();

        // Utiliser la réflexion pour accéder à la méthode protégée
        $reflection = new \ReflectionClass($instance);
        $getStatsMethod = $reflection->getMethod('getStats');
        $getStatsMethod->setAccessible(true);
        $stats = $getStatsMethod->invoke($instance);

        $produitsActifsStat = collect($stats)->first(fn($stat) => str_contains($stat->getLabel(), 'Produits Actifs'));
        expect($produitsActifsStat->getValue())->toBe(8);
        expect($produitsActifsStat->getDescription())->toContain('3 inactifs');
    });

    test('has correct widget properties', function () {
        $widget = new StatsOverviewWidget();
        $reflection = new \ReflectionClass($widget);

        $pollingProperty = $reflection->getProperty('pollingInterval');
        $pollingProperty->setAccessible(true);
        expect($pollingProperty->getValue($widget))->toBe('30s');

        expect(StatsOverviewWidget::getSort())->toBe(1);
    });
});
