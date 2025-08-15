<?php

use App\Providers\EventServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FilamentNavigationServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
    EventServiceProvider::class,
];
