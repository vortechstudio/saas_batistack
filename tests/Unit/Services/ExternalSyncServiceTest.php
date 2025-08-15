<?php

use App\Services\ExternalSyncService;
use App\Models\ExternalSyncLog;
use App\Models\Customer;
use App\Enums\SyncStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Configuration des systèmes externes pour les tests
    Config::set('external_sync', [
        'crm' => [
            'api_url' => 'https://crm.example.com/api',
            'api_key' => 'test-crm-key',
        ],
        'erp' => [
            'api_url' => 'https://erp.example.com/api',
            'api_key' => 'test-erp-key',
        ],
        'accounting' => [
            'api_url' => 'https://accounting.example.com/api',
            'username' => 'test-user',
            'password' => 'test-password',
        ],
        'analytics' => [
            'api_url' => 'https://analytics.example.com/api',
            'api_key' => 'test-analytics-key',
        ],
    ]);

    $this->syncService = new ExternalSyncService();
});

test('can execute sync successfully', function () {
    $customer = Customer::factory()->create();

    // Mock HTTP pour CRM
    Http::fake([
        'https://crm.example.com/api/customers/' . $customer->id => Http::response(['data' => []], 200)
    ]);

    $syncLog = ExternalSyncLog::create([
        'system_name' => 'crm',
        'operation' => 'sync',
        'entity_type' => 'customers',
        'entity_id' => $customer->id,
        'status' => SyncStatus::PENDING,
        'request_data' => $customer->toArray(),
        'started_at' => now(),
    ]);

    $result = $this->syncService->executeSync($syncLog);

    expect($result)->toBeTrue()
        ->and($syncLog->fresh()->status)->toBe(SyncStatus::SUCCESS);
});

test('handles sync failure gracefully', function () {
    $customer = Customer::factory()->create();

    // Mock HTTP pour simuler un échec
    Http::fake([
        'https://crm.example.com/api/customers/' . $customer->id => Http::response(['error' => 'Server error'], 500)
    ]);

    $syncLog = ExternalSyncLog::create([
        'system_name' => 'crm',
        'operation' => 'sync',
        'entity_type' => 'customers',
        'entity_id' => $customer->id,
        'status' => SyncStatus::PENDING,
        'request_data' => $customer->toArray(),
        'started_at' => now(),
    ]);

    $result = $this->syncService->executeSync($syncLog);

    expect($result)->toBeFalse()
        ->and($syncLog->fresh()->status)->toBe(SyncStatus::FAILED)
        ->and($syncLog->fresh()->error_message)->toContain('Erreur HTTP: 500');
});

test('can retry failed sync', function () {
    $customer = Customer::factory()->create();

    // Mock HTTP pour le retry
    Http::fake([
        'https://crm.example.com/api/customers/' . $customer->id => Http::response(['data' => []], 200)
    ]);

    $syncLog = ExternalSyncLog::create([
        'system_name' => 'crm',
        'operation' => 'sync',
        'entity_type' => 'customers',
        'entity_id' => $customer->id,
        'status' => SyncStatus::FAILED,
        'retry_count' => 1,
        'request_data' => $customer->toArray(),
        'started_at' => now(),
    ]);

    $result = $this->syncService->retrySync($syncLog);

    expect($result)->toBeTrue()
        ->and($syncLog->fresh()->retry_count)->toBe(2);
});

test('cannot retry sync when max retries reached', function () {
    $customer = Customer::factory()->create();

    $syncLog = ExternalSyncLog::create([
        'system_name' => 'crm',
        'operation' => 'sync',
        'entity_type' => 'customers',
        'entity_id' => $customer->id,
        'status' => SyncStatus::FAILED,
        'retry_count' => 3, // Max atteint
        'request_data' => $customer->toArray(),
        'started_at' => now(),
    ]);

    $result = $this->syncService->retrySync($syncLog);

    expect($result)->toBeFalse();
});

