<?php

namespace App\Services;

use App\Models\ExternalSyncLog;
use App\Models\Customer;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Enums\SyncStatus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class ExternalSyncService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('external_sync', []);
    }

    /**
     * Synchronise une entité avec un système externe
     */
    public function syncEntity(
        string $systemName,
        string $operation,
        Model $entity,
        array $additionalData = []
    ): ExternalSyncLog {
        $syncLog = ExternalSyncLog::create([
            'system_name' => $systemName,
            'operation' => $operation,
            'entity_type' => $this->getEntityType($entity),
            'entity_id' => $entity->id,
            'status' => SyncStatus::PENDING,
            'request_data' => array_merge($entity->toArray(), $additionalData),
            'started_at' => now(),
        ]);

        Log::info('Synchronisation créée', [
            'sync_id' => $syncLog->id,
            'system' => $systemName,
            'operation' => $operation,
            'entity' => get_class($entity),
            'entity_id' => $entity->id
        ]);

        return $syncLog;
    }

    /**
     * Exécute une synchronisation
     */
    public function executeSync(ExternalSyncLog $syncLog): bool
    {
        try {
            $syncLog->update(['status' => SyncStatus::RUNNING]);

            Log::info('Début de la synchronisation', ['sync_id' => $syncLog->id]);

            $response = $this->performSync($syncLog);

            if ($response['success']) {
                $syncLog->update([
                    'status' => SyncStatus::SUCCESS,
                    'response_data' => $response['data'],
                    'completed_at' => now(),
                ]);

                Log::info('Synchronisation réussie', [
                    'sync_id' => $syncLog->id,
                    'response' => $response['data']
                ]);

                return true;
            }

            throw new \Exception($response['error'] ?? 'Erreur inconnue');

        } catch (\Exception $e) {
            $syncLog->update([
                'status' => SyncStatus::FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            Log::error('Échec de la synchronisation', [
                'sync_id' => $syncLog->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Effectue la synchronisation selon le système
     */
    protected function performSync(ExternalSyncLog $syncLog): array
    {
        return match ($syncLog->system_name) {
            'crm' => $this->syncWithCRM($syncLog),
            'erp' => $this->syncWithERP($syncLog),
            'accounting' => $this->syncWithAccounting($syncLog),
            'analytics' => $this->syncWithAnalytics($syncLog),
            default => ['success' => false, 'error' => 'Système non supporté'],
        };
    }

    /**
     * Synchronisation avec le CRM
     */
    protected function syncWithCRM(ExternalSyncLog $syncLog): array
    {
        $config = $this->config['crm'] ?? [];
        
        if (!isset($config['api_url']) || !isset($config['api_key'])) {
            return ['success' => false, 'error' => 'Configuration CRM manquante'];
        }

        try {
            $endpoint = $this->buildEndpoint($config['api_url'], $syncLog);
            $method = $this->getHttpMethod($syncLog->operation);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Content-Type' => 'application/json',
            ])->$method($endpoint, $syncLog->request_data);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return [
                'success' => false,
                'error' => 'Erreur HTTP: ' . $response->status() . ' - ' . $response->body()
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Synchronisation avec l'ERP
     */
    protected function syncWithERP(ExternalSyncLog $syncLog): array
    {
        $config = $this->config['erp'] ?? [];
        
        if (!isset($config['api_url']) || !isset($config['api_key'])) {
            return ['success' => false, 'error' => 'Configuration ERP manquante'];
        }

        try {
            $endpoint = $this->buildEndpoint($config['api_url'], $syncLog);
            $method = $this->getHttpMethod($syncLog->operation);

            $response = Http::withHeaders([
                'X-API-Key' => $config['api_key'],
                'Content-Type' => 'application/json',
            ])->$method($endpoint, $syncLog->request_data);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return [
                'success' => false,
                'error' => 'Erreur HTTP: ' . $response->status() . ' - ' . $response->body()
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Synchronisation avec la comptabilité
     */
    protected function syncWithAccounting(ExternalSyncLog $syncLog): array
    {
        $config = $this->config['accounting'] ?? [];
        
        if (!isset($config['api_url']) || !isset($config['username']) || !isset($config['password'])) {
            return ['success' => false, 'error' => 'Configuration comptabilité manquante'];
        }

        try {
            $endpoint = $this->buildEndpoint($config['api_url'], $syncLog);
            $method = $this->getHttpMethod($syncLog->operation);

            $response = Http::withBasicAuth($config['username'], $config['password'])
                ->withHeaders(['Content-Type' => 'application/json'])
                ->$method($endpoint, $syncLog->request_data);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return [
                'success' => false,
                'error' => 'Erreur HTTP: ' . $response->status() . ' - ' . $response->body()
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Synchronisation avec les analytics
     */
    protected function syncWithAnalytics(ExternalSyncLog $syncLog): array
    {
        $config = $this->config['analytics'] ?? [];
        
        if (!isset($config['api_url']) || !isset($config['api_key'])) {
            return ['success' => false, 'error' => 'Configuration analytics manquante'];
        }

        try {
            // Pour les analytics, on envoie généralement des événements
            $eventData = [
                'event' => $syncLog->operation,
                'entity_type' => $syncLog->entity_type,
                'entity_id' => $syncLog->entity_id,
                'timestamp' => now()->toISOString(),
                'data' => $syncLog->request_data,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Content-Type' => 'application/json',
            ])->post($config['api_url'] . '/events', $eventData);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return [
                'success' => false,
                'error' => 'Erreur HTTP: ' . $response->status() . ' - ' . $response->body()
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Construit l'endpoint API
     */
    protected function buildEndpoint(string $baseUrl, ExternalSyncLog $syncLog): string
    {
        $endpoint = rtrim($baseUrl, '/') . '/' . $syncLog->entity_type;

        if (in_array($syncLog->operation, ['sync', 'export']) && $syncLog->entity_id) {
            $endpoint .= '/' . $syncLog->entity_id;
        }

        return $endpoint;
    }

    /**
     * Obtient la méthode HTTP selon l'opération
     */
    protected function getHttpMethod(string $operation): string
    {
        return match ($operation) {
            'sync', 'export' => 'put',
            'import' => 'post',
            default => 'post',
        };
    }

    /**
     * Obtient le type d'entité
     */
    protected function getEntityType(Model $entity): string
    {
        return match (get_class($entity)) {
            Customer::class => 'customers',
            License::class => 'licenses',
            Product::class => 'products',
            User::class => 'users',
            default => 'unknown',
        };
    }

    /**
     * Relance une synchronisation échouée
     */
    public function retrySync(ExternalSyncLog $syncLog): bool
    {
        if (!$syncLog->canRetry()) {
            return false;
        }

        $syncLog->incrementRetryCount();
        
        return $this->executeSync($syncLog);
    }

    /**
     * Synchronise en masse plusieurs entités
     */
    public function bulkSync(
        string $systemName,
        string $operation,
        string $entityType,
        array $entityIds = []
    ): array {
        $results = [];
        $modelClass = $this->getModelClass($entityType);

        if (!$modelClass) {
            return ['success' => false, 'error' => 'Type d\'entité non supporté'];
        }

        $query = $modelClass::query();
        
        if (!empty($entityIds)) {
            $query->whereIn('id', $entityIds);
        }

        $entities = $query->get();

        foreach ($entities as $entity) {
            $syncLog = $this->syncEntity($systemName, $operation, $entity);
            $success = $this->executeSync($syncLog);
            
            $results[] = [
                'entity_id' => $entity->id,
                'sync_id' => $syncLog->id,
                'success' => $success,
            ];
        }

        return [
            'success' => true,
            'total' => count($results),
            'successful' => count(array_filter($results, fn($r) => $r['success'])),
            'failed' => count(array_filter($results, fn($r) => !$r['success'])),
            'results' => $results,
        ];
    }

    /**
     * Obtient la classe de modèle selon le type d'entité
     */
    protected function getModelClass(string $entityType): ?string
    {
        return match ($entityType) {
            'customers' => Customer::class,
            'licenses' => License::class,
            'products' => Product::class,
            'users' => User::class,
            default => null,
        };
    }

    /**
     * Relance les synchronisations échouées
     */
    public function retryFailedSyncs(int $maxRetries = 3): int
    {
        $failedSyncs = ExternalSyncLog::where('status', SyncStatus::FAILED)
            ->where('retry_count', '<', $maxRetries)
            ->orderBy('created_at', 'desc')
            ->limit(50) // Limiter pour éviter la surcharge
            ->get();

        $retryCount = 0;

        foreach ($failedSyncs as $syncLog) {
            if ($this->retrySync($syncLog)) {
                $retryCount++;
            }
        }

        return $retryCount;
    }

    /**
     * Obtient les statistiques de synchronisation
     */
    public function getSyncStats(string $systemName = null): array
    {
        $query = ExternalSyncLog::query();
        
        if ($systemName) {
            $query->where('system_name', $systemName);
        }

        return [
            'total' => $query->count(),
            'successful' => $query->where('status', SyncStatus::SUCCESS)->count(),
            'failed' => $query->where('status', SyncStatus::FAILED)->count(),
            'running' => $query->where('status', SyncStatus::RUNNING)->count(),
            'last_sync' => $query->where('status', SyncStatus::SUCCESS)
                ->orderBy('completed_at', 'desc')
                ->first()?->completed_at,
            'by_system' => ExternalSyncLog::selectRaw('system_name, COUNT(*) as count')
                ->groupBy('system_name')
                ->pluck('count', 'system_name')
                ->toArray(),
        ];
    }
}