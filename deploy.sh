#!/bin/bash

# =============================================================================
# 🚀 SCRIPT DE DÉPLOIEMENT BATISTACK - INTELLIGENT
# =============================================================================
# Optimisé pour Vito Deploy - Détection automatique première installation
# Version: 3.0
# =============================================================================

set -e  # Arrêt en cas d'erreur

# Enregistrer le temps de début
DEPLOY_START_TIME=$(date +%s)

# Variables Vito Deploy
VITO_APP_PATH="${VITO_APP_PATH:-/home/vito}"
VITO_SITE_PATH="${VITO_SITE_PATH:-$PWD}"
VITO_SHARED_PATH="${VITO_SHARED_PATH:-$VITO_SITE_PATH/shared}"
VITO_BACKUP_DB="${VITO_BACKUP_DB:-false}"
VITO_RUN_SEEDERS="${VITO_RUN_SEEDERS:-false}"

# Variables de couleur pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Fonction d'affichage des messages
print_message() {
    echo -e "${2:-$GREEN}[$(date '+%H:%M:%S')] $1${NC}"
}

print_error() {
    echo -e "${RED}[ERREUR] $1${NC}" >&2
}

print_warning() {
    echo -e "${YELLOW}[ATTENTION] $1${NC}"
}

print_info() {
    echo -e "${BLUE}[INFO] $1${NC}"
}

# Fonction de détection première installation
is_fresh_install() {
    # Vérifier si .env existe et contient APP_KEY
    if [[ ! -f "$VITO_SHARED_PATH/.env" ]] || ! grep -q "APP_KEY=" "$VITO_SHARED_PATH/.env" 2>/dev/null || [[ $(grep "APP_KEY=" "$VITO_SHARED_PATH/.env" | cut -d'=' -f2) == "" ]]; then
        return 0  # Première installation
    fi
    return 1  # Installation existante
}

# =============================================================================
# 🎯 DÉBUT DU DÉPLOIEMENT
# =============================================================================

print_message "🚀 Démarrage du déploiement BatiStack..." "$CYAN"
print_info "Répertoire de travail: $VITO_SITE_PATH"

# Détection du type d'installation
if is_fresh_install; then
    print_message "🆕 PREMIÈRE INSTALLATION DÉTECTÉE" "$PURPLE"
    INSTALL_TYPE="fresh"
else
    print_message "🔄 MISE À JOUR DÉTECTÉE" "$BLUE"
    INSTALL_TYPE="update"
fi

# =============================================================================
# 📁 ÉTAPE 1: CRÉATION DES RÉPERTOIRES PARTAGÉS (Première installation uniquement)
# =============================================================================

if [[ "$INSTALL_TYPE" == "fresh" ]]; then
    print_message "📁 Création des répertoires partagés..." "$YELLOW"
    
    mkdir -p "$VITO_SHARED_PATH"
    mkdir -p "$VITO_SHARED_PATH/storage/app/public"
    mkdir -p "$VITO_SHARED_PATH/storage/framework/cache"
    mkdir -p "$VITO_SHARED_PATH/storage/framework/sessions"
    mkdir -p "$VITO_SHARED_PATH/storage/framework/views"
    mkdir -p "$VITO_SHARED_PATH/storage/logs"
    mkdir -p "$VITO_SHARED_PATH/bootstrap/cache"
    
    print_message "✅ Répertoires partagés créés" "$GREEN"
fi

# =============================================================================
# 🔗 ÉTAPE 2: CRÉATION DES LIENS SYMBOLIQUES
# =============================================================================

print_message "🔗 Configuration des liens symboliques..." "$YELLOW"

# Supprimer les anciens liens/dossiers s'ils existent
[[ -L "$VITO_SITE_PATH/storage" ]] && rm -f "$VITO_SITE_PATH/storage"
[[ -d "$VITO_SITE_PATH/storage" ]] && rm -rf "$VITO_SITE_PATH/storage"
[[ -L "$VITO_SITE_PATH/bootstrap/cache" ]] && rm -f "$VITO_SITE_PATH/bootstrap/cache"
[[ -d "$VITO_SITE_PATH/bootstrap/cache" ]] && rm -rf "$VITO_SITE_PATH/bootstrap/cache"

# Créer les nouveaux liens symboliques
ln -sf "$VITO_SHARED_PATH/storage" "$VITO_SITE_PATH/storage"
ln -sf "$VITO_SHARED_PATH/bootstrap/cache" "$VITO_SITE_PATH/bootstrap/cache"

# Lien pour .env
if [[ -f "$VITO_SHARED_PATH/.env" ]]; then
    [[ -L "$VITO_SITE_PATH/.env" ]] && rm -f "$VITO_SITE_PATH/.env"
    [[ -f "$VITO_SITE_PATH/.env" ]] && rm -f "$VITO_SITE_PATH/.env"
    ln -sf "$VITO_SHARED_PATH/.env" "$VITO_SITE_PATH/.env"
fi

print_message "✅ Liens symboliques configurés" "$GREEN"

# =============================================================================
# ⚙️ ÉTAPE 3: CONFIGURATION .ENV (Première installation uniquement)
# =============================================================================

if [[ "$INSTALL_TYPE" == "fresh" ]]; then
    print_message "⚙️ Configuration du fichier .env..." "$YELLOW"
    
    if [[ ! -f "$VITO_SHARED_PATH/.env" ]]; then
        if [[ -f "$VITO_SITE_PATH/.env.example" ]]; then
            cp "$VITO_SITE_PATH/.env.example" "$VITO_SHARED_PATH/.env"
            print_message "✅ Fichier .env créé depuis .env.example" "$GREEN"
        else
            print_error "Fichier .env.example introuvable!"
            exit 1
        fi
    fi
    
    # Créer le lien symbolique vers .env
    [[ -L "$VITO_SITE_PATH/.env" ]] && rm -f "$VITO_SITE_PATH/.env"
    [[ -f "$VITO_SITE_PATH/.env" ]] && rm -f "$VITO_SITE_PATH/.env"
    ln -sf "$VITO_SHARED_PATH/.env" "$VITO_SITE_PATH/.env"
fi

# =============================================================================
# 🔑 ÉTAPE 4: GÉNÉRATION APP_KEY (Première installation uniquement)
# =============================================================================

if [[ "$INSTALL_TYPE" == "fresh" ]]; then
    print_message "🔑 Génération de la clé d'application..." "$YELLOW"
    
    # Vérifier si APP_KEY existe déjà
    if ! grep -q "APP_KEY=" "$VITO_SHARED_PATH/.env" || [[ $(grep "APP_KEY=" "$VITO_SHARED_PATH/.env" | cut -d'=' -f2) == "" ]]; then
        php artisan key:generate --force
        print_message "✅ Clé d'application générée" "$GREEN"
    else
        print_info "Clé d'application déjà présente"
    fi
fi

# =============================================================================
# 💾 ÉTAPE 5: SAUVEGARDE BASE DE DONNÉES (Mise à jour uniquement)
# =============================================================================

if [[ "$INSTALL_TYPE" == "update" && "$VITO_BACKUP_DB" == "true" ]]; then
    print_message "💾 Sauvegarde de la base de données..." "$YELLOW"
    
    BACKUP_DIR="$VITO_SHARED_PATH/backups"
    mkdir -p "$BACKUP_DIR"
    
    # Récupérer les informations de la base de données
    DB_HOST=$(php artisan tinker --execute="echo config('database.connections.mysql.host');" 2>/dev/null | tail -1)
    DB_NAME=$(php artisan tinker --execute="echo config('database.connections.mysql.database');" 2>/dev/null | tail -1)
    DB_USER=$(php artisan tinker --execute="echo config('database.connections.mysql.username');" 2>/dev/null | tail -1)
    DB_PASS=$(php artisan tinker --execute="echo config('database.connections.mysql.password');" 2>/dev/null | tail -1)
    
    if [[ -n "$DB_NAME" && -n "$DB_USER" ]]; then
        BACKUP_FILE="$BACKUP_DIR/backup_$(date +%Y%m%d_%H%M%S).sql"
        
        if [[ -n "$DB_PASS" ]]; then
            mysqldump -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null
        else
            mysqldump -h"$DB_HOST" -u"$DB_USER" "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null
        fi
        
        if [[ $? -eq 0 ]]; then
            print_message "✅ Sauvegarde créée: $(basename "$BACKUP_FILE")" "$GREEN"
            
            # Garder seulement les 5 dernières sauvegardes
            cd "$BACKUP_DIR" && ls -t backup_*.sql | tail -n +6 | xargs -r rm --
        else
            print_warning "Échec de la sauvegarde de la base de données"
        fi
    else
        print_warning "Impossible de récupérer les informations de la base de données"
    fi
fi

# =============================================================================
# 📦 ÉTAPE 6: INSTALLATION DES DÉPENDANCES
# =============================================================================

print_message "📦 Installation des dépendances..." "$YELLOW"

# Composer
if [[ -f "composer.json" ]]; then
    if [[ "$INSTALL_TYPE" == "fresh" ]]; then
        composer install --no-dev --optimize-autoloader --no-interaction
    else
        composer install --no-dev --optimize-autoloader --no-interaction --no-scripts
    fi
    print_message "✅ Dépendances Composer installées" "$GREEN"
fi

# NPM/Yarn
if [[ -f "package.json" ]]; then
    if command -v yarn >/dev/null 2>&1; then
        yarn install --frozen-lockfile --production=false
        print_message "✅ Dépendances Yarn installées" "$GREEN"
    elif command -v npm >/dev/null 2>&1; then
        npm ci
        print_message "✅ Dépendances NPM installées" "$GREEN"
    fi
fi

# =============================================================================
# 🎨 ÉTAPE 7: COMPILATION DES ASSETS
# =============================================================================

print_message "🎨 Compilation des assets..." "$YELLOW"

if [[ -f "vite.config.js" ]] || [[ -f "vite.config.ts" ]]; then
    if command -v yarn >/dev/null 2>&1; then
        yarn build
    elif command -v npm >/dev/null 2>&1; then
        npm run build
    fi
    print_message "✅ Assets compilés avec Vite" "$GREEN"
elif [[ -f "webpack.mix.js" ]]; then
    if command -v yarn >/dev/null 2>&1; then
        yarn production
    elif command -v npm >/dev/null 2>&1; then
        npm run production
    fi
    print_message "✅ Assets compilés avec Mix" "$GREEN"
fi

# =============================================================================
# 🗃️ ÉTAPE 8: MIGRATIONS ET SEEDERS
# =============================================================================

print_message "🗃️ Exécution des migrations..." "$YELLOW"

if [[ "$INSTALL_TYPE" == "fresh" ]]; then
    # Première installation - migrations + seeders optionnels
    php artisan migrate --force
    print_message "✅ Migrations exécutées" "$GREEN"
    
    if [[ "$VITO_RUN_SEEDERS" == "true" ]]; then
        print_message "🌱 Exécution des seeders..." "$YELLOW"
        php artisan db:seed --force
        print_message "✅ Seeders exécutés" "$GREEN"
    fi
else
    # Mise à jour - migrations uniquement
    php artisan migrate --force
    print_message "✅ Migrations exécutées" "$GREEN"
fi

# =============================================================================
# 🔧 ÉTAPE 9: CONFIGURATION DES PERMISSIONS
# =============================================================================

print_message "🔧 Configuration des permissions..." "$YELLOW"

# Permissions pour les répertoires de stockage
chmod -R 755 "$VITO_SHARED_PATH/storage"
chmod -R 755 "$VITO_SHARED_PATH/bootstrap/cache"

# Permissions spécifiques pour les logs et cache
find "$VITO_SHARED_PATH/storage" -type f -exec chmod 644 {} \;
find "$VITO_SHARED_PATH/bootstrap/cache" -type f -exec chmod 644 {} \;

print_message "✅ Permissions configurées" "$GREEN"

# =============================================================================
# 🔗 ÉTAPE 10: LIEN DE STOCKAGE PUBLIC (Première installation uniquement)
# =============================================================================

if [[ "$INSTALL_TYPE" == "fresh" ]]; then
    print_message "🔗 Création du lien de stockage public..." "$YELLOW"
    
    php artisan storage:link --force
    print_message "✅ Lien de stockage créé" "$GREEN"
fi

# =============================================================================
# ⚡ ÉTAPE 11: OPTIMISATIONS LARAVEL
# =============================================================================

print_message "⚡ Optimisations Laravel..." "$YELLOW"

# Cache des configurations
php artisan config:cache
print_message "✅ Configuration mise en cache" "$GREEN"

# Cache des routes
php artisan route:cache
print_message "✅ Routes mises en cache" "$GREEN"

# Cache des vues
php artisan view:cache
print_message "✅ Vues mises en cache" "$GREEN"

# Cache des événements (Laravel 11+)
if php artisan list | grep -q "event:cache"; then
    php artisan event:cache
    print_message "✅ Événements mis en cache" "$GREEN"
fi

# Optimisation de l'autoloader
composer dump-autoload --optimize --classmap-authoritative --no-dev
print_message "✅ Autoloader optimisé" "$GREEN"

# =============================================================================
# 🔄 ÉTAPE 12: REDÉMARRAGE DES SERVICES
# =============================================================================

print_message "🔄 Redémarrage des services..." "$YELLOW"

# Redémarrer les queues si elles existent
if pgrep -f "artisan queue:work" > /dev/null; then
    print_info "Redémarrage des workers de queue..."
    php artisan queue:restart
    print_message "✅ Workers de queue redémarrés" "$GREEN"
fi

# Redémarrer Horizon si installé
if php artisan list | grep -q "horizon:terminate"; then
    print_info "Redémarrage d'Horizon..."
    php artisan horizon:terminate
    print_message "✅ Horizon redémarré" "$GREEN"
fi

# Redémarrer le scheduler si nécessaire
if command -v supervisorctl >/dev/null 2>&1; then
    if supervisorctl status | grep -q "laravel-scheduler"; then
        supervisorctl restart laravel-scheduler
        print_message "✅ Scheduler redémarré" "$GREEN"
    fi
fi

# =============================================================================
# 🧹 ÉTAPE 13: NETTOYAGE FINAL
# =============================================================================

print_message "🧹 Nettoyage final..." "$YELLOW"

# Nettoyage des logs anciens (> 7 jours)
if [[ -d "$VITO_SHARED_PATH/storage/logs" ]]; then
    find "$VITO_SHARED_PATH/storage/logs" -name "*.log" -type f -mtime +7 -delete 2>/dev/null || true
    print_info "Logs anciens supprimés"
fi

# Nettoyage des caches NPM/Yarn
if command -v yarn >/dev/null 2>&1; then
    yarn cache clean --silent 2>/dev/null || true
elif command -v npm >/dev/null 2>&1; then
    npm cache clean --force --silent 2>/dev/null || true
fi

# Nettoyage des fichiers temporaires
find /tmp -name "php*" -user "$(whoami)" -mtime +1 -delete 2>/dev/null || true

print_message "✅ Nettoyage terminé" "$GREEN"

# =============================================================================
# 🏥 ÉTAPE 14: VÉRIFICATIONS DE SANTÉ
# =============================================================================

print_message "🏥 Vérifications de santé..." "$YELLOW"

# Test de l'application Laravel
if php artisan --version >/dev/null 2>&1; then
    print_message "✅ Application Laravel fonctionnelle" "$GREEN"
else
    print_error "❌ Problème avec l'application Laravel"
    exit 1
fi

# Test de la connectivité à la base de données
if php artisan migrate:status >/dev/null 2>&1; then
    print_message "✅ Base de données accessible" "$GREEN"
else
    print_warning "⚠️ Problème de connectivité à la base de données"
fi

# Vérifier que les liens symboliques sont corrects
if [[ -L "$VITO_SITE_PATH/storage" && -L "$VITO_SITE_PATH/.env" ]]; then
    print_message "✅ Liens symboliques corrects" "$GREEN"
else
    print_warning "⚠️ Problème avec les liens symboliques"
fi

# =============================================================================
# 🎉 DÉPLOIEMENT TERMINÉ
# =============================================================================

DEPLOY_END_TIME=$(date +%s)
DEPLOY_DURATION=$((DEPLOY_END_TIME - ${DEPLOY_START_TIME:-$DEPLOY_END_TIME}))

print_message "🎉 DÉPLOIEMENT BATISTACK TERMINÉ AVEC SUCCÈS!" "$GREEN"
print_info "Type: $([[ "$INSTALL_TYPE" == "fresh" ]] && echo "Première installation" || echo "Mise à jour")"
print_info "Durée: ${DEPLOY_DURATION}s"
print_info "Timestamp: $(date '+%Y-%m-%d %H:%M:%S')"

if [[ "$INSTALL_TYPE" == "fresh" ]]; then
    print_message "🔧 PROCHAINES ÉTAPES:" "$CYAN"
    print_info "1. Configurez votre fichier .env dans $VITO_SHARED_PATH/.env"
    print_info "2. Configurez votre domaine dans Vito Deploy"
    print_info "3. Configurez SSL/TLS"
    print_info "4. Testez votre application"
else
    print_message "✨ Votre application BatiStack a été mise à jour avec succès!" "$CYAN"
fi

# Enregistrer le timestamp du déploiement
echo "$(date '+%Y-%m-%d %H:%M:%S') - Déploiement $INSTALL_TYPE réussi" >> "$VITO_SHARED_PATH/deploy.log"

exit 0