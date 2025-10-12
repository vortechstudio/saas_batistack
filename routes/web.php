<?php

use App\Livewire\Client\Account\Dashboard as AccountDashboard;
use App\Livewire\Client\Account\Sauvegardes;
use App\Livewire\Client\Account\Service;
use App\Livewire\Client\Account\ServiceShow;
use App\Livewire\Client\Dashboard;
use App\Services\VitoDeploy\Vito;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/test', function () {
    $vito = app(Vito::class)
        ->get('/projects/1/servers');

    dd($vito);
});

Route::prefix('client')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('client.dashboard');

    Route::prefix('account')->group(function () {
        Route::get('/', AccountDashboard::class)->name('client.account.dashboard');
        Route::get('/invoice', \App\Livewire\Client\Account\Invoice::class)->name('client.account.invoice');
        Route::get('/method-payment', \App\Livewire\Client\Account\MethodPayment::class)->name('client.account.method-payment');
        Route::get('/orders', \App\Livewire\Client\Account\Orders::class)->name('client.account.orders');
        Route::get('/order/{id}', \App\Livewire\Client\Account\OrderShow::class)->name('client.account.order.show');


        Route::get('/cart', \App\Livewire\Client\Account\CartIndex::class)->name('client.account.cart.index');
        Route::get('/cart/license', \App\Livewire\Client\Account\CartLicense::class)->name('client.account.cart.license');
        Route::get('/cart/module', \App\Livewire\Client\Account\CartModule::class)->name('client.account.cart.module');
        Route::get('/cart/option', \App\Livewire\Client\Account\CartOption::class)->name('client.account.cart.option');
    });

    Route::prefix('services')->group(function () {
        Route::get('/', Service::class)->name('client.services');
        Route::get('/{service_code}', ServiceShow::class)->name('client.service.show');
        Route::get('/sauvegardes', Sauvegardes::class)->name('client.service.sauvegardes');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
