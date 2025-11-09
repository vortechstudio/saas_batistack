#!/bin/bash

# =============================================================================
# SCRIPT D'INSTALLATION NEUVE - BATISTACK SAAS
# =============================================================================
# Ce script est conçu pour la première installation de l'application BatiStack
# sur un serveur via Vito Deploy
# =============================================================================

set -e  # Arrêter le script en cas d'erreur

# Variables Vito Deploy
RELEASE_PATH=${VITO_RELEASE_PATH:-$PWD}
SHARED_PATH=${VITO_SHARED_PATH:-"$PWD/../shared"}

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages
log_info() {
    echo -e "${BLUE}[VITO-INSTALL]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[VITO-INSTALL]${NC} ✅ $1"
}

log_warning() {
    echo -e "${YELLOW}[VITO-INSTALL]${NC} ⚠️  $1"
}

log_error() {
    echo -e "${RED}[VITO-INSTALL]${NC} ❌ $1"
}

# =============================================================================
# DÉBUT DE L'INSTALLATION
# =============================================================================

log_info "🚀 Démarrage de l'installation neuve de BatiStack..."
log_info "📁 Répertoire de release: $RELEASE_PATH"
log_info "📁 Répertoire partagé: $SHARED_PATH"

# =============================================================================
# 1. CRÉATION DES RÉPERTOIRES PARTAGÉS
# =============================================================================

log_info "📂 Création des répertoires partagés..."

# Créer les répertoires partagés s'ils n'existent pas
mkdir -p "$SHARED_PATH/storage/app/private"
mkdir -p "$SHARED_PATH/storage/app/public"
mkdir -p "$SHARED_PATH/storage/framework/cache"
mkdir -p "$SHARED_PATH/storage/framework/sessions"
mkdir -p "$SHARED_PATH/storage/framework/testing"
mkdir -p "$SHARED_PATH/storage/framework/views"
mkdir -p "$SHARED_PATH/storage/logs"
mkdir -p "$SHARED_PATH/bootstrap/cache"

log_success "Répertoires partagés créés"

# =============================================================================
# 2. CONFIGURATION DU FICHIER .ENV
# =============================================================================

log_info "⚙️  Configuration du fichier .env..."

# Copier le fichier .env.example vers le répertoire partagé si .env n'existe pas
if [ ! -f "$SHARED_PATH/.env" ]; then
    if [ -f "$RELEASE_PATH/.env.example" ]; then
        cp "$RELEASE_PATH/.env.example" "$SHARED_PATH/.env"
        log_success "Fichier .env créé à partir de .env.example"
    else
        log_error "Fichier .env.example introuvable!"
        exit 1
    fi
else
    log_warning "Fichier .env existe déjà, conservation de la configuration actuelle"
fi

# =============================================================================
# 3. LIENS SYMBOLIQUES
# =============================================================================

log_info "🔗 Configuration des liens symboliques..."

# Supprimer les répertoires/fichiers existants s'ils existent
[ -d "$RELEASE_PATH/storage" ] && rm -rf "$RELEASE_PATH/storage"
[ -f "$RELEASE_PATH/.env" ] && rm -f "$RELEASE_PATH/.env"
[ -d "$RELEASE_PATH/bootstrap/cache" ] && rm -rf "$RELEASE_PATH/bootstrap/cache"

# Créer les liens symboliques
ln -nfs "$SHARED_PATH/.env" "$RELEASE_PATH/.env"
ln -nfs "$SHARED_PATH/storage" "$RELEASE_PATH/storage"
ln -nfs "$SHARED_PATH/bootstrap/cache" "$RELEASE_PATH/bootstrap/cache"

log_success "Liens symboliques configurés"

# =============================================================================
# 4. INSTALLATION DES DÉPENDANCES COMPOSER
# =============================================================================

log_info "📦 Installation des dépendances Composer..."

composer install --no-dev --optimize-autoloader --classmap-authoritative --no-interaction --prefer-dist

log_success "Dépendances Composer installées"

# =============================================================================
# 5. GÉNÉRATION DE LA CLÉ D'APPLICATION
# =============================================================================

log_info "🔑 Génération de la clé d'application..."

