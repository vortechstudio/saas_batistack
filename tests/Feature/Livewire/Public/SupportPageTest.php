<?php

use App\Livewire\Public\SupportPage;
use Livewire\Livewire;

describe('SupportPage Component', function () {
    test('renders support page successfully', function () {
        Livewire::test(SupportPage::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.public.support-page');
    });

    test('has correct title and layout', function () {
        $component = new SupportPage();
        $reflection = new ReflectionClass($component);

        $titleAttribute = $reflection->getAttributes(\Livewire\Attributes\Title::class)[0] ?? null;
        expect($titleAttribute?->getArguments()[0])
            ->toBe('Support Technique - Batistack');

        $layoutAttribute = $reflection->getAttributes(\Livewire\Attributes\Layout::class)[0] ?? null;
        expect($layoutAttribute?->getArguments()[0])
            ->toBe('livewire.public.main-layout');
    });

    test('uses correct layout', function () {
        $component = new SupportPage();
        $reflection = new ReflectionClass($component);

        $layoutAttributes = $reflection->getAttributes(\Livewire\Attributes\Layout::class);
        expect($layoutAttributes)->toHaveCount(1);

        $layoutAttribute = $layoutAttributes[0];
        expect($layoutAttribute->getArguments()[0])->toBe('livewire.public.main-layout');
    });

    test('is a valid Livewire component', function () {
        $component = new SupportPage();
        expect($component)->toBeInstanceOf(\Livewire\Component::class);
    });

    test('render method returns correct view', function () {
        $component = new SupportPage();
        $view = $component->render();

        expect($view->getName())->toBe('livewire.public.support-page');
    });
});
