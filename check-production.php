<?php

/**
 * Script de vérification post-déploiement
 * BatiStack SaaS - Vérification de l'état de production
 */

use Illuminate\Support\Facades\DB;

echo "🔍 BatiStack SaaS - Vérification de Production\n";
echo "==============================================\n\n";

$errors = [];
$warnings = [];
$checks = [];

// Fonction utilitaire pour les vérifications
function checkItem($description, $condition, $errorMessage = null, $warningMessage = null) {
    global $errors, $warnings, $checks;

    $checks[] = $description;

    if ($condition) {
        echo "✅ $description\n";
        return true;
    } else {
        if ($errorMessage) {
            echo "❌ $description - $errorMessage\n";
            $errors[] = "$description: $errorMessage";
        } elseif ($warningMessage) {
            echo "⚠️  $description - $warningMessage\n";
            $warnings[] = "$description: $warningMessage";
        } else {
            echo "❌ $description\n";
            $errors[] = $description;
        }
        return false;
    }
}

echo "🔧 Vérifications de l'environnement...\n";
echo "=====================================\n";

// Vérification du fichier .env
checkItem(
    "Fichier .env présent",
    file_exists('.env'),
    "Le fichier .env est manquant"
);

// Vérification de l'environnement
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');

    checkItem(
        "APP_ENV configuré pour la production",
        strpos($envContent, 'APP_ENV=production') !== false,
        null,
        "APP_ENV n'est pas défini sur 'production'"
    );

    checkItem(
        "APP_DEBUG désactivé",
        strpos($envContent, 'APP_DEBUG=false') !== false,
        "APP_DEBUG doit être défini sur 'false' en production"
    );

    checkItem(
        "APP_KEY configuré",
        strpos($envContent, 'APP_KEY=base64:') !== false,
        "APP_KEY n'est pas configuré"
    );

    checkItem(
        "Configuration de base de données présente",
        strpos($envContent, 'DB_DATABASE=') !== false &&
        strpos($envContent, 'DB_USERNAME=') !== false,
        "Configuration de base de données incomplète"
    );

    checkItem(
        "Configuration SMTP présente",
        strpos($envContent, 'MAIL_HOST=') !== false &&
        strpos($envContent, 'MAIL_USERNAME=') !== false,
        null,
        "Configuration SMTP non définie"
    );
}

echo "\n🗂️  Vérifications des fichiers et dossiers...\n";
echo "=============================================\n";

// Vérification des dossiers critiques
checkItem(
    "Dossier storage accessible en écriture",
    is_writable('storage'),
    "Le dossier storage n'est pas accessible en écriture"
);

checkItem(
    "Dossier bootstrap/cache accessible en écriture",
    is_writable('bootstrap/cache'),
    "Le dossier bootstrap/cache n'est pas accessible en écriture"
);

checkItem(
    "Dossier public accessible",
    is_dir('public') && is_readable('public'),
    "Le dossier public n'est pas accessible"
);

// Vérification des fichiers de cache
checkItem(
    "Cache de configuration présent",
    file_exists('bootstrap/cache/config.php'),
    null,
    "Cache de configuration non généré (exécutez: php artisan config:cache)"
);

checkItem(
    "Cache des routes présent",
    file_exists('bootstrap/cache/routes-v7.php'),
    null,
    "Cache des routes non généré (exécutez: php artisan route:cache)"
);

echo "\n🔌 Vérifications des extensions PHP...\n";
echo "=====================================\n";

$requiredExtensions = [
    'pdo', 'pdo_mysql', 'mbstring', 'xml', 'ctype', 'json',
    'openssl', 'tokenizer', 'curl', 'gd', 'zip'
];

foreach ($requiredExtensions as $extension) {
    checkItem(
        "Extension PHP '$extension'",
        extension_loaded($extension),
        "Extension PHP '$extension' manquante"
    );
}

echo "\n🗄️  Vérifications de la base de données...\n";
echo "=========================================\n";

try {
    // Charger Laravel pour les vérifications de base de données
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    // Test de connexion à la base de données
    $pdo = DB::connection()->getPdo();
    checkItem(
        "Connexion à la base de données",
        $pdo !== null,
        "Impossible de se connecter à la base de données"
    );

    // Vérification des tables principales
    $tables = ['users', 'roles', 'permissions', 'modules', 'products'];
    foreach ($tables as $table) {
        $exists = DB::getSchemaBuilder()->hasTable($table);
        checkItem(
            "Table '$table' présente",
            $exists,
            "Table '$table' manquante - exécutez les migrations"
        );
    }

    // Vérification du Super Admin
    $superAdmin = DB::table('users')
        ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->where('roles.name', 'Super Admin')
        ->where('model_has_roles.model_type', 'App\\Models\\User')
        ->first();

    checkItem(
        "Compte Super Admin présent",
        $superAdmin !== null,
        "Aucun compte Super Admin trouvé - exécutez le ProductionSeeder"
    );

    // Vérification des permissions
    $permissionsCount = DB::table('permissions')->count();
    checkItem(
        "Permissions créées ($permissionsCount)",
        $permissionsCount > 0,
        "Aucune permission trouvée - exécutez le ProductionSeeder"
    );

    // Vérification des modules
    $modulesCount = DB::table('modules')->count();
    checkItem(
        "Modules créés ($modulesCount)",
        $modulesCount > 0,
        "Aucun module trouvé - exécutez le ProductionSeeder"
    );

} catch (Exception $e) {
    checkItem(
        "Connexion à la base de données",
        false,
        "Erreur de connexion: " . $e->getMessage()
    );
}

echo "\n🔒 Vérifications de sécurité...\n";
echo "==============================\n";

// Vérification des fichiers sensibles
$sensitiveFiles = ['.env', 'composer.json', 'artisan'];
foreach ($sensitiveFiles as $file) {
    if (file_exists("public/$file")) {
        checkItem(
            "Fichier '$file' non exposé publiquement",
            false,
            "Le fichier '$file' est accessible publiquement"
        );
    } else {
        checkItem(
            "Fichier '$file' non exposé publiquement",
            true
        );
    }
}

// Vérification HTTPS (si possible)
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    checkItem("HTTPS activé", true);
} else {
    checkItem(
        "HTTPS activé",
        false,
        null,
        "HTTPS non détecté - assurez-vous que SSL/TLS est configuré"
    );
}

echo "\n📊 Résumé des vérifications...\n";
echo "=============================\n";

$totalChecks = count($checks);
$errorCount = count($errors);
$warningCount = count($warnings);
$successCount = $totalChecks - $errorCount - $warningCount;

echo "Total des vérifications : $totalChecks\n";
echo "✅ Succès : $successCount\n";
echo "⚠️  Avertissements : $warningCount\n";
echo "❌ Erreurs : $errorCount\n\n";

if ($errorCount > 0) {
    echo "🚨 ERREURS CRITIQUES À CORRIGER :\n";
    echo "=================================\n";
    foreach ($errors as $error) {
        echo "• $error\n";
    }
    echo "\n";
}

if ($warningCount > 0) {
    echo "⚠️  AVERTISSEMENTS À EXAMINER :\n";
    echo "==============================\n";
    foreach ($warnings as $warning) {
        echo "• $warning\n";
    }
    echo "\n";
}

if ($errorCount === 0) {
    echo "🎉 Félicitations ! Votre application BatiStack SaaS est prête pour la production !\n\n";
    echo "📋 Prochaines étapes recommandées :\n";
    echo "===================================\n";
    echo "1. Tester la connexion admin (admin@batistack.com)\n";
    echo "2. Changer le mot de passe du Super Admin\n";
    echo "3. Configurer l'authentification à deux facteurs\n";
    echo "4. Tester toutes les fonctionnalités critiques\n";
    echo "5. Configurer les sauvegardes automatiques\n";
    echo "6. Configurer le monitoring et les alertes\n";
} else {
    echo "❌ Des erreurs critiques doivent être corrigées avant la mise en production.\n";
    exit(1);
}

echo "\n🔗 Liens utiles :\n";
echo "================\n";
echo "• Interface admin : " . (isset($_SERVER['HTTP_HOST']) ? "https://{$_SERVER['HTTP_HOST']}/admin" : "https://votre-domaine.com/admin") . "\n";
echo "• Documentation : PRODUCTION_DEPLOYMENT.md\n";
echo "• Support : support@batistack.com\n";

exit($errorCount > 0 ? 1 : 0);
