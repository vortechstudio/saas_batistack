<?php

use App\Http\Controllers\Api\LicenseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});
Route::prefix('license')->group(function() {
    Route::get('/validate', [LicenseController::class, 'validate']);
    Route::get('/info', [LicenseController::class, 'info']);
});
