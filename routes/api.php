<?php

use App\Http\Controllers\Api\LicenseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\StripeWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Stripe Webhook Route (DOIT être accessible sans authentification)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->name('stripe.webhook');

// Routes pour les notifications (protégées par auth)
Route::middleware(['auth:web'])->prefix('admin')->group(function () {
    Route::get('/notifications/count', [NotificationController::class, 'getNotificationCount'])
        ->name('api.notifications.count');

    Route::get('/notifications', [NotificationController::class, 'getNotifications'])
        ->name('api.notifications.index');

    Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])
        ->name('api.notifications.mark-read');

    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
        ->name('api.notifications.mark-all-read');
});

Route::prefix('license')->group(function () {
    Route::get('validate', [LicenseController::class, 'validate']);
    Route::get('info', [LicenseController::class, 'info']);
});

Route::prefix('modules')->group(function () {
    Route::get('/list');
});
