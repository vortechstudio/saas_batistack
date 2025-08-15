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
        'file_size' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the human-readable file size.
     */
    protected function humanFileSize(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->file_size) {
                    return null;
                }

                $bytes = $this->file_size;
                $units = ['B', 'KB', 'MB', 'GB', 'TB'];

                for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                    $bytes /= 1024;
                }

                return round($bytes, 2) . ' ' . $units[$i];
            }
        );
    }



    /**
     * Scope a query to only include completed backups.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', BackupStatus::COMPLETED);
    }

    /**
     * Scope a query to only include failed backups.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', BackupStatus::FAILED);
    }

    /**
     * Scope a query to only include running backups.
     */
    public function scopeRunning($query)
    {
        return $query->where('status', BackupStatus::RUNNING);
    }

    /**
     * Check if the backup is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === BackupStatus::COMPLETED;
    }

    /**
     * Check if the backup has failed.
     */
    public function isFailed(): bool
    {
        return $this->status === BackupStatus::FAILED;
    }

    /**
     * Check if the backup is currently running.
     */
    public function isRunning(): bool
    {
        return $this->status === BackupStatus::RUNNING;
    }

    /**
     * Get the duration of the backup in seconds.
     */
    public function duration(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }

    /**
     * Get the formatted file size.
     */
    protected function formattedFileSize(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->file_size) {
                    return null;
                }

                $bytes = $this->file_size;
                $units = ['B', 'KB', 'MB', 'GB', 'TB'];

                for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
                    $bytes /= 1024;
                }

                return round($bytes, 0) . ' ' . $units[$i];
            }
        );
    }

    /**
     * Get the full path to the backup file.
     */
    public function getFullPath(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return storage_path('app/backups/' . $this->file_path);
    }

    /**
     * Check if the backup file exists.
     */
    public function fileExists(): bool
    {
        $fullPath = $this->getFullPath();
        
        if (!$fullPath) {
            return false;
        }

        return file_exists($fullPath);
    }

    /**
     * Scope a query to only include successful backups.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', BackupStatus::COMPLETED);
    }
}
