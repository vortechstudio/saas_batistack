<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;

class FilamentNavigationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Configuration des raccourcis clavier
        $this->configureKeyboardShortcuts();
    }

    protected function configureKeyboardShortcuts(): void
    {
        // Ajouter les raccourcis clavier dans le layout
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn () => view('filament.components.keyboard-shortcuts')
        );
    }
}