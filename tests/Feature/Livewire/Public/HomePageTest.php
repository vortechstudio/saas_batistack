<?php

use App\Livewire\Public\HomePage;
use Livewire\Livewire;

describe('HomePage Component', function () {
    test('renders home page successfully', function () {
        Livewire::test(HomePage::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.public.home-page');
    });

    test('has correct title and layout', function () {
        $component = new HomePage();

        expect($component)
            ->toBeInstanceOf(HomePage::class);

        // Test que le titre est correct
        $reflection = new ReflectionClass($component);
        $titleAttribute = $reflection->getAttributes(\Livewire\Attributes\Title::class)[0] ?? null;

        expect($titleAttribute?->getArguments()[0])
            ->toBe('Accueil');
    });

    test('uses correct layout', function () {
        $component = new HomePage();

        $reflection = new ReflectionClass($component);
        $layoutAttribute = $reflection->getAttributes(\Livewire\Attributes\Layout::class)[0] ?? null;

        expect($layoutAttribute?->getArguments()[0])
            ->toBe('livewire.public.main-layout');
    });
});