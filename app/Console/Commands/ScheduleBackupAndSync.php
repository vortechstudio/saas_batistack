<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use App\Services\ExternalSyncService;
use App\Models\Customer;
use Illuminate\Console\Command;

class ScheduleBackupAndSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:backup-sync {--backup-only : Exécuter seulement la sauvegarde} {--sync-only : Exécuter seulement la synchronisation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exécute les tâches planifiées de sauvegarde et de synchronisation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $backupOnly = $this->option('backup-only');
        $syncOnly = $this->option('sync-only');

        $this->info('🚀 Démarrage des tâches planifiées...');

        // Sauvegarde automatique
        if (!$syncOnly) {
            $this->info('💾 Exécution de la sauvegarde automatique...');
            $this->performBackup();
        }

        // Synchronisation automatique
        if (!$backupOnly) {
            $this->info('🔄 Exécution de la synchronisation automatique...');
            $this->performSync();
        }

        $this->info('✅ Tâches planifiées terminées avec succès !');
    }

    private function performBackup()
    {
        try {
            $backupService = app(BackupService::class);
            
            // Sauvegarde complète quotidienne
            $this->info('📦 Création d\'une sauvegarde complète...');
            $backup = $backupService->createFullBackup();
            
            if ($backup) {
                $this->info("✅ Sauvegarde créée: {$backup->name}");
                
                // Nettoyage des anciennes sauvegardes (garder les 7 dernières)
                $this->info('🧹 Nettoyage des anciennes sauvegardes...');
                $cleaned = $backupService->cleanupOldBackups(7);
                $this->info("🗑️  {$cleaned} anciennes sauvegardes supprimées");
            } else {
                $this->error('❌ Échec de la création de la sauvegarde');
            }
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la sauvegarde: {$e->getMessage()}");
        }
    }

    private function performSync()
    {
        try {
            $syncService = app(ExternalSyncService::class);
            
            // Synchronisation des clients modifiés dans les dernières 24h
            $this->info('👥 Synchronisation des clients récemment modifiés...');
            $recentCustomers = Customer::where('updated_at', '>=', now()->subDay())->get();
            
            if ($recentCustomers->count() > 0) {
                $this->info("📊 {$recentCustomers->count()} clients à synchroniser");
                
                // Synchronisation avec le CRM
                $this->info('🔄 Synchronisation avec le CRM...');
                $crmResults = $syncService->bulkSync('crm', 'sync', 'customers', $recentCustomers->pluck('id')->toArray());
                $this->displaySyncResults('CRM', $crmResults);
                
                // Synchronisation avec l'ERP
                $this->info('🔄 Synchronisation avec l\'ERP...');
                $erpResults = $syncService->bulkSync('erp', 'sync', 'customers', $recentCustomers->pluck('id')->toArray());
                $this->displaySyncResults('ERP', $erpResults);
                
                // Synchronisation avec Analytics
                $this->info('📈 Synchronisation avec Analytics...');
                $analyticsResults = $syncService->bulkSync('analytics', 'sync', 'customers', $recentCustomers->pluck('id')->toArray());
                $this->displaySyncResults('Analytics', $analyticsResults);
                
            } else {
                $this->info('ℹ️  Aucun client récemment modifié à synchroniser');
            }
            
            // Retry des synchronisations échouées
            $this->info('🔄 Retry des synchronisations échouées...');
            $retryCount = $syncService->retryFailedSyncs(3); // Retry max 3 fois
            $this->info("🔄 {$retryCount} synchronisations relancées");
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la synchronisation: {$e->getMessage()}");
        }
    }

    private function displaySyncResults(string $system, array $results)
    {
        $successful = collect($results)->where('success', true)->count();
        $failed = collect($results)->where('success', false)->count();
        
        if ($successful > 0) {
            $this->info("✅ {$system}: {$successful} réussies");
        }
        if ($failed > 0) {
            $this->warn("⚠️  {$system}: {$failed} échouées");
        }
    }
}
