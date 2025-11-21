<?php

namespace App\Services;

use App\Models\Customer\CustomerService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;

class TenantApiService
{
    protected ?CustomerService $service = null;

    public function for(CustomerService $service): self
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function client(): PendingRequest
    {
        if (!$this->service) {
            throw new \Exception("Service not set");
        }

        try {
            if (config('app.env') === 'local') {
                return \Http::withoutVerifying()
                    ->baseUrl('https://'.$this->service->domain.'/api')
                    ->timeout(10)
                    ->acceptJson();
            } else {
                return \Http::baseUrl('https://'.$this->service->domain.'/api')
                    ->timeout(10)
                    ->acceptJson();
            }
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @throws ConnectionException
     * @throws \Exception
     */
    public function getUsers(): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return $this->client()->get('/users');
    }

    public function createUser(array $data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return $this->client()->post('/users', $data);
    }

    public function updateUser($userId, array $data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return $this->client()->put("/users/{$userId}", $data);
    }

    public function deleteUser($userId): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return $this->client()->delete("/users/{$userId}");
    }

    public function sendPasswordReset($userId): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return $this->client()->get("/users/{$userId}/password-reset");
    }

    public function getSsoLink(string $email): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return $this->client()->post('/auth/sso-link', [
            'email' => $email,
            'source' => 'saas_dashboard',
        ]);
    }

    // --- Gestion du Système & Sauvegardes ---

    public function getStorageInfo(): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return $this->client()->get('/core/storage/info');
    }

    public function triggerBackup(): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        // On augmente le timeout pour la sauvegarde car cela peut être long
        return $this->client()->timeout(60)->post('/core/backup/run');
    }

    public function restoreBackup(string $backupTimestamp): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return $this->client()->get('/core/backup-restore', [
            'backup' => $backupTimestamp
        ]);
    }

    public function checkHealth()
    {
        // Timeout court (2s) car un check de santé doit être rapide
        return $this->client()->timeout(2)->get('/core/health');
    }

    public function getActivityLog(int $page = 1)
    {
        return $this->client()
            ->get('/core/activity-log', ['page' => $page]);
    }


}
