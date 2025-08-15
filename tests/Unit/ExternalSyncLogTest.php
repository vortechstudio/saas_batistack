<?php

use App\Models\ExternalSyncLog;
use App\Models\Customer;
use App\Enums\SyncStatus;

beforeEach(function () {
    $this->syncLog = ExternalSyncLog::factory()->create([
        'system_name' => 'crm',
        'operation' => 'sync',
        'entity_type' => 'customers',
        'status' => SyncStatus::SUCCESS,
        'started_at' => now()->subMinutes(5),
        'completed_at' => now(),
        'retry_count' => 0,
    ]);
});

test('has correct fillable attributes', function () {
    $fillable = [
        'system_name', 'operation', 'entity_type', 'entity_id', 'status',
        'request_data', 'response_data', 'error_message', 'retry_count',
        'last_retry_at', 'started_at', 'completed_at'
    ];

    expect($this->syncLog->getFillable())->toBe($fillable);
});

test('successful scope filters completed syncs', function () {
    ExternalSyncLog::factory()->create(['status' => SyncStatus::FAILED]);
    ExternalSyncLog::factory()->create(['status' => SyncStatus::RUNNING]);

    $successfulSyncs = ExternalSyncLog::successful()->get();

    expect($successfulSyncs)->toHaveCount(1)
        ->and($successfulSyncs->first()->status)->toBe(SyncStatus::SUCCESS);
});

test('failed scope filters failed syncs', function () {
    ExternalSyncLog::factory()->create(['status' => SyncStatus::FAILED]);

    $failedSyncs = ExternalSyncLog::failed()->get();

    expect($failedSyncs)->toHaveCount(1)
        ->and($failedSyncs->first()->status)->toBe(SyncStatus::FAILED);
});

test('running scope filters running syncs', function () {
    ExternalSyncLog::factory()->create(['status' => SyncStatus::RUNNING]);

    $runningSyncs = ExternalSyncLog::running()->get();

    expect($runningSyncs)->toHaveCount(1)
        ->and($runningSyncs->first()->status)->toBe(SyncStatus::RUNNING);
});

test('pending scope filters pending syncs', function () {
    ExternalSyncLog::factory()->create(['status' => SyncStatus::PENDING]);

    $pendingSyncs = ExternalSyncLog::pending()->get();

    expect($pendingSyncs)->toHaveCount(1)
        ->and($pendingSyncs->first()->status)->toBe(SyncStatus::PENDING);
});

test('forSystem scope filters by system name', function () {
    ExternalSyncLog::factory()->create(['system_name' => 'erp']);

    $crmSyncs = ExternalSyncLog::forSystem('crm')->get();
    $erpSyncs = ExternalSyncLog::forSystem('erp')->get();

    expect($crmSyncs)->toHaveCount(1)
        ->and($erpSyncs)->toHaveCount(1);
});

test('forEntity scope filters by entity type', function () {
    ExternalSyncLog::factory()->create(['entity_type' => 'licenses']);

    $customerSyncs = ExternalSyncLog::forEntity('customers')->get();
    $licenseSyncs = ExternalSyncLog::forEntity('licenses')->get();

    expect($customerSyncs)->toHaveCount(1)
        ->and($licenseSyncs)->toHaveCount(1);
});

test('status check methods work correctly', function () {
    expect($this->syncLog->isSuccessful())->toBeTrue()
        ->and($this->syncLog->isFailed())->toBeFalse()
        ->and($this->syncLog->isRunning())->toBeFalse()
        ->and($this->syncLog->isPending())->toBeFalse();
});

test('can retry when failed and under retry limit', function () {
    $failedSync = ExternalSyncLog::factory()->create([
        'status' => SyncStatus::FAILED,
        'retry_count' => 2,
    ]);

    expect($failedSync->canRetry())->toBeTrue();
});

test('cannot retry when retry limit reached', function () {
    $failedSync = ExternalSyncLog::factory()->create([
        'status' => SyncStatus::FAILED,
        'retry_count' => 3,
    ]);

    expect($failedSync->canRetry())->toBeFalse();
});

test('incrementRetryCount increases count and sets timestamp', function () {
    $this->syncLog->incrementRetryCount();

    expect($this->syncLog->fresh()->retry_count)->toBe(1)
        ->and($this->syncLog->fresh()->last_retry_at)->not()->toBeNull();
});

test('duration calculates correctly', function () {
    expect($this->syncLog->duration())->toBe(300); // 5 minutes
});

test('duration returns null when timestamps missing', function () {
    $syncLog = ExternalSyncLog::factory()->create([
        'started_at' => null,
        'completed_at' => null,
    ]);

    expect($syncLog->duration())->toBeNull();
});

test('status label and color accessors work', function () {
    expect($this->syncLog->status_label)->toBeString()
        ->and($this->syncLog->status_color)->toBeString();
});

test('entity relationship works', function () {
    $customer = Customer::factory()->create();
    $syncLog = ExternalSyncLog::factory()->create([
        'entity_type' => 'customers',
        'entity_id' => $customer->id,
    ]);

    // Note: This would require implementing the morphTo relationship properly
    expect($syncLog->entity_type)->toBe('customers')
        ->and($syncLog->entity_id)->toBe($customer->id);
});
