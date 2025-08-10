<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600; // 1 heure
    public int $tries = 3;

    public function __construct(
        public Backup $backup
    ) {}

    public function handle(BackupService $backupService): void
    {
        Log::info('Début du job de sauvegarde', ['backup_id' => $this->backup->id]);

        $success = $backupService->executeBackup($this->backup);

        if ($success) {
            Log::info('Job de sauvegarde terminé avec succès', ['backup_id' => $this->backup->id]);
        } else {
            Log::error('Échec du job de sauvegarde', ['backup_id' => $this->backup->id]);
            $this->fail('Échec de la création de la sauvegarde');
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Job de sauvegarde échoué', [
            'backup_id' => $this->backup->id,
            'error' => $exception->getMessage()
        ]);

        $this->backup->update([
            'status' => \App\Enums\BackupStatus::FAILED,
            'error_message' => $exception->getMessage(),
            'completed_at' => now(),
        ]);
    }
}