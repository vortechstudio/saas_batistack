<?php

use App\Livewire\Public\SolutionsPage;
use Livewire\Livewire;

describe('SolutionsPage Component', function () {
    test('renders solutions page successfully', function () {
        Livewire::test(SolutionsPage::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.public.solutions-page');
    });

    test('has correct title', function () {
        $component = new SolutionsPage();

        $reflection = new ReflectionClass($component);
        $titleAttribute = $reflection->getAttributes(\Livewire\Attributes\Title::class)[0] ?? null;

        expect($titleAttribute?->getArguments()[0])
            ->toBe('Solutions BTP - Batistack');
    });

    test('uses correct layout', function () {
        $component = new SolutionsPage();

        $reflection = new ReflectionClass($component);
        $layoutAttribute = $reflection->getAttributes(\Livewire\Attributes\Layout::class)[0] ?? null;

        expect($layoutAttribute?->getArguments()[0])
            ->toBe('livewire.public.main-layout');
    });
});
