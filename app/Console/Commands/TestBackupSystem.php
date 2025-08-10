<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Services\BackupService;
use App\Enums\BackupType;
use Illuminate\Console\Command;

class TestBackupSystem extends Command
{
    protected $signature = 'test:backup-system';
    protected $description = 'Test the backup system functionality';

    public function handle()
    {
        $this->info('🔧 Test du système de sauvegarde...');

        try {
            $backupService = app(BackupService::class);

            // Test 1: Créer une sauvegarde complète
            $this->info('📦 Création d\'une sauvegarde complète...');
            $backup = $backupService->createBackup(BackupType::FULL, 'local');
            $this->info("✅ Sauvegarde créée: {$backup->name} (ID: {$backup->id})");

            // Test 2: Exécuter la sauvegarde
            $this->info('⚡ Exécution de la sauvegarde...');
            $success = $backupService->executeBackup($backup);
            
            if ($success) {
                $this->info('✅ Sauvegarde exécutée avec succès');
                $this->info("📁 Fichier: {$backup->file_path}");
                $this->info("📊 Taille: " . number_format($backup->file_size / 1024 / 1024, 2) . " MB");
            } else {
                $this->error('❌ Échec de la sauvegarde');
                $this->error("Erreur: {$backup->error_message}");
            }

            // Test 3: Statistiques
            $this->info('📈 Statistiques des sauvegardes:');
            $stats = $backupService->getBackupStats();
            $this->table(
                ['Métrique', 'Valeur'],
                [
                    ['Total', $stats['total']],
                    ['Réussies', $stats['successful']],
                    ['Échouées', $stats['failed']],
                    ['En cours', $stats['running']],
                    ['Espace utilisé', number_format($stats['total_size'] / 1024 / 1024, 2) . ' MB'],
                ]
            );

            $this->info('🎉 Test du système de sauvegarde terminé avec succès !');

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors du test: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
