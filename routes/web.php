<?php

use App\Http\Controllers\InvoicePdfController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Stripe\StripeClient;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/test', function() {
    $stripe = new StripeClient(config('services.stripe.secret'));
    $products = $stripe->products->all(['limit' => 100]);
    $pp = collect($products)->filter(function ($prod) {
        return $prod->metadata->type === 'main_product';
    })->groupBy('metadata.product_id')->reverse()->toArray();

    foreach ($pp as $module) {
        $prices = $stripe->prices->all(['product' => $module[0]['id']]);
        //dd($prices['data']);
    }

    dd($products, $pp);
});

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
    Route::get('/licenses/{license}/certificate', [App\Http\Controllers\LicensePdfController::class, 'download'])
        ->name('license.certificate');
    // Remplacer la ligne 45 par :
    Route::get('/invoices', App\Livewire\Client\Invoices::class)->name('invoices');
    Route::get('/support', function() { return 'Support à venir'; })->name('support');

    // Nouvelles routes pour la commande
    Route::get('/order', App\Livewire\Client\OrderLicense::class)->name('order');
    Route::get('/order/success/{invoice}', function($invoiceId) {
        $invoice = \App\Models\Invoice::findOrFail($invoiceId);
        return view('client.order-success', compact('invoice'));
    })->name('order.success');
    Route::get('/order/cancel/{invoice}', function($invoiceId) {
        $invoice = \App\Models\Invoice::findOrFail($invoiceId);
        return view('client.order-cancel', compact('invoice'));
    })->name('order.cancel');
});

require __DIR__.'/auth.php';
