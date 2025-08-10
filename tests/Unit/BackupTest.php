<?php

use App\Models\Backup;
use App\Enums\BackupStatus;
use App\Enums\BackupType;

beforeEach(function () {
    $this->backup = Backup::factory()->create([
        'name' => 'test-backup',
        'type' => BackupType::FULL,
        'status' => BackupStatus::COMPLETED,
        'storage_driver' => 'local',
        'file_path' => 'backups/test-backup.zip',
        'file_size' => 1048576, // 1MB
        'started_at' => now()->subMinutes(10),
        'completed_at' => now(),
    ]);
});

describe('Backup Model', function () {
    test('can create a backup', function () {
        expect($this->backup)->toBeInstanceOf(Backup::class)
            ->and($this->backup->name)->toBe('test-backup')
            ->and($this->backup->type)->toBe(BackupType::FULL)
            ->and($this->backup->status)->toBe(BackupStatus::COMPLETED);
    });

    test('has correct fillable attributes', function () {
        $fillable = [
            'name', 'type', 'status', 'storage_driver', 'file_path',
            'file_size', 'metadata', 'error_message', 'started_at', 'completed_at'
        ];
        
        expect($this->backup->getFillable())->toBe($fillable);
    });

    test('casts attributes correctly', function () {
        expect($this->backup->type)->toBeInstanceOf(BackupType::class)
            ->and($this->backup->status)->toBeInstanceOf(BackupStatus::class)
            ->and($this->backup->started_at)->toBeInstanceOf(\Carbon\Carbon::class)
            ->and($this->backup->completed_at)->toBeInstanceOf(\Carbon\Carbon::class)
            ->and($this->backup->file_size)->toBe(1048576);
    });

    test('successful scope filters completed backups', function () {
        Backup::factory()->create(['status' => BackupStatus::FAILED]);
        Backup::factory()->create(['status' => BackupStatus::RUNNING]);
        
        $successfulBackups = Backup::successful()->get();
        
        expect($successfulBackups)->toHaveCount(1)
            ->and($successfulBackups->first()->status)->toBe(BackupStatus::COMPLETED);
    });

    test('failed scope filters failed backups', function () {
        Backup::factory()->create(['status' => BackupStatus::FAILED]);
        
        $failedBackups = Backup::failed()->get();
        
        expect($failedBackups)->toHaveCount(1)
            ->and($failedBackups->first()->status)->toBe(BackupStatus::FAILED);
    });

    test('running scope filters running backups', function () {
        Backup::factory()->create(['status' => BackupStatus::RUNNING]);
        
        $runningBackups = Backup::running()->get();
        
        expect($runningBackups)->toHaveCount(1)
            ->and($runningBackups->first()->status)->toBe(BackupStatus::RUNNING);
    });

    test('isCompleted returns correct boolean', function () {
        expect($this->backup->isCompleted())->toBeTrue();
        
        $runningBackup = Backup::factory()->create(['status' => BackupStatus::RUNNING]);
        expect($runningBackup->isCompleted())->toBeFalse();
    });

    test('isFailed returns correct boolean', function () {
        expect($this->backup->isFailed())->toBeFalse();
        
        $failedBackup = Backup::factory()->create(['status' => BackupStatus::FAILED]);
        expect($failedBackup->isFailed())->toBeTrue();
    });

    test('isRunning returns correct boolean', function () {
        expect($this->backup->isRunning())->toBeFalse();
        
        $runningBackup = Backup::factory()->create(['status' => BackupStatus::RUNNING]);
        expect($runningBackup->isRunning())->toBeTrue();
    });

    test('duration calculates correct duration', function () {
        expect($this->backup->duration())->toBe(600); // 10 minutes = 600 seconds
    });

    test('duration returns null when dates are missing', function () {
        $backup = Backup::factory()->create([
            'started_at' => null,
            'completed_at' => null,
        ]);
        
        expect($backup->duration())->toBeNull();
    });

    test('formattedFileSize returns formatted size', function () {
        expect($this->backup->formatted_file_size)->toBe('1 MB');
        
        $smallBackup = Backup::factory()->create(['file_size' => 1024]);
        expect($smallBackup->formatted_file_size)->toBe('1 KB');
        
        $largeBackup = Backup::factory()->create(['file_size' => 1073741824]);
        expect($largeBackup->formatted_file_size)->toBe('1 GB');
    });

    test('formattedFileSize returns null when file_size is null', function () {
        $backup = Backup::factory()->create(['file_size' => null]);
        
        expect($backup->formatted_file_size)->toBeNull();
    });

    test('getFullPath returns correct path', function () {
        $expectedPath = storage_path('app/backups/' . $this->backup->file_path);
        
        expect($this->backup->getFullPath())->toBe($expectedPath);
    });

    test('getFullPath returns null when file_path is null', function () {
        $backup = Backup::factory()->create(['file_path' => null]);
        
        expect($backup->getFullPath())->toBeNull();
    });

    test('fileExists checks file existence', function () {
        // Since we're testing, the file won't actually exist
        expect($this->backup->fileExists())->toBeFalse();
    });
});