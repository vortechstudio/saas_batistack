<?php

use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\BackupController;
use App\Http\Controllers\Api\ModuleController;
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
    Route::prefix('modules')->group(function () {
        Route::get('/{slug}', [LicenseController::class, 'moduleInfo']);
        Route::get('/{slug}/activate', [LicenseController::class, 'moduleActivate']);
        Route::get('/{slug}/deactivate', [LicenseController::class, 'moduleDeactivate']);
    });
});

Route::prefix('version')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\VersionController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\Api\VersionController::class, 'create']);
    Route::get('/{version}', [\App\Http\Controllers\Api\VersionController::class, 'show']);

});

Route::prefix('backup')->group(function () {
    Route::get('/', [BackupController::class, 'list']);
    Route::post('/', [BackupController::class, 'store']);
});

Route::prefix('modules')->group(function () {
    Route::get('/', [ModuleController::class, 'list']);
    Route::get('/{module_slug}', [ModuleController::class, 'info']);
});
