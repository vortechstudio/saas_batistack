<?php

namespace App\Models;

use App\Enums\SyncStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'system_name',
        'operation',
        'entity_type',
        'entity_id',
        'status',
        'request_data',
        'response_data',
        'error_message',
        'retry_count',
        'last_retry_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'status' => SyncStatus::class,
        'request_data' => 'array',
        'response_data' => 'array',
        'retry_count' => 'integer',
        'entity_id' => 'integer',
        'last_retry_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Scope pour les synchronisations réussies
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', SyncStatus::SUCCESS);
    }

    /**
     * Scope pour les synchronisations échouées
     */
    public function scopeFailed($query)
    {
        return $query->where('status', SyncStatus::FAILED);
    }

    /**
     * Scope pour les synchronisations en cours
     */
    public function scopeRunning($query)
    {
        return $query->where('status', SyncStatus::RUNNING);
    }

    /**
     * Scope par système externe
     */
    public function scopeForSystem($query, string $systemName)
    {
        return $query->where('system_name', $systemName);
    }

    /**
     * Scope par type d'entité
     */
    public function scopeForEntity($query, string $entityType, ?int $entityId = null)
    {
        $query = $query->where('entity_type', $entityType);
        
        if ($entityId) {
            $query->where('entity_id', $entityId);
        }

        return $query;
    }

    /**
     * Vérifie si la synchronisation est réussie
     */
    public function isSuccessful(): bool
    {
        return $this->status === SyncStatus::SUCCESS;
    }

    /**
     * Vérifie si la synchronisation a échoué
     */
    public function isFailed(): bool
    {
        return $this->status === SyncStatus::FAILED;
    }

    /**
     * Vérifie si la synchronisation est en cours
     */
    public function isRunning(): bool
    {
        return $this->status === SyncStatus::RUNNING;
    }

    /**
     * Vérifie si une nouvelle tentative est possible
     */
    public function canRetry(): bool
    {
        return $this->isFailed() && $this->retry_count < 3;
    }

    /**
     * Incrémente le compteur de tentatives
     */
    public function incrementRetryCount(): void
    {
        $this->increment('retry_count');
        $this->update(['last_retry_at' => now()]);
    }

    /**
     * Calcule la durée de la synchronisation
     */
    public function duration(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInSeconds($this->started_at);
    }

    /**
     * Relation polymorphe avec l'entité synchronisée
     */
    public function entity()
    {
        $modelClass = match ($this->entity_type) {
            'customers' => Customer::class,
            'licenses' => License::class,
            'products' => Product::class,
            'users' => User::class,
            default => null,
        };

        if (!$modelClass || !$this->entity_id) {
            return null;
        }

        return $modelClass::find($this->entity_id);
    }
}