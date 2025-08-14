<?php

use App\Livewire\Public\ResourcesPage;
use Livewire\Livewire;

describe('ResourcesPage Component', function () {
    test('renders resources page successfully', function () {
        Livewire::test(ResourcesPage::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.public.resources-page');
    });

    test('has correct title', function () {
        $component = new ResourcesPage();

        $reflection = new ReflectionClass($component);
        $titleAttribute = $reflection->getAttributes(\Livewire\Attributes\Title::class)[0] ?? null;

        expect($titleAttribute?->getArguments()[0])
            ->toBe('Ressources');
    });
});
