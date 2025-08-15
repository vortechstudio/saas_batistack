<?php

use App\Filament\Widgets\LicenseStatusWidget;
use App\Models\User;
use App\Models\License;
use App\Models\Product;
use App\Enums\LicenseStatus;
use Livewire\Livewire;

describe('LicenseStatusWidget', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    test('can render widget', function () {
        Livewire::test(LicenseStatusWidget::class)
            ->assertSuccessful();
    });

    test('has correct widget properties', function () {
        $widget = new LicenseStatusWidget();
        $reflection = new ReflectionClass($widget);

        $headingProperty = $reflection->getProperty('heading');
        $headingProperty->setAccessible(true);
        expect($headingProperty->getValue($widget))->toBe('Répartition des Licences');

        $descriptionProperty = $reflection->getProperty('description');
        $descriptionProperty->setAccessible(true);
        expect($descriptionProperty->getValue($widget))->toBe('État actuel de toutes les licences');

        expect(LicenseStatusWidget::getSort())->toBe(3);
    });

    test('returns doughnut chart type', function () {
        $widget = new LicenseStatusWidget();
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getType');
        $method->setAccessible(true);

        expect($method->invoke($widget))->toBe('doughnut');
    });

    test('generates correct chart data structure', function () {
        $product = Product::factory()->create();

        // Créer des licences avec différents statuts
        License::factory()->count(3)->create([
            'product_id' => $product->id,
            'status' => LicenseStatus::ACTIVE
        ]);

        License::factory()->count(2)->create([
            'product_id' => $product->id,
            'status' => LicenseStatus::SUSPENDED
        ]);

        License::factory()->create([
            'product_id' => $product->id,
            'status' => LicenseStatus::EXPIRED
        ]);

        License::factory()->create([
            'product_id' => $product->id,
            'status' => LicenseStatus::CANCELLED
        ]);

        $widget = new LicenseStatusWidget();
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);
        $data = $method->invoke($widget);

        expect($data)->toHaveKeys(['datasets', 'labels']);
        expect($data['datasets'])->toHaveCount(1);
        expect($data['datasets'][0])->toHaveKeys(['data', 'backgroundColor']);
        expect($data['labels'])->toBe(['Actives', 'Suspendues', 'Expirées', 'Annulées']);
        expect($data['datasets'][0]['data'])->toBe([3, 2, 1, 1]);
    });

    test('calculates license status counts correctly', function () {
        $product = Product::factory()->create();

        // Créer 5 licences actives
        License::factory()->count(5)->create([
            'product_id' => $product->id,
            'status' => LicenseStatus::ACTIVE
        ]);

        // Créer 3 licences expirées
        License::factory()->count(3)->create([
            'product_id' => $product->id,
            'status' => LicenseStatus::EXPIRED
        ]);

        $widget = new LicenseStatusWidget();
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);
        $data = $method->invoke($widget);

        $counts = $data['datasets'][0]['data'];
        expect($counts[0])->toBe(5); // Actives
        expect($counts[1])->toBe(0); // Suspendues
        expect($counts[2])->toBe(3); // Expirées
        expect($counts[3])->toBe(0); // Annulées
    });

    test('has correct chart options', function () {
        $widget = new LicenseStatusWidget();
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getOptions');
        $method->setAccessible(true);
        $options = $method->invoke($widget);

        expect($options)->toHaveKeys(['plugins']);
        expect($options['plugins']['legend']['display'])->toBeTrue();
        expect($options['plugins']['legend']['position'])->toBe('bottom');
    });

    test('handles empty license data', function () {
        $widget = new LicenseStatusWidget();
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);
        $data = $method->invoke($widget);

        expect($data['datasets'][0]['data'])->toBe([0, 0, 0, 0]);
        expect($data['labels'])->toBe(['Actives', 'Suspendues', 'Expirées', 'Annulées']);
    });
});
