<?php

namespace App\Jobs;

use App\Models\ExternalSyncLog;
use App\Services\ExternalSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncEntityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes
    public int $tries = 3;

    public function __construct(
        public ExternalSyncLog $syncLog
    ) {}

    public function handle(ExternalSyncService $syncService): void
    {
        Log::info('Début du job de synchronisation', ['sync_id' => $this->syncLog->id]);

        $success = $syncService->executSync($this->syncLog);

        if ($success) {
            Log::info('Job de synchronisation terminé avec succès', ['sync_id' => $this->syncLog->id]);
        } else {
            Log::error('Échec du job de synchronisation', ['sync_id' => $this->syncLog->id]);
            
            // Tentative de retry si possible
            if ($this->syncLog->canRetry()) {
                Log::info('Tentative de retry programmée', ['sync_id' => $this->syncLog->id]);
                SyncEntityJob::dispatch($this->syncLog)->delay(now()->addMinutes(5));
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Job de synchronisation échoué', [
            'sync_id' => $this->syncLog->id,
            'error' => $exception->getMessage()
        ]);

        $this->syncLog->update([
            'status' => \App\Enums\SyncStatus::FAILED,
            'error_message' => $exception->getMessage(),
            'completed_at' => now(),
        ]);
    }
}