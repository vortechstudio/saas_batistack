<?php

use App\Models\ExternalSyncLog;
use App\Enums\SyncStatus;

beforeEach(function () {
    $this->syncLog = ExternalSyncLog::factory()->create([
        'system_name' => 'external_api',
        'operation' => 'sync',
        'entity_type' => 'customers',
        'entity_id' => 1,
        'status' => SyncStatus::SUCCESS,
        'request_data' => ['name' => 'Test Customer'],
        'response_data' => ['id' => 'ext_123', 'status' => 'created'],
        'error_message' => null,
        'started_at' => now()->subMinutes(5),
        'completed_at' => now(),
    ]);
});

describe('ExternalSyncLog Model', function () {
    test('can create a sync log', function () {
        expect($this->syncLog)->toBeInstanceOf(ExternalSyncLog::class)
            ->and($this->syncLog->system_name)->toBe('external_api')
            ->and($this->syncLog->operation)->toBe('sync')
            ->and($this->syncLog->entity_type)->toBe('customers')
            ->and($this->syncLog->entity_id)->toBe(1);
    });

    test('has correct fillable attributes', function () {
        $fillable = [
            'system_name', 'operation', 'entity_type', 'entity_id', 'status',
            'request_data', 'response_data', 'error_message', 'retry_count',
            'last_retry_at', 'started_at', 'completed_at'
        ];
        
        expect($this->syncLog->getFillable())->toBe($fillable);
    });

    test('casts attributes correctly', function () {
        expect($this->syncLog->status)->toBeInstanceOf(SyncStatus::class)
            ->and($this->syncLog->request_data)->toBeArray()
            ->and($this->syncLog->response_data)->toBeArray()
            ->and($this->syncLog->retry_count)->toBeInt()
            ->and($this->syncLog->entity_id)->toBeInt()
            ->and($this->syncLog->started_at)->toBeInstanceOf(\Carbon\Carbon::class)
            ->and($this->syncLog->completed_at)->toBeInstanceOf(\Carbon\Carbon::class);
    });

    test('successful scope filters successful syncs', function () {
        ExternalSyncLog::factory()->create(['status' => SyncStatus::FAILED]);
        ExternalSyncLog::factory()->create(['status' => SyncStatus::PENDING]);
        
        $successfulLogs = ExternalSyncLog::successful()->get();
        
        expect($successfulLogs)->toHaveCount(1)
            ->and($successfulLogs->first()->status)->toBe(SyncStatus::SUCCESS);
    });

    test('failed scope filters failed syncs', function () {
        ExternalSyncLog::factory()->create(['status' => SyncStatus::FAILED]);
        
        $failedLogs = ExternalSyncLog::failed()->get();
        
        expect($failedLogs)->toHaveCount(1)
            ->and($failedLogs->first()->status)->toBe(SyncStatus::FAILED);
    });

    test('pending scope filters pending syncs', function () {
        ExternalSyncLog::factory()->create(['status' => SyncStatus::PENDING]);
        
        $pendingLogs = ExternalSyncLog::pending()->get();
        
        expect($pendingLogs)->toHaveCount(1)
            ->and($pendingLogs->first()->status)->toBe(SyncStatus::PENDING);
    });

    test('forEntity scope filters by entity', function () {
        ExternalSyncLog::factory()->create([
            'entity_type' => 'products',
            'entity_id' => 2
        ]);
        
        $customerLogs = ExternalSyncLog::forEntity('customers', 1)->get();
        
        expect($customerLogs)->toHaveCount(1)
            ->and($customerLogs->first()->entity_type)->toBe('customers')
            ->and($customerLogs->first()->entity_id)->toBe(1);
    });

    test('isSuccessful returns correct boolean', function () {
        expect($this->syncLog->isSuccessful())->toBeTrue();
        
        $failedLog = ExternalSyncLog::factory()->create(['status' => SyncStatus::FAILED]);
        expect($failedLog->isSuccessful())->toBeFalse();
    });

    test('isFailed returns correct boolean', function () {
        expect($this->syncLog->isFailed())->toBeFalse();
        
        $failedLog = ExternalSyncLog::factory()->create(['status' => SyncStatus::FAILED]);
        expect($failedLog->isFailed())->toBeTrue();
    });

    test('isPending returns correct boolean', function () {
        expect($this->syncLog->isPending())->toBeFalse();
        
        $pendingLog = ExternalSyncLog::factory()->create(['status' => SyncStatus::PENDING]);
        expect($pendingLog->isPending())->toBeTrue();
    });

    test('getStatusLabelAttribute returns status label', function () {
        expect($this->syncLog->status_label)->toBe($this->syncLog->status->label());
    });

    test('getStatusColorAttribute returns status color', function () {
        expect($this->syncLog->status_color)->toBe($this->syncLog->status->color());
    });
});