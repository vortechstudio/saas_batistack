<?php

use App\Filament\Widgets\RevenueChartWidget;
use App\Models\User;
use App\Models\License;
use App\Models\Product;
use App\Enums\LicenseStatus;
use Livewire\Livewire;

describe('RevenueChartWidget', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    test('can render widget', function () {
        Livewire::test(RevenueChartWidget::class)
            ->assertSuccessful();
    });

    test('has correct widget properties', function () {
        $widget = new RevenueChartWidget();
        $reflection = new ReflectionClass($widget);

        $headingProperty = $reflection->getProperty('heading');
        $headingProperty->setAccessible(true);
        expect($headingProperty->getValue($widget))->toBe('Évolution des Revenus');

        $descriptionProperty = $reflection->getProperty('description');
        $descriptionProperty->setAccessible(true);
        expect($descriptionProperty->getValue($widget))->toBe('Revenus mensuels des 12 derniers mois');

        expect(RevenueChartWidget::getSort())->toBe(2);
    });

    test('returns line chart type', function () {
        $widget = new RevenueChartWidget();
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getType');
        $method->setAccessible(true);

        expect($method->invoke($widget))->toBe('line');
    });

    test('generates correct chart data structure', function () {
        $product = Product::factory()->create(['base_price' => 200]);

        // Créer des licences pour différents mois
        License::factory()->create([
            'product_id' => $product->id,
            'status' => LicenseStatus::ACTIVE,
            'created_at' => now()
        ]);

        License::factory()->create([
            'product_id' => $product->id,
            'status' => LicenseStatus::ACTIVE,
            'created_at' => now()->subMonth()
        ]);

        $widget = new RevenueChartWidget();
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);
        $data = $method->invoke($widget);

        expect($data)->toHaveKeys(['datasets', 'labels']);
        expect($data['datasets'])->toHaveCount(1);
        expect($data['datasets'][0])->toHaveKeys(['label', 'data', 'backgroundColor', 'borderColor', 'borderWidth', 'fill']);
        expect($data['datasets'][0]['label'])->toBe('Revenus (€)');
        expect($data['labels'])->toHaveCount(12); // 12 derniers mois
    });

    test('calculates revenue data for 12 months', function () {
        $product = Product::factory()->create(['base_price' => 100]);

        // Créer une licence pour ce mois
        License::factory()->create([
            'product_id' => $product->id,
            'status' => LicenseStatus::ACTIVE,
            'created_at' => now()
        ]);

        // Créer deux licences pour il y a 3 mois
        License::factory()->count(2)->create([
            'product_id' => $product->id,
            'status' => LicenseStatus::ACTIVE,
            'created_at' => now()->subMonths(3)
        ]);

        $widget = new RevenueChartWidget();
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);
        $data = $method->invoke($widget);

        $revenueData = $data['datasets'][0]['data'];
        expect($revenueData)->toHaveCount(12);

        // Le dernier élément (mois actuel) devrait être 100
        expect(end($revenueData))->toBe(100);

        // L'élément d'il y a 3 mois devrait être 200
        expect($revenueData[8])->toBe(200); // 12 - 3 - 1 = 8
    });

    test('has correct chart options', function () {
        $widget = new RevenueChartWidget();
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getOptions');
        $method->setAccessible(true);
        $options = $method->invoke($widget);

        expect($options)->toHaveKeys(['plugins', 'scales']);
        expect($options['plugins']['legend']['display'])->toBeTrue();
        expect($options['scales']['y']['beginAtZero'])->toBeTrue();
    });
});
