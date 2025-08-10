<?php

namespace App\Console\Commands;

use App\Services\ExternalSyncService;
use App\Models\Customer;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Jobs\SyncEntityJob;
use Illuminate\Console\Command;

class SyncEntityCommand extends Command
{
    protected $signature = 'sync:entity 
                            {system : Nom du système externe (crm, erp, accounting, analytics)}
                            {operation : Opération (sync, export, import)}
                            {entity_type : Type d\'entité (customers, licenses, products, users)}
                            {--id= : ID spécifique de l\'entité}
                            {--bulk : Synchroniser toutes les entités du type}
                            {--async : Exécuter en arrière-plan}';

    protected $description = 'Synchronise des entités avec un système externe';

    public function handle(ExternalSyncService $syncService): int
    {
        $system = $this->argument('system');
        $operation = $this->argument('operation');
        $entityType = $this->argument('entity_type');
        $entityId = $this->option('id');
        $bulk = $this->option('bulk');
        $async = $this->option('async');

        if (!in_array($system, ['crm', 'erp', 'accounting', 'analytics'])) {
            $this->error('Système non supporté. Utilisez: crm, erp, accounting, analytics');
            return 1;
        }

        if (!in_array($entityType, ['customers', 'licenses', 'products', 'users'])) {
            $this->error('Type d\'entité non supporté. Utilisez: customers, licenses, products, users');
            return 1;
        }

        if ($bulk) {
            return $this->handleBulkSync($syncService, $system, $operation, $entityType, $async);
        }

        if ($entityId) {
            return $this->handleSingleSync($syncService, $system, $operation, $entityType, $entityId, $async);
        }

        $this->error('Vous devez spécifier --id ou --bulk');
        return 1;
    }

    protected function handleSingleSync(
        ExternalSyncService $syncService,
        string $system,
        string $operation,
        string $entityType,
        int $entityId,
        bool $async
    ): int {
        $entity = $this->findEntity($entityType, $entityId);
        
        if (!$entity) {
            $this->error("Entité {$entityType}#{$entityId} non trouvée");
            return 1;
        }

        $this->info("Synchronisation de {$entityType}#{$entityId} avec {$system}...");

        $syncLog = $syncService->syncEntity($system, $operation, $entity);

        if ($async) {
            SyncEntityJob::dispatch($syncLog);
            $this->info("Synchronisation #{$syncLog->id} programmée en arrière-plan.");
        } else {
            $success = $syncService->executSync($syncLog);
            
            if ($success) {
                $this->info("Synchronisation #{$syncLog->id} réussie !");
            } else {
                $this->error("Échec de la synchronisation #{$syncLog->id}");
                $this->line("Erreur: {$syncLog->error_message}");
                return 1;
            }
        }

        return 0;
    }

    protected function handleBulkSync(
        ExternalSyncService $syncService,
        string $system,
        string $operation,
        string $entityType,
        bool $async
    ): int {
        $this->info("Synchronisation en masse de {$entityType} avec {$system}...");

        if ($async) {
            // Pour la synchronisation en masse asynchrone, on crée des jobs individuels
            $modelClass = $this->getModelClass($entityType);
            $entities = $modelClass::all();

            foreach ($entities as $entity) {
                $syncLog = $syncService->syncEntity($system, $operation, $entity);
                SyncEntityJob::dispatch($syncLog);
            }

            $this->info("{$entities->count()} synchronisations programmées en arrière-plan.");
        } else {
            $result = $syncService->bulkSync($system, $operation, $entityType);
            
            if ($result['success']) {
                $this->info("Synchronisation en masse terminée !");
                $this->line("Total: {$result['total']}");
                $this->line("Réussies: {$result['successful']}");
                $this->line("Échouées: {$result['failed']}");
            } else {
                $this->error("Échec de la synchronisation en masse");
                $this->line("Erreur: {$result['error']}");
                return 1;
            }
        }

        return 0;
    }

    protected function findEntity(string $entityType, int $entityId)
    {
        $modelClass = $this->getModelClass($entityType);
        return $modelClass::find($entityId);
    }

    protected function getModelClass(string $entityType): string
    {
        return match ($entityType) {
            'customers' => Customer::class,
            'licenses' => License::class,
            'products' => Product::class,
            'users' => User::class,
        };
    }
}