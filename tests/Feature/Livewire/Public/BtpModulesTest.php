<?php

use App\Livewire\Public\ComptabiliteBtpPage;
use App\Livewire\Public\DevisMetresPage;
use App\Livewire\Public\FacturationBtpPage;
use App\Livewire\Public\GestionChantierPage;
use App\Livewire\Public\GestionStockPage;
use App\Livewire\Public\PlanningResourcesPage;
use Livewire\Livewire;

describe('BTP Modules Components', function () {
    test('ComptabiliteBtpPage renders successfully', function () {
        Livewire::test(ComptabiliteBtpPage::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.public.comptabilite-btp-page');
    });

    test('DevisMetresPage renders successfully', function () {
        Livewire::test(DevisMetresPage::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.public.devis-metres-page');
    });

    test('FacturationBtpPage renders successfully', function () {
        Livewire::test(FacturationBtpPage::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.public.facturation-btp-page');
    });

    test('GestionChantierPage renders successfully', function () {
        Livewire::test(GestionChantierPage::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.public.gestion-chantier-page');
    });

    test('GestionStockPage renders successfully', function () {
        Livewire::test(GestionStockPage::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.public.gestion-stock-page');
    });

    test('PlanningResourcesPage renders successfully', function () {
        Livewire::test(PlanningResourcesPage::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.public.planning-resources-page');
    });

    test('all BTP modules have correct titles', function () {
        $modules = [
            ComptabiliteBtpPage::class => 'Comptabilité BTP - Batistack',
            DevisMetresPage::class => 'Devis & Métrés - Batistack',
            FacturationBtpPage::class => 'Facturation BTP - Batistack',
            GestionChantierPage::class => 'Gestion de Chantier - Batistack',
            GestionStockPage::class => 'Gestion des Stocks - Batistack',
            PlanningResourcesPage::class => 'Planning & Resources - Batistack',
        ];

        foreach ($modules as $componentClass => $expectedTitle) {
            $component = new $componentClass();
            $reflection = new ReflectionClass($component);
            $titleAttribute = $reflection->getAttributes(\Livewire\Attributes\Title::class)[0] ?? null;

            expect($titleAttribute?->getArguments()[0])
                ->toBe($expectedTitle, "Title mismatch for {$componentClass}");
        }
    });

    test('all BTP modules use correct layout', function () {
        $modules = [
            ComptabiliteBtpPage::class,
            DevisMetresPage::class,
            FacturationBtpPage::class,
            GestionChantierPage::class,
            GestionStockPage::class,
            PlanningResourcesPage::class,
        ];

        foreach ($modules as $componentClass) {
            $component = new $componentClass();
            $reflection = new ReflectionClass($component);
            $layoutAttribute = $reflection->getAttributes(\Livewire\Attributes\Layout::class)[0] ?? null;

            expect($layoutAttribute?->getArguments()[0])
                ->toBe('livewire.public.main-layout', "Layout mismatch for {$componentClass}");
        }
    });
});
