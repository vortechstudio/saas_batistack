<?php

use App\Livewire\Client\Account\Dashboard as AccountDashboard;
use App\Livewire\Client\Account\Service;
use App\Livewire\Client\Account\ServiceShow;
use App\Livewire\Client\Dashboard;
use App\Models\Customer\Customer;
use App\Models\Product\Product;
use App\Services\Panel\PanelService;
use App\Services\PanelService as ServicesPanelService;
use App\Services\Stripe\CustomerService;
use App\Services\Stripe\ProductService;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/test', function () {
    $db = app(ServicesPanelService::class)->addDatabase('db_c2me', 'db_c2me');
    dd($db);
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
    });

    Route::prefix('services')->group(function () {
        Route::get('/', Service::class)->name('client.services');
        Route::get('/{service_code}', ServiceShow::class)->name('client.service.show');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
