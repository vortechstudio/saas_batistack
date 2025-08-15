<?php

use App\Livewire\Public\DemoPage;
use Livewire\Livewire;
use Filament\Notifications\Notification;

describe('DemoPage Component', function () {
    test('renders demo page successfully', function () {
        Livewire::test(DemoPage::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.public.demo-page');
    });

    test('initializes form correctly', function () {
        Livewire::test(DemoPage::class)
            ->assertSet('data.selectedPlan', 'professional');
    });

    test('validates required fields', function () {
        Livewire::test(DemoPage::class)
            ->set('data', []) // Vider complètement les données
            ->call('submitDemo')
            ->assertHasErrors([
                'data.name' => 'required',
                'data.email' => 'required',
                'data.company' => 'required',
                'data.selectedPlan' => 'required'
            ]);
    });

    test('validates email format', function () {
        Livewire::test(DemoPage::class)
            ->set('data.email', 'invalid-email')
            ->call('submitDemo')
            ->assertHasErrors(['data.email' => 'email']);
    });

    test('validates minimum length for name', function () {
        Livewire::test(DemoPage::class)
            ->set('data.name', 'a')
            ->call('submitDemo')
            ->assertHasErrors(['data.name' => 'min']);
    });

    test('validates minimum length for company', function () {
        Livewire::test(DemoPage::class)
            ->set('data.company', 'a')
            ->call('submitDemo')
            ->assertHasErrors(['data.company' => 'min']);
    });

    test('validates maximum length for message', function () {
        Livewire::test(DemoPage::class)
            ->set('data.message', str_repeat('a', 501))
            ->call('submitDemo')
            ->assertHasErrors(['data.message' => 'max']);
    });

    test('submits form successfully with valid data', function () {
        $validData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'company' => 'Test Company',
            'phone' => '0612345678',
            'selectedPlan' => 'professional',
            'message' => 'Test message'
        ];

        Livewire::test(DemoPage::class)
            ->set('data', $validData)
            ->call('submitDemo')
            ->assertHasNoErrors()
            ->assertSet('data.selectedPlan', 'professional'); // Vérifie que la valeur par défaut est restaurée
    });

    test('has correct default plan selection', function () {
        Livewire::test(DemoPage::class)
            ->assertSet('data.selectedPlan', 'professional');
    });

    test('has correct title and layout', function () {
        $component = new DemoPage();
        $reflection = new ReflectionClass($component);

        $titleAttribute = $reflection->getAttributes(\Livewire\Attributes\Title::class)[0] ?? null;
        expect($titleAttribute?->getArguments()[0])
            ->toBe('Demander une démo - Batistack');

        $layoutAttribute = $reflection->getAttributes(\Livewire\Attributes\Layout::class)[0] ?? null;
        expect($layoutAttribute?->getArguments()[0])
            ->toBe('livewire.public.main-layout');
    });

    test('form schema is accessible for testing', function () {
        $component = new DemoPage();
        $schema = $component->getFormSchemaForTesting();

        expect($schema)->toBeArray();
        expect($schema)->not->toBeEmpty();
    });
});
