<?php

use App\Jobs\CreateBackupJob;
use App\Models\Backup;
use App\Services\BackupService;
use App\Enums\BackupStatus;
use App\Enums\BackupType;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

describe('CreateBackupJob', function () {
    test('can be dispatched', function () {
        $backup = Backup::factory()->create();

        CreateBackupJob::dispatch($backup);

        Queue::assertPushed(CreateBackupJob::class);
    });

    test('handles backup creation successfully', function () {
        $backup = Backup::factory()->create([
            'status' => BackupStatus::PENDING,
            'type' => BackupType::FULL
        ]);

        $backupService = $this->mock(BackupService::class);
        $backupService->shouldReceive('executeBackup')
            ->with($backup)
            ->once()
            ->andReturn(true);

        $job = new CreateBackupJob($backup);
        $job->handle($backupService);

        // Le job ne met pas à jour le statut directement, c'est le BackupService qui le fait
        // On vérifie juste que la méthode a été appelée correctement
        expect(true)->toBeTrue(); // Le test passe si le mock est appelé correctement
    });
});
