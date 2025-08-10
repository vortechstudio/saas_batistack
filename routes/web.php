<?php

use App\Http\Controllers\InvoicePdfController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'two.factor'])
    ->name('dashboard');

Route::middleware(['auth', 'two.factor'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
    
    // Route pour télécharger les PDF de factures
    Route::get('invoice/{invoice}/pdf', [InvoicePdfController::class, 'download'])->name('invoice.pdf');
});

require __DIR__.'/auth.php';
