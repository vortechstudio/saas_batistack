<?php

use App\Services\BackupService;
use App\Models\Backup;
use App\Enums\BackupStatus;
use App\Enums\BackupType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    Storage::fake('backups');
    $this->backupService = new BackupService();
});

describe('BackupService', function () {
    test('can create full backup', function () {
        $result = $this->backupService->createBackup(BackupType::FULL, 'local', ['test' => true]);

        expect($result)->toBeInstanceOf(Backup::class)
            ->and($result->type)->toBe(BackupType::FULL)
            ->and($result->status)->toBe(BackupStatus::PENDING)
            ->and($result->storage_driver)->toBe('local');
    });

    test('can create incremental backup', function () {
        $result = $this->backupService->createBackup(BackupType::INCREMENTAL, 'local', ['test' => true]);

        expect($result)->toBeInstanceOf(Backup::class)
            ->and($result->type)->toBe(BackupType::INCREMENTAL)
            ->and($result->status)->toBe(BackupStatus::PENDING);
    });

    test('can execute backup successfully', function () {
        // Mock les requêtes SQL spécifiques à SQLite
        DB::shouldReceive('select')
            ->with("SELECT name FROM sqlite_master WHERE type='table'")
            ->andReturn([
                (object)['name' => 'users'],
                (object)['name' => 'customers']
            ]);

        DB::shouldReceive('select')
            ->with(\Mockery::pattern("/SELECT sql FROM sqlite_master WHERE type='table' AND name='/"))
            ->andReturn([
                (object)['sql' => 'CREATE TABLE users (...)']
            ]);

        DB::shouldReceive('getSchemaBuilder->hasTable')
            ->andReturn(true);

        DB::shouldReceive('table')
            ->andReturn(\Mockery::mock()
                ->shouldReceive('get')
                ->andReturn(collect([]))
                ->getMock()
            );

        $backup = $this->backupService->createBackup(BackupType::FULL, 'local', ['test' => true]);

        $result = $this->backupService->executeBackup($backup);

        expect($result)->toBeTrue()
            ->and($backup->fresh()->status)->toBe(BackupStatus::COMPLETED);
    });

    test('handles backup failure gracefully', function () {
        $backup = $this->backupService->createBackup(BackupType::FULL, 'local', ['test' => true]);

        // Simuler une erreur en mockant DB pour lancer une exception lors de l'export du schéma
        DB::shouldReceive('select')
            ->with('SHOW TABLES')
            ->andThrow(new Exception('Database connection error'));

        $result = $this->backupService->executeBackup($backup);

        expect($result)->toBeFalse()
            ->and($backup->fresh()->status)->toBe(BackupStatus::FAILED);
    });
});
