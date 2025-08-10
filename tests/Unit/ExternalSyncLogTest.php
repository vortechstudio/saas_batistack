<?php

use App\Models\ExternalSyncLog;
use App\Enums\SyncStatus;

beforeEach(function () {
    $this->syncLog = ExternalSyncLog::factory()->create([
        'entity_type' => 'Customer',
        'entity_id' => 1,
        'external_id' => 'ext_123',
        'action' => 'create',
        'status' => SyncStatus::SUCCESS,
        'request_data' => ['name' => 'Test Customer'],
        'response_data' => ['id' => 'ext_123', 'status' => 'created'],
        'error_message' => null,
        'synced_at' => now(),
    ]);
});

describe('ExternalSyncLog Model', function () {
    test('can create a sync log', function () {
        expect($this->syncLog)->toBeInstanceOf(ExternalSyncLog::class)
            ->and($this->syncLog->entity_type)->toBe('Customer')
            ->and($this->syncLog->entity_id)->toBe(1)
            ->and($this->syncLog->external_id)->toBe('ext_123')
            ->and($this->syncLog->action)->toBe('create');
    });

    test('has correct fillable attributes', function () {
        $fillable = [
            'entity_type', 'entity_id', 'external_id', 'action', 'status',
            'request_data', 'response_data', 'error_message', 'synced_at'
        ];
        
        expect($this->syncLog->getFillable())->toBe($fillable);
    });

    test('casts attributes correctly', function () {
        expect($this->syncLog->status)->toBeInstanceOf(SyncStatus::class)
            ->and($this->syncLog->request_data)->toBeArray()
            ->and($this->syncLog->response_data)->toBeArray()
            ->and($this->syncLog->synced_at)->toBeInstanceOf(\Carbon\Carbon::class);
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
            'entity_type' => 'Product',
            'entity_id' => 2
        ]);
        
        $customerLogs = ExternalSyncLog::forEntity('Customer', 1)->get();
        
        expect($customerLogs)->toHaveCount(1)
            ->and($customerLogs->first()->entity_type)->toBe('Customer')
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