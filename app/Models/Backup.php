<?php

namespace App\Models;

use App\Enums\BackupStatus;
use App\Enums\BackupType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'status',
        'storage_driver',
        'file_path',
        'file_size',
        'metadata',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'type' => BackupType::class,
        'status' => BackupStatus::class,
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'file_size' => 'integer',
    ];

    /**
     * Scope pour les sauvegardes réussies
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', BackupStatus::COMPLETED);
    }

    /**
     * Scope pour les sauvegardes échouées
     */
    public function scopeFailed($query)
    {
        return $query->where('status', BackupStatus::FAILED);
    }

    /**
     * Scope pour les sauvegardes en cours
     */
    public function scopeRunning($query)
    {
        return $query->where('status', BackupStatus::RUNNING);
    }

    /**
     * Vérifie si la sauvegarde est terminée
     */
    public function isCompleted(): bool
    {
        return $this->status === BackupStatus::COMPLETED;
    }

    /**
     * Vérifie si la sauvegarde a échoué
     */
    public function isFailed(): bool
    {
        return $this->status === BackupStatus::FAILED;
    }

    /**
     * Vérifie si la sauvegarde est en cours
     */
    public function isRunning(): bool
    {
        return $this->status === BackupStatus::RUNNING;
    }

    /**
     * Calcule la durée de la sauvegarde
     */
    public function duration(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }

    /**
     * Formate la taille du fichier
     */
    protected function formattedFileSize(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->file_size) {
                    return null;
                }

                $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                $bytes = $this->file_size;
                $i = 0;

                while ($bytes >= 1024 && $i < count($units) - 1) {
                    $bytes /= 1024;
                    $i++;
                }

                return round($bytes, 2) . ' ' . $units[$i];
            }
        );
    }

    /**
     * Obtient le chemin complet du fichier de sauvegarde
     */
    public function getFullPath(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return storage_path('app/backups/' . $this->file_path);
    }

    /**
     * Vérifie si le fichier de sauvegarde existe
     */
    public function fileExists(): bool
    {
        $path = $this->getFullPath();
        return $path && file_exists($path);
    }
}