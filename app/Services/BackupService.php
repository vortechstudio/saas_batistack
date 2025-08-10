<?php

namespace App\Services;

use App\Models\Backup;
use App\Enums\BackupStatus;
use App\Enums\BackupType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BackupService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('backup', []);
    }

    /**
     * Crée une nouvelle sauvegarde
     */
    public function createBackup(
        BackupType $type = BackupType::FULL,
        string $storageDriver = 'local',
        array $options = []
    ): Backup {
        $backup = Backup::create([
            'name' => $this->generateBackupName($type),
            'type' => $type,
            'status' => BackupStatus::PENDING,
            'storage_driver' => $storageDriver,
            'metadata' => $options,
            'started_at' => now(),
        ]);

        Log::info('Sauvegarde créée', ['backup_id' => $backup->id, 'type' => $type->value]);

        return $backup;
    }

    /**
     * Exécute une sauvegarde
     */
    public function executeBackup(Backup $backup): bool
    {
        try {
            $backup->update(['status' => BackupStatus::RUNNING]);

            Log::info('Début de la sauvegarde', ['backup_id' => $backup->id]);

            $filePath = $this->performBackup($backup);

            if ($filePath) {
                $fileSize = $this->getFileSize($filePath);
                
                $backup->update([
                    'status' => BackupStatus::COMPLETED,
                    'file_path' => $filePath,
                    'file_size' => $fileSize,
                    'completed_at' => now(),
                ]);

                Log::info('Sauvegarde terminée avec succès', [
                    'backup_id' => $backup->id,
                    'file_path' => $filePath,
                    'file_size' => $fileSize
                ]);

                return true;
            }

            throw new \Exception('Échec de la création du fichier de sauvegarde');

        } catch (\Exception $e) {
            $backup->update([
                'status' => BackupStatus::FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            Log::error('Échec de la sauvegarde', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Effectue la sauvegarde selon le type
     */
    protected function performBackup(Backup $backup): ?string
    {
        return match ($backup->type) {
            BackupType::FULL => $this->performFullBackup($backup),
            BackupType::INCREMENTAL => $this->performIncrementalBackup($backup),
            BackupType::DIFFERENTIAL => $this->performDifferentialBackup($backup),
        };
    }

    /**
     * Sauvegarde complète de la base de données
     */
    public function performFullBackup(Backup $backup): ?string
    {
        $fileName = 'full_backup_' . now()->format('Y-m-d_H-i-s') . '.json';
        $filePath = storage_path('app/backups/' . $fileName);

        // Créer le répertoire s'il n'existe pas
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        try {
            // Utiliser Laravel pour exporter toutes les données
            $data = [
                'backup_info' => [
                    'type' => 'full',
                    'created_at' => now()->toISOString(),
                    'database' => config('database.connections.mysql.database'),
                ],
                'schema' => $this->exportSchema(),
                'data' => $this->exportAllData(),
            ];

            if (file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT))) {
                return $fileName;
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de la sauvegarde complète', [
                'error' => $e->getMessage(),
                'file_path' => $filePath
            ]);
        }

        return null;
    }

    /**
     * Exporte le schéma de la base de données
     */
    protected function exportSchema(): array
    {
        $tables = DB::select('SHOW TABLES');
        $schema = [];

        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $schema[$tableName] = $createTable[0]->{'Create Table'};
        }

        return $schema;
    }

    /**
     * Exporte toutes les données
     */
    protected function exportAllData(): array
    {
        $data = [];
        
        // Tables principales à sauvegarder
        $tables = ['users', 'customers', 'products', 'modules', 'options', 'licenses', 'license_modules', 'license_options'];
        
        foreach ($tables as $table) {
            try {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $data[$table] = DB::table($table)->get()->toArray();
                }
            } catch (\Exception $e) {
                Log::warning("Impossible de sauvegarder la table {$table}", ['error' => $e->getMessage()]);
            }
        }

        return $data;
    }

    /**
     * Sauvegarde incrémentale
     */
    protected function performIncrementalBackup(Backup $backup): ?string
    {
        $lastBackup = Backup::where('status', BackupStatus::COMPLETED)
            ->where('id', '<', $backup->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $since = $lastBackup ? $lastBackup->created_at : now()->subDays(1);

        return $this->performDataBackup($backup, $since, 'incremental');
    }

    /**
     * Sauvegarde différentielle
     */
    protected function performDifferentialBackup(Backup $backup): ?string
    {
        $lastFullBackup = Backup::where('status', BackupStatus::COMPLETED)
            ->where('type', BackupType::FULL)
            ->where('id', '<', $backup->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $since = $lastFullBackup ? $lastFullBackup->created_at : now()->subDays(7);

        return $this->performDataBackup($backup, $since, 'differential');
    }

    /**
     * Sauvegarde des données modifiées depuis une date
     */
    protected function performDataBackup(Backup $backup, Carbon $since, string $type): ?string
    {
        $fileName = $type . '_backup_' . now()->format('Y-m-d_H-i-s') . '.json';
        $filePath = storage_path('app/backups/' . $fileName);

        // Créer le répertoire s'il n'existe pas
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $data = [
            'backup_info' => [
                'type' => $type,
                'created_at' => now()->toISOString(),
                'since' => $since->toISOString(),
            ],
            'customers' => DB::table('customers')->where('updated_at', '>=', $since)->get(),
            'licenses' => DB::table('licenses')->where('updated_at', '>=', $since)->get(),
            'products' => DB::table('products')->where('updated_at', '>=', $since)->get(),
            'users' => DB::table('users')->where('updated_at', '>=', $since)->get(),
        ];

        if (file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT))) {
            return $fileName;
        }

        return null;
    }

    /**
     * Génère un nom de sauvegarde
     */
    protected function generateBackupName(BackupType $type): string
    {
        return sprintf(
            'Sauvegarde %s - %s',
            $type->label(),
            now()->format('d/m/Y H:i')
        );
    }

    /**
     * Obtient la taille d'un fichier
     */
    protected function getFileSize(string $fileName): int
    {
        $fullPath = storage_path('app/backups/' . $fileName);
        return file_exists($fullPath) ? filesize($fullPath) : 0;
    }

    /**
     * Crée et exécute une sauvegarde complète
     */
    public function createFullBackup(): ?Backup
    {
        $backup = Backup::create([
            'name' => $this->generateBackupName(BackupType::FULL),
            'type' => BackupType::FULL,
            'status' => BackupStatus::PENDING,
            'storage_driver' => 'local',
            'started_at' => now(),
        ]);

        $success = $this->executeBackup($backup);
        
        return $success ? $backup->fresh() : null;
    }

    /**
     * Nettoie les anciennes sauvegardes
     */
    public function cleanupOldBackups(int $keepDays = 30): int
    {
        $cutoffDate = now()->subDays($keepDays);
        
        $oldBackups = Backup::where('created_at', '<', $cutoffDate)->get();
        $deletedCount = 0;

        foreach ($oldBackups as $backup) {
            if ($backup->file_path && $backup->fileExists()) {
                unlink($backup->getFullPath());
            }
            
            $backup->delete();
            $deletedCount++;
        }

        Log::info('Nettoyage des anciennes sauvegardes', [
            'deleted_count' => $deletedCount,
            'cutoff_date' => $cutoffDate
        ]);

        return $deletedCount;
    }

    /**
     * Restaure une sauvegarde
     */
    public function restoreBackup(Backup $backup): bool
    {
        if (!$backup->isCompleted() || !$backup->fileExists()) {
            return false;
        }

        try {
            Log::info('Début de la restauration', ['backup_id' => $backup->id]);

            $success = match ($backup->type) {
                BackupType::FULL => $this->restoreFullBackup($backup),
                default => $this->restoreDataBackup($backup),
            };

            if ($success) {
                Log::info('Restauration terminée avec succès', ['backup_id' => $backup->id]);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('Échec de la restauration', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Restaure une sauvegarde complète
     */
    protected function restoreFullBackup(Backup $backup): bool
    {
        $filePath = $backup->getFullPath();
        
        $databaseName = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $command = sprintf(
            'mysql --host=%s --user=%s --password=%s %s < %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($databaseName),
            escapeshellarg($filePath)
        );

        exec($command, $output, $returnCode);

        return $returnCode === 0;
    }

    /**
     * Restaure une sauvegarde de données
     */
    protected function restoreDataBackup(Backup $backup): bool
    {
        $filePath = $backup->getFullPath();
        $data = json_decode(file_get_contents($filePath), true);

        if (!$data) {
            return false;
        }

        DB::transaction(function () use ($data) {
            foreach (['customers', 'licenses', 'products', 'users'] as $table) {
                if (isset($data[$table])) {
                    foreach ($data[$table] as $record) {
                        DB::table($table)->updateOrInsert(
                            ['id' => $record['id']],
                            (array) $record
                        );
                    }
                }
            }
        });

        return true;
    }

    /**
     * Obtient les statistiques des sauvegardes
     */
    public function getBackupStats(): array
    {
        return [
            'total' => Backup::count(),
            'successful' => Backup::where('status', BackupStatus::COMPLETED)->count(),
            'failed' => Backup::where('status', BackupStatus::FAILED)->count(),
            'running' => Backup::where('status', BackupStatus::RUNNING)->count(),
            'total_size' => Backup::where('status', BackupStatus::COMPLETED)->sum('file_size'),
            'last_backup' => Backup::where('status', BackupStatus::COMPLETED)
                ->orderBy('created_at', 'desc')
                ->first()?->created_at,
        ];
    }
}