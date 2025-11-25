<?php
use Illuminate\Support\Facades\Route;

Route::prefix('administration')->middleware(['auth'])->group(function () {
    Route::get('/', \App\Livewire\Admin\Dashboard::class)->name('admin.dashboard');

    Route::prefix('commerce')->group(function () {
        Route::get('/customers', \App\Livewire\Admin\Commerce\Customer\ListCustomer::class)->name('admin.commerce.customers');
        Route::get('/customers/{customer}', \App\Livewire\Admin\Commerce\Customer\ShowCustomer::class)->name('admin.commerce.customers.show');
    });
})->middleware('email: admin@batistack.test, admin@batistack.ovh, contact@batistack.ovh');
