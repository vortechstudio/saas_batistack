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
     * Get the duration of the backup process.
     */
    protected function duration(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->started_at || !$this->completed_at) {
                    return null;
                }

                return $this->started_at->diffForHumans($this->completed_at, true);
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
    public function hasFailed(): bool
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
}

use App\Filament\Resources\Backups;

use App\Filament\Resources\Backups\Pages\CreateBackup;
use App\Filament\Resources\Backups\Pages\EditBackup;
use App\Filament\Resources\Backups\Pages\ListBackups;
use App\Filament\Resources\Backups\Schemas\BackupForm;
use App\Filament\Resources\Backups\Tables\BackupsTable;
use App\Models\Backup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BackupResource extends Resource
{
    protected static ?string $model = Backup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Sauvegardes';

    protected static ?string $modelLabel = 'Sauvegarde';

    protected static ?string $pluralModelLabel = 'Sauvegardes';

    protected static string | UnitEnum | null $navigationGroup = 'Sauvegarde/Synchronisation';

    public static function form(Schema $schema): Schema
    {
        return BackupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BackupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBackups::route('/'),
            'create' => CreateBackup::route('/create'),
            'edit' => EditBackup::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'file_path'];
    }
}
