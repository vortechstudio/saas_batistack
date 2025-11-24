<?php
use Illuminate\Support\Facades\Route;

Route::prefix('administration')->group(function () {
    Route::get('/', \App\Livewire\Admin\Dashboard::class)->name('admin.dashboard');
})->middleware('email: admin@batistack.test, admin@batistack.ovh, contact@batistack.ovh');
