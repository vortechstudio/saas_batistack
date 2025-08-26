<?php

use App\Livewire\Client\Account\Dashboard as AccountDashboard;
use App\Livewire\Client\Dashboard;
use App\Models\Customer\Customer;
use App\Services\Stripe\CustomerService;
use App\Services\Stripe\ProductService;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/test', function () {
    $customer = Customer::find(1);
    $methods = app(CustomerService::class)->listPaymentMethods($customer);
    dd($customer->support_type->color());
});

Route::prefix('client')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('client.dashboard');

    Route::prefix('account')->group(function () {
        Route::get('/', AccountDashboard::class)->name('client.account.dashboard');
        Route::get('/invoice', \App\Livewire\Client\Account\Invoice::class)->name('client.account.invoice');
        Route::get('/method-payment', \App\Livewire\Client\Account\MethodPayment::class)->name('client.account.method-payment');
        Route::get('/orders', \App\Livewire\Client\Account\Orders::class)->name('client.account.orders');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
