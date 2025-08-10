# 🚀 Guide de Déploiement Production - BatiStack SaaS

Ce guide vous accompagne dans le déploiement sécurisé de votre application BatiStack SaaS en environnement de production.

## 📋 Prérequis

### Serveur
- **PHP** : 8.2 ou supérieur
- **Composer** : Version récente
- **Base de données** : MySQL 8.0+ ou PostgreSQL 13+
- **Serveur web** : Apache 2.4+ ou Nginx 1.18+
- **Node.js** : 18+ (pour la compilation des assets)
- **SSL/TLS** : Certificat valide configuré

### Extensions PHP Requises
```bash
php-cli php-fpm php-mysql php-pgsql php-sqlite3 php-redis
php-memcached php-gd php-xml php-mbstring php-curl php-zip
php-intl php-bcmath php-soap php-imagick
```

## 🔧 Méthodes de Déploiement

### Méthode 1 : Commande Artisan (Recommandée)

```bash
# 1. Cloner le projet
git clone https://github.com/votre-repo/batistack-saas.git
cd batistack-saas

# 2. Configurer l'environnement
cp .env.example .env
# Éditer .env avec vos paramètres de production

# 3. Installer les dépendances
composer install --no-dev --optimize-autoloader

# 4. Initialiser l'application
php artisan batistack:init-production
```

### Méthode 2 : Script PHP

```bash
# 1. Cloner et configurer comme ci-dessus
# 2. Exécuter le script de déploiement
php deploy-production.php
```

### Méthode 3 : Manuelle

```bash
# 1. Installation des dépendances
composer install --no-dev --optimize-autoloader

# 2. Configuration
php artisan key:generate --force

# 3. Base de données
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder

# 4. Optimisations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Assets (si nécessaire)
npm ci --production
npm run build
```

## ⚙️ Configuration de l'Environnement

### Fichier .env de Production

```env
# Application
APP_NAME="BatiStack SaaS"
APP_ENV=production
APP_KEY=base64:VOTRE_CLE_GENEREE
APP_DEBUG=false
APP_URL=https://votre-domaine.com

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=batistack_prod
DB_USERNAME=batistack_user
DB_PASSWORD=mot_de_passe_securise

# Cache et Sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.votre-provider.com
MAIL_PORT=587
MAIL_USERNAME=votre@email.com
MAIL_PASSWORD=votre_mot_de_passe
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@votre-domaine.com
MAIL_FROM_NAME="${APP_NAME}"

# Stripe (si applicable)
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Sécurité
BCRYPT_ROUNDS=12
```

## 🔒 Configuration du Serveur Web

### Apache (.htaccess)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

# Sécurité
<Files .env>
    Order allow,deny
    Deny from all
</Files>

# Headers de sécurité
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

### Nginx

```nginx
server {
    listen 443 ssl http2;
    server_name votre-domaine.com;
    root /var/www/batistack-saas/public;

    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 📊 Configuration des Tâches Cron

```bash
# Éditer le crontab
crontab -e

# Ajouter cette ligne
* * * * * cd /var/www/batistack-saas && php artisan schedule:run >> /dev/null 2>&1
```

## 🔐 Sécurité Post-Déploiement

### 1. Changement du Mot de Passe Admin

```bash
# Se connecter à l'interface admin
# URL: https://votre-domaine.com/admin
# Email: admin@batistack.com
# Mot de passe: (affiché lors du seeding)

# Changer immédiatement le mot de passe !
```

### 2. Configuration 2FA

1. Connectez-vous en tant que Super Admin
2. Allez dans le menu "2FA"
3. Activez l'authentification à deux facteurs
4. Sauvegardez les codes de récupération

### 3. Permissions des Fichiers

```bash
# Propriétaire des fichiers
chown -R www-data:www-data /var/www/batistack-saas

# Permissions des dossiers
find /var/www/batistack-saas -type d -exec chmod 755 {} \;

# Permissions des fichiers
find /var/www/batistack-saas -type f -exec chmod 644 {} \;

# Permissions spéciales
chmod -R 775 /var/www/batistack-saas/storage
chmod -R 775 /var/www/batistack-saas/bootstrap/cache
```

## 📈 Monitoring et Logs

### Configuration des Logs

```php
// config/logging.php
'channels' => [
    'production' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'error',
        'days' => 30,
    ],
],
```

### Surveillance Recommandée

- **Uptime** : Pingdom, UptimeRobot
- **Performance** : New Relic, DataDog
- **Erreurs** : Sentry, Bugsnag
- **Logs** : ELK Stack, Splunk

## 🔄 Sauvegarde

### Script de Sauvegarde Automatique

```bash
#!/bin/bash
# backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/batistack"
APP_DIR="/var/www/batistack-saas"

# Créer le dossier de sauvegarde
mkdir -p $BACKUP_DIR

# Sauvegarde de la base de données
mysqldump -u username -p password batistack_prod > $BACKUP_DIR/db_$DATE.sql

# Sauvegarde des fichiers
tar -czf $BACKUP_DIR/files_$DATE.tar.gz $APP_DIR/storage/app

# Nettoyer les anciennes sauvegardes (garder 30 jours)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

### Cron pour la Sauvegarde

```bash
# Sauvegarde quotidienne à 2h du matin
0 2 * * * /path/to/backup.sh
```

## 🚨 Checklist de Déploiement

### Avant le Déploiement

- [ ] Serveur configuré avec tous les prérequis
- [ ] Base de données créée et accessible
- [ ] Certificat SSL installé et configuré
- [ ] Variables d'environnement configurées
- [ ] DNS pointant vers le serveur

### Pendant le Déploiement

- [ ] Code déployé depuis la branche main/master
- [ ] Dépendances installées
- [ ] Migrations exécutées
- [ ] Seeder de production exécuté
- [ ] Caches générés
- [ ] Permissions des fichiers configurées

### Après le Déploiement

- [ ] Test de connexion admin
- [ ] Changement du mot de passe admin
- [ ] Configuration 2FA
- [ ] Test des fonctionnalités critiques
- [ ] Configuration du monitoring
- [ ] Configuration des sauvegardes
- [ ] Test des emails
- [ ] Test des paiements (si applicable)

## 🆘 Dépannage

### Erreurs Communes

#### Erreur 500
```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Vérifier les permissions
ls -la storage/
ls -la bootstrap/cache/
```

#### Base de données inaccessible
```bash
# Tester la connexion
php artisan tinker
>>> DB::connection()->getPdo();
```

#### Cache corrompu
```bash
# Nettoyer tous les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Support

Pour obtenir de l'aide :
1. Consultez les logs de l'application
2. Vérifiez la documentation technique
3. Contactez l'équipe de support

## 📞 Contacts

- **Support Technique** : support@batistack.com
- **Urgences** : +33 X XX XX XX XX
- **Documentation** : https://docs.batistack.com

---

**⚠️ Important** : Ce guide contient des informations sensibles. Gardez-le en sécurité et ne le partagez qu'avec les personnes autorisées.