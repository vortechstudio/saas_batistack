<?php

/**
 * Script de déploiement pour l'environnement de production
 * BatiStack SaaS - Initialisation sécurisée
 */

echo "🚀 BatiStack SaaS - Script de déploiement production\n";
echo "==================================================\n\n";

// Vérifier l'environnement
if (!file_exists('.env')) {
    echo "❌ Erreur : Fichier .env non trouvé\n";
    echo "   Copiez .env.example vers .env et configurez vos variables\n";
    exit(1);
}

// Vérifier que nous ne sommes pas en local
$env = trim(file_get_contents('.env'));
if (strpos($env, 'APP_ENV=local') !== false) {
    echo "⚠️  Attention : APP_ENV est défini sur 'local'\n";
    echo "   Assurez-vous d'être en environnement de production\n";
    
    echo "\nContinuer quand même ? (y/N): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) !== 'y' && trim($line) !== 'Y') {
        echo "Déploiement annulé.\n";
        exit(0);
    }
    fclose($handle);
}

echo "📋 Étapes du déploiement :\n";
echo "1. Installation des dépendances\n";
echo "2. Génération de la clé d'application\n";
echo "3. Exécution des migrations\n";
echo "4. Initialisation des données de production\n";
echo "5. Optimisation pour la production\n";
echo "6. Configuration des permissions\n\n";

echo "Démarrer le déploiement ? (y/N): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) !== 'y' && trim($line) !== 'Y') {
    echo "Déploiement annulé.\n";
    exit(0);
}
fclose($handle);

echo "\n🔧 Démarrage du déploiement...\n\n";

// 1. Installation des dépendances
echo "📦 Installation des dépendances Composer...\n";
exec('composer install --no-dev --optimize-autoloader', $output, $return);
if ($return !== 0) {
    echo "❌ Erreur lors de l'installation des dépendances\n";
    exit(1);
}

// 2. Génération de la clé d'application
echo "🔑 Génération de la clé d'application...\n";
exec('php artisan key:generate --force', $output, $return);
if ($return !== 0) {
    echo "❌ Erreur lors de la génération de la clé\n";
    exit(1);
}

// 3. Exécution des migrations
echo "🗄️  Exécution des migrations...\n";
exec('php artisan migrate --force', $output, $return);
if ($return !== 0) {
    echo "❌ Erreur lors des migrations\n";
    exit(1);
}

// 4. Initialisation des données de production
echo "🌱 Initialisation des données de production...\n";
exec('php artisan db:seed --class=ProductionSeeder', $output, $return);
if ($return !== 0) {
    echo "❌ Erreur lors du seeding\n";
    exit(1);
}

// 5. Optimisation pour la production
echo "⚡ Optimisation pour la production...\n";
exec('php artisan config:cache', $output, $return);
exec('php artisan route:cache', $output, $return);
exec('php artisan view:cache', $output, $return);

// 6. Configuration des permissions
echo "🔒 Configuration des permissions...\n";
if (PHP_OS_FAMILY !== 'Windows') {
    exec('chmod -R 755 storage bootstrap/cache');
    exec('chown -R www-data:www-data storage bootstrap/cache');
}

// 7. Installation des assets NPM (si nécessaire)
if (file_exists('package.json')) {
    echo "🎨 Installation et compilation des assets...\n";
    exec('npm ci --production', $output, $return);
    exec('npm run build', $output, $return);
}

echo "\n✅ Déploiement terminé avec succès !\n\n";

echo "🔐 INFORMATIONS IMPORTANTES :\n";
echo "============================\n";
echo "• Connectez-vous avec : admin@batistack.com\n";
echo "• Le mot de passe temporaire a été affiché lors du seeding\n";
echo "• CHANGEZ IMMÉDIATEMENT le mot de passe après la première connexion\n";
echo "• Configurez vos variables d'environnement (.env)\n";
echo "• Configurez votre serveur web (Apache/Nginx)\n";
echo "• Configurez SSL/TLS pour HTTPS\n";
echo "• Configurez les tâches cron pour les jobs\n\n";

echo "📚 Prochaines étapes recommandées :\n";
echo "===================================\n";
echo "1. Tester la connexion admin\n";
echo "2. Configurer les paramètres SMTP\n";
echo "3. Configurer Stripe (si applicable)\n";
echo "4. Configurer les sauvegardes automatiques\n";
echo "5. Configurer la surveillance (logs, monitoring)\n";
echo "6. Tester toutes les fonctionnalités critiques\n\n";

echo "🎉 Votre application BatiStack SaaS est prête !\n";