test('can get sync stats', function () {
    // Utiliser les méthodes de la factory au lieu de passer le statut en paramètre
    ExternalSyncLog::factory()->count(5)->successful()->create();
    ExternalSyncLog::factory()->count(2)->failed()->create();
    ExternalSyncLog::factory()->create(['status' => SyncStatus::RUNNING]);

    $stats = $this->syncService->getSyncStats();

    expect($stats)->toHaveKey('total')
        ->toHaveKey('successful')
        ->toHaveKey('failed')
        ->toHaveKey('running')
        ->and($stats['total'])->toBe(8)
        ->and($stats['successful'])->toBe(5)
        ->and($stats['failed'])->toBe(2)
        ->and($stats['running'])->toBe(1);
});

test('can bulk sync multiple entities', function () {
    $customers = Customer::factory()->count(3)->create();
    $customerIds = $customers->pluck('id')->toArray();

    // Mock HTTP pour chaque customer
    $httpFakes = [];
    foreach ($customers as $customer) {
        $httpFakes['https://crm.example.com/api/customers/' . $customer->id] = Http::response(['data' => []], 200);
    }
    Http::fake($httpFakes);

    $results = $this->syncService->bulkSync('crm', 'sync', 'customers', $customerIds);

    expect($results['success'])->toBeTrue()
        ->and($results['total'])->toBe(3)
        ->and($results['successful'])->toBe(3)
        ->and($results['failed'])->toBe(0);
});

test('bulk sync handles failures gracefully', function () {
    $customers = Customer::factory()->count(2)->create();
    $customerIds = $customers->pluck('id')->toArray();

    // Mock HTTP - un succès, un échec
    Http::fake([
        'https://crm.example.com/api/customers/' . $customers[0]->id => Http::response(['data' => []], 200),
        'https://crm.example.com/api/customers/' . $customers[1]->id => Http::response(['error' => 'Server error'], 500)
    ]);

    $results = $this->syncService->bulkSync('crm', 'sync', 'customers', $customerIds);

    expect($results['success'])->toBeTrue()
        ->and($results['total'])->toBe(2)
        ->and($results['successful'])->toBe(1)
        ->and($results['failed'])->toBe(1);
});

test('bulk sync returns error for unsupported entity type', function () {
    $results = $this->syncService->bulkSync('crm', 'sync', 'unsupported', [1, 2, 3]);

    expect($results['success'])->toBeFalse()
        ->and($results['error'])->toBe('Type d\'entité non supporté');
});

test('can retry failed syncs in batch', function () {
    $customers = Customer::factory()->count(5)->create();

    // Créer des logs échoués avec différents retry_count
    for ($i = 0; $i < 5; $i++) {
        ExternalSyncLog::create([
            'system_name' => 'crm',
            'operation' => 'sync',
            'entity_type' => 'customers',
            'entity_id' => $customers[$i]->id,
            'status' => SyncStatus::FAILED,
            'retry_count' => $i < 3 ? 1 : 3, // Les 3 premiers peuvent être retentés
            'request_data' => $customers[$i]->toArray(),
            'started_at' => now(),
        ]);
    }

    // Mock HTTP pour les retries
    $httpFakes = [];
    for ($i = 0; $i < 3; $i++) {
        $httpFakes['https://crm.example.com/api/customers/' . $customers[$i]->id] = Http::response(['data' => []], 200);
    }
    Http::fake($httpFakes);

    $retryCount = $this->syncService->retryFailedSyncs(3);

    expect($retryCount)->toBe(3); // Seulement les 3 premiers peuvent être retentés
});

test('get sync stats filters by system name', function () {
    ExternalSyncLog::factory()->successful()->create([
        'system_name' => 'crm'
    ]);
    ExternalSyncLog::factory()->failed()->create([
        'system_name' => 'crm'
    ]);
    ExternalSyncLog::factory()->successful()->create([
        'system_name' => 'erp'
    ]);

    $crmStats = $this->syncService->getSyncStats('crm');

    expect($crmStats['total'])->toBe(2)
        ->and($crmStats['successful'])->toBe(1)
        ->and($crmStats['failed'])->toBe(1);
});
