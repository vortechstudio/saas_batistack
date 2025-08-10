# Système de Sauvegarde et Synchronisation Externe

## Vue d'ensemble

Ce système fournit une solution complète de sauvegarde automatique et de synchronisation avec des systèmes externes (CRM, ERP, Analytics) pour l'application SaaS BatiStack.

## ✅ Fonctionnalités Implémentées

### 🔄 Système de Sauvegarde
- **Sauvegardes complètes** : Export complet de la base de données en JSON
- **Sauvegardes incrémentales** : Sauvegarde des modifications depuis la dernière sauvegarde
- **Sauvegardes différentielles** : Sauvegarde des modifications depuis la dernière sauvegarde complète
- **Nettoyage automatique** : Suppression des anciennes sauvegardes
- **Restauration** : Possibilité de restaurer depuis une sauvegarde
- **Interface Filament** : Gestion complète via l'interface web

### 🌐 Synchronisation Externe
- **Multi-systèmes** : Support CRM, ERP, Analytics
- **Opérations multiples** : Create, Update, Sync, Export, Import
- **Retry automatique** : Relance des synchronisations échouées
- **Logs détaillés** : Traçabilité complète des synchronisations
- **Synchronisation en masse** : Traitement par lots
- **Interface Filament** : Monitoring et gestion via l'interface web

### 🕐 Planification Automatique
- **Sauvegarde quotidienne** : Tous les jours à 02:00
- **Synchronisation régulière** : Toutes les 4 heures
- **Sauvegarde hebdomadaire** : Dimanche à 01:00
- **Nettoyage automatique** : Suppression des anciens logs

## 📦 Installation

Les migrations ont été exécutées et les tables suivantes ont été créées :
- `backups` : Stockage des informations de sauvegarde
- `external_sync_logs` : Logs de synchronisation externe

## ⚙️ Configuration

### Configuration des Sauvegardes (`config/backup.php`)
```php
// Pilote de stockage par défaut
'default_storage_driver' => 'local',

// Configuration des pilotes de stockage
'storage_drivers' => [
    'local' => [...],
    's3' => [...],
    'ftp' => [...]
],

// Politiques de rétention
'retention' => [
    'full' => 30,        // 30 jours
    'incremental' => 7,  // 7 jours
    'differential' => 14 // 14 jours
]
```

### Configuration de la Synchronisation (`config/external_sync.php`)
```php
// Configuration des systèmes externes
'systems' => [
    'crm' => [
        'enabled' => true,
        'base_url' => env('CRM_API_URL'),
        'api_key' => env('CRM_API_KEY'),
        // ...
    ],
    // ...
]
```

## 🎯 Utilisation

### Commandes Artisan

#### Sauvegardes
```bash
# Créer une sauvegarde complète
php artisan backup:create --type=full

# Créer une sauvegarde incrémentale
php artisan backup:create --type=incremental

# Nettoyer les anciennes sauvegardes
php artisan backup:cleanup --days=30

# Tester le système
php artisan test:backup-system
```

#### Synchronisation
```bash
# Synchroniser tous les clients avec le CRM
php artisan sync:entity customer --system=crm --bulk

# Synchroniser un utilisateur spécifique
php artisan sync:entity user --id=123 --system=erp

# Synchronisation asynchrone
php artisan sync:entity product --system=analytics --async
```

### Interface Filament

Accédez à l'interface d'administration pour :
- **Sauvegardes** : `/admin/backups`
  - Créer, visualiser, télécharger et restaurer des sauvegardes
  - Voir les statistiques et l'historique
  - Configurer les planifications

- **Synchronisations** : `/admin/external-sync-logs`
  - Voir l'historique des synchronisations
  - Relancer les synchronisations échouées
  - Analyser les performances

### Programmation Automatique

Le système est configuré pour s'exécuter automatiquement :

```php
// Dans app/Console/Kernel.php
$schedule->command('backup:create --type=full')->daily();
$schedule->command('backup:create --type=incremental')->everySixHours();
$schedule->command('backup:cleanup')->weekly();

// Synchronisations
$schedule->command('sync:entity customer --system=crm --bulk')->hourly();
$schedule->command('sync:entity license --system=erp --bulk')->daily();
```

## 🔧 Services Principaux

### BackupService
- `createBackup()` : Créer une nouvelle sauvegarde
- `executeBackup()` : Exécuter une sauvegarde
- `restoreBackup()` : Restaurer une sauvegarde
- `cleanupOldBackups()` : Nettoyer les anciennes sauvegardes
- `getBackupStats()` : Obtenir les statistiques

### ExternalSyncService
- `syncEntity()` : Synchroniser une entité
- `executeBulkSync()` : Synchronisation en masse
- `retryFailedSync()` : Relancer une synchronisation échouée
- `buildApiEndpoint()` : Construire l'endpoint API

## 📊 Monitoring

### Widgets Filament
- **BackupStatsWidget** : Statistiques des sauvegardes
- **SyncStatsWidget** : Statistiques des synchronisations

### Notifications
- Email et Slack configurables
- Alertes en cas d'échec
- Rapports de performance

## 🛠️ Jobs Asynchrones

### CreateBackupJob
- Exécution des sauvegardes en arrière-plan
- Timeout : 3600 secondes
- Retry : 3 tentatives

### SyncEntityJob
- Synchronisation des entités en arrière-plan
- Timeout : 1800 secondes
- Retry : 5 tentatives avec délai exponentiel

## 🔒 Sécurité

- Chiffrement des clés API
- Validation des données avant synchronisation
- Logs d'audit complets
- Gestion des permissions Filament

## 📈 Performance

- Compression des sauvegardes
- Exécution asynchrone
- Optimisation des requêtes
- Cache des configurations

## 🐛 Dépannage

### Problèmes Courants

1. **Échec de sauvegarde**
   - Vérifier les permissions du dossier de stockage
   - Contrôler l'espace disque disponible
   - Valider la configuration de la base de données

2. **Échec de synchronisation**
   - Vérifier la connectivité réseau
   - Contrôler les clés API
   - Examiner les logs détaillés

3. **Performance lente**
   - Activer la compression
   - Utiliser l'exécution asynchrone
   - Optimiser les politiques de rétention

## 📝 Logs

Les logs sont disponibles dans :
- `storage/logs/laravel.log` : Logs généraux
- Interface Filament : Logs de synchronisation détaillés
- Notifications : Alertes en temps réel

## 🔄 Mise à Jour

Pour mettre à jour le système :
1. Sauvegarder la configuration actuelle
2. Exécuter les nouvelles migrations
3. Mettre à jour les fichiers de configuration
4. Tester les fonctionnalités

---

**Note** : Ce système est conçu pour être robuste et extensible. N'hésitez pas à adapter la configuration selon vos besoins spécifiques.