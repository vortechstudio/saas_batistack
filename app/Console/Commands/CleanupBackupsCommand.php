<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class CleanupBackupsCommand extends Command
{
    protected $signature = 'backup:cleanup 
                            {--days=30 : Nombre de jours à conserver}
                            {--force : Forcer le nettoyage sans confirmation}';

    protected $description = 'Nettoie les anciennes sauvegardes';

    public function handle(BackupService $backupService): int
    {
        $days = (int) $this->option('days');
        $force = $this->option('force');

        $this->info("Nettoyage des sauvegardes de plus de {$days} jours...");

        if (!$force && !$this->confirm('Êtes-vous sûr de vouloir supprimer les anciennes sauvegardes ?')) {
            $this->info('Opération annulée.');
            return 0;
        }

        $deletedCount = $backupService->cleanupOldBackups($days);

        $this->info("{$deletedCount} sauvegarde(s) supprimée(s).");

        return 0;
    }
}