# Vérifier si APP_KEY existe dans le .env
if ! grep -q "APP_KEY=" "$SHARED_PATH/.env" || grep -q "APP_KEY=$" "$SHARED_PATH/.env"; then
    php artisan key:generate --force
    log_success "Clé d'application générée"
else
    log_warning "Clé d'application déjà présente"
fi

# =============================================================================
# 6. INSTALLATION DES DÉPENDANCES NPM/YARN ET BUILD
# =============================================================================

log_info "🎨 Installation des dépendances frontend et compilation..."

if command -v yarn &> /dev/null; then
    yarn install --frozen-lockfile --production=false
    yarn build
    log_success "Assets compilés avec Yarn"
else
    npm ci
    npm run build
    log_success "Assets compilés avec NPM"
fi

# =============================================================================
# 7. CONFIGURATION DES PERMISSIONS
# =============================================================================

log_info "🔒 Configuration des permissions..."

# Définir les bonnes permissions
chown -R www-data:www-data "$SHARED_PATH/storage"
chown -R www-data:www-data "$SHARED_PATH/bootstrap/cache"
chmod -R 755 "$SHARED_PATH/storage"
chmod -R 755 "$SHARED_PATH/bootstrap/cache"

log_success "Permissions configurées"

# =============================================================================
# 8. OPTIMISATION LARAVEL
# =============================================================================

log_info "⚡ Optimisation de l'application Laravel..."

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Commandes spécifiques à BatiStack (si elles existent)
php artisan icons:cache 2>/dev/null || log_warning "Commande icons:cache non disponible"
php artisan lang:cache 2>/dev/null || log_warning "Commande lang:cache non disponible"

log_success "Optimisations appliquées"

# =============================================================================
# 9. MIGRATION DE LA BASE DE DONNÉES
# =============================================================================

log_info "🗄️  Exécution des migrations de base de données..."

php artisan migrate --force

log_success "Migrations exécutées"

# =============================================================================
# 10. SEEDERS (OPTIONNEL)
# =============================================================================

log_info "🌱 Exécution des seeders..."

# Exécuter les seeders seulement si la variable VITO_RUN_SEEDERS est définie
if [ "${VITO_RUN_SEEDERS:-false}" = "true" ]; then
    php artisan db:seed --force
    log_success "Seeders exécutés"
else
    log_warning "Seeders ignorés (définir VITO_RUN_SEEDERS=true pour les exécuter)"
fi

# =============================================================================
# 11. CRÉATION DU LIEN DE STOCKAGE PUBLIC
# =============================================================================

log_info "🔗 Création du lien de stockage public..."

php artisan storage:link

log_success "Lien de stockage créé"

# =============================================================================
# 12. CONFIGURATION DES QUEUES ET HORIZON
# =============================================================================

log_info "🔄 Configuration des queues..."

# Redémarrer les queues
php artisan queue:restart

# Terminer Horizon s'il est en cours d'exécution
php artisan horizon:terminate 2>/dev/null || log_warning "Horizon non disponible ou non démarré"

log_success "Queues configurées"

# =============================================================================
# 13. NETTOYAGE FINAL
# =============================================================================

log_info "🧹 Nettoyage final..."

# Nettoyer les caches de développement
php artisan cache:clear
php artisan config:clear

# Nettoyer les caches NPM/Yarn
if command -v yarn &> /dev/null; then
    yarn cache clean
else
    npm cache clean --force
fi

log_success "Nettoyage terminé"

# =============================================================================
# 14. VÉRIFICATION DE SANTÉ
# =============================================================================

log_info "🏥 Vérification de santé de l'application..."

# Vérifier que l'application répond
if php artisan tinker --execute="echo 'OK';" &>/dev/null; then
    log_success "Application opérationnelle"
else
    log_error "Problème détecté avec l'application"
    exit 1
fi

# =============================================================================
# FIN DE L'INSTALLATION
# =============================================================================

log_success "🎉 Installation neuve de BatiStack terminée avec succès!"
log_info "📋 Prochaines étapes recommandées:"
log_info "   1. Vérifier la configuration .env"
log_info "   2. Configurer les services externes (Stripe, etc.)"
log_info "   3. Tester l'application"
log_info "   4. Configurer les sauvegardes automatiques"

exit 0