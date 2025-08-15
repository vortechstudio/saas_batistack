<?php

use App\Livewire\Public\MainLayout;
use Livewire\Livewire;

describe('MainLayout Component', function () {
    test('renders main layout successfully', function () {
        Livewire::test(MainLayout::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.public.main-layout');
    });

    test('is a valid Livewire component', function () {
        $component = new MainLayout();
        expect($component)->toBeInstanceOf(\Livewire\Component::class);
    });

    test('render method returns correct view', function () {
        $component = new MainLayout();
        $view = $component->render();

        expect($view->getName())->toBe('livewire.public.main-layout');
    });
});