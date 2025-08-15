<?php

use App\Jobs\SyncEntityJob;
use App\Models\ExternalSyncLog;
use App\Services\ExternalSyncService;
use App\Enums\SyncStatus;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    Log::spy();
});

test('handles sync successfully', function () {
    $syncLog = ExternalSyncLog::factory()->create([
        'status' => SyncStatus::PENDING,
    ]);

    $mockSyncService = Mockery::mock(ExternalSyncService::class);
    $mockSyncService->shouldReceive('executeSync')
        ->with($syncLog)
        ->once()
        ->andReturn(true);

    $this->app->instance(ExternalSyncService::class, $mockSyncService);

    $job = new SyncEntityJob($syncLog);
    $job->handle($mockSyncService);

    Log::shouldHaveReceived('info')
        ->with('Début du job de synchronisation', ['sync_id' => $syncLog->id]);

    Log::shouldHaveReceived('info')
        ->with('Job de synchronisation terminé avec succès', ['sync_id' => $syncLog->id]);
});

test('handles sync failure', function () {
    $syncLog = ExternalSyncLog::factory()->create([
        'status' => SyncStatus::PENDING,
    ]);

    $mockSyncService = Mockery::mock(ExternalSyncService::class);
    $mockSyncService->shouldReceive('executeSync')
        ->with($syncLog)
        ->once()
        ->andReturn(false);

    $this->app->instance(ExternalSyncService::class, $mockSyncService);

    $job = new SyncEntityJob($syncLog);
    $job->handle($mockSyncService);

    Log::shouldHaveReceived('error')
        ->with('Échec du job de synchronisation', ['sync_id' => $syncLog->id]);
});

test('handles exceptions during sync', function () {
    $syncLog = ExternalSyncLog::factory()->create([
        'status' => SyncStatus::PENDING,
    ]);

    $mockSyncService = Mockery::mock(ExternalSyncService::class);
    $mockSyncService->shouldReceive('executeSync')
        ->with($syncLog)
        ->once()
        ->andThrow(new Exception('Sync error'));

    $this->app->instance(ExternalSyncService::class, $mockSyncService);

    $job = new SyncEntityJob($syncLog);

    expect(fn() => $job->handle($mockSyncService))->toThrow(Exception::class);

    // The exception will be caught by Laravel's queue system and the failed() method will be called
    // We need to manually call failed() to test the logging behavior
    $job->failed(new Exception('Sync error'));

    Log::shouldHaveReceived('error')
        ->with('Job de synchronisation échoué', [
            'sync_id' => $syncLog->id,
            'error' => 'Sync error'
        ]);

    // Verify the sync log was updated
    expect($syncLog->fresh()->status)->toBe(SyncStatus::FAILED)
        ->and($syncLog->fresh()->error_message)->toBe('Sync error')
        ->and($syncLog->fresh()->completed_at)->not()->toBeNull();
});

test('has correct timeout and retry settings', function () {
    $syncLog = ExternalSyncLog::factory()->create();
    $job = new SyncEntityJob($syncLog);

    expect($job->timeout)->toBe(300)
        ->and($job->tries)->toBe(3);
});
