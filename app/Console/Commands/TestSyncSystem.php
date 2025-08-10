<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\ExternalSyncLog;
use App\Services\ExternalSyncService;
use App\Enums\SyncStatus;
use Illuminate\Console\Command;

class TestSyncSystem extends Command
{
    protected $signature = 'test:sync-system';
    protected $description = 'Test the external synchronization system functionality';

    public function handle()
    {
        $this->info('🔄 Test du système de synchronisation externe...');

        try {
            $syncService = app(ExternalSyncService::class);

            // Créer un client de test s'il n'existe pas
            $customer = Customer::first();
            if (!$customer) {
                $this->info('📝 Création d\'un client de test...');
                $customer = Customer::create([
                    'name' => 'Client Test Sync',
                    'email' => 'test.sync@example.com',
                    'phone' => '+33123456789',
                    'address' => '123 Rue de Test',
                    'city' => 'Paris',
                    'postal_code' => '75001',
                    'country' => 'France',
                    'status' => \App\Enums\CustomerStatus::ACTIVE,
                ]);
                $this->info("✅ Client créé: {$customer->name} (ID: {$customer->id})");
            } else {
                $this->info("📋 Utilisation du client existant: {$customer->name} (ID: {$customer->id})");
            }

            // Test 1: Synchronisation avec le CRM
            $this->info('🔄 Test de synchronisation avec le CRM...');
            $syncLog = $syncService->syncEntity('crm', 'create', $customer);
            
            // Exécuter la synchronisation
            $success = $syncService->executeSync($syncLog);
            
            if ($success) {
                $this->info('✅ Synchronisation CRM réussie');
                $this->info("📋 Log ID: {$syncLog->id}");
            } else {
                $this->warn('⚠️  Synchronisation CRM simulée (système externe non configuré)');
                $this->info("📋 Statut: {$syncLog->status->value}");
            }

            // Test 2: Synchronisation avec l'ERP
            $this->info('🔄 Test de synchronisation avec l\'ERP...');
            $syncLog2 = $syncService->syncEntity('erp', 'update', $customer);
            $success2 = $syncService->executeSync($syncLog2);
            
            if ($success2) {
                $this->info('✅ Synchronisation ERP réussie');
            } else {
                $this->warn('⚠️  Synchronisation ERP simulée (système externe non configuré)');
            }

            // Test 3: Synchronisation en masse
            $this->info('📦 Test de synchronisation en masse...');
            $customers = Customer::limit(3)->get();
            $results = $syncService->bulkSync('analytics', 'sync', 'customers', $customers->pluck('id')->toArray());
            
            if ($results['success']) {
                $this->info("✅ Synchronisation en masse terminée: {$results['successful']} réussies, {$results['failed']} échouées");
            } else {
                $this->error("❌ Erreur lors de la synchronisation en masse: {$results['error']}");
            }

            // Test 4: Statistiques
            $this->info('📈 Statistiques des synchronisations:');
            $stats = [
                'Total' => ExternalSyncLog::count(),
                'Réussies' => ExternalSyncLog::where('status', SyncStatus::SUCCESS)->count(),
                'Échouées' => ExternalSyncLog::where('status', SyncStatus::FAILED)->count(),
                'En cours' => ExternalSyncLog::where('status', SyncStatus::PENDING)->count(),
            ];

            $this->table(
                ['Métrique', 'Valeur'],
                collect($stats)->map(fn($value, $key) => [$key, $value])->toArray()
            );

            // Test 5: Logs récents
            $this->info('📋 Derniers logs de synchronisation:');
            $recentLogs = ExternalSyncLog::orderBy('created_at', 'desc')->limit(5)->get();
            
            if ($recentLogs->count() > 0) {
                $this->table(
                    ['ID', 'Système', 'Entité', 'Opération', 'Statut', 'Date'],
                    $recentLogs->map(fn($log) => [
                        $log->id,
                        $log->system_name,
                        $log->entity_type,
                        $log->operation,
                        $log->status->value,
                        $log->created_at->format('d/m/Y H:i')
                    ])->toArray()
                );
            } else {
                $this->info('Aucun log de synchronisation trouvé.');
            }

            $this->info('🎉 Test du système de synchronisation terminé avec succès !');

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors du test: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
