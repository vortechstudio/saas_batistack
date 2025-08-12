<?php

use App\Http\Controllers\InvoicePdfController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Route dashboard avec redirection conditionnelle
Route::get('dashboard', function () {
    $user = Auth::user();
    
    if (!$user) {
        return redirect()->route('login');
    }
    
    // Rediriger selon le rôle
    if ($user->isAdmin() || $user->hasAnyRole(['Super Admin', 'Admin', 'Manager'])) {
        return redirect()->route('filament.admin.pages.dashboard');
    } else {
        return redirect()->route('client.dashboard');
    }
})->middleware(['auth', 'verified', 'two.factor'])->name('dashboard');

Route::middleware(['auth', 'two.factor'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
    
    // Route pour télécharger les PDF de factures
    Route::get('invoice/{invoice}/pdf', [InvoicePdfController::class, 'download'])->name('invoice.pdf');
});

// Routes client
Route::middleware(['auth', 'verified', 'two.factor'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', App\Livewire\Client\Dashboard::class)->name('dashboard');
    Route::get('/licenses', App\Livewire\Client\Licenses::class)->name('licenses');
    Route::get('/invoices', function() { return 'Factures à venir'; })->name('invoices');
    Route::get('/support', function() { return 'Support à venir'; })->name('support');
});

require __DIR__.'/auth.php';
