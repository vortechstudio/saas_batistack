<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use App\Enums\BackupType;
use App\Jobs\CreateBackupJob;
use Illuminate\Console\Command;

class CreateBackupCommand extends Command
{
    protected $signature = 'backup:create 
                            {--type=full : Type de sauvegarde (full, incremental, differential)}
                            {--storage=local : Driver de stockage}
                            {--async : Exécuter en arrière-plan}';

    protected $description = 'Crée une nouvelle sauvegarde';

    public function handle(BackupService $backupService): int
    {
        $type = BackupType::tryFrom($this->option('type')) ?? BackupType::FULL;
        $storage = $this->option('storage');
        $async = $this->option('async');

        $this->info("Création d'une sauvegarde {$type->label()}...");

        $backup = $backupService->createBackup($type, $storage);

        if ($async) {
            CreateBackupJob::dispatch($backup);
            $this->info("Sauvegarde #{$backup->id} programmée en arrière-plan.");
        } else {
            $success = $backupService->executeBackup($backup);
            
            if ($success) {
                $this->info("Sauvegarde #{$backup->id} créée avec succès !");
                $this->line("Fichier: {$backup->file_path}");
                $this->line("Taille: {$backup->formatted_file_size}");
            } else {
                $this->error("Échec de la sauvegarde #{$backup->id}");
                $this->line("Erreur: {$backup->error_message}");
                return 1;
            }
        }

        return 0;
    }
}