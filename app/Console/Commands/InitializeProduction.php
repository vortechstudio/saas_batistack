<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\ProductionSeeder;
use Illuminate\Support\Facades\DB;

class InitializeProduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batistack:init-production
                            {--force : Force l\'exécution sans confirmation}
                            {--skip-migrations : Ignorer les migrations}
                            {--skip-cache : Ignorer la mise en cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialise l\'application BatiStack pour l\'environnement de production';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 BatiStack SaaS - Initialisation Production');
        $this->info('============================================');
        $this->newLine();

        // Vérification de l'environnement
        if (!$this->checkEnvironment()) {
            return 1;
        }

        // Confirmation
        if (!$this->option('force') && !$this->confirm('Êtes-vous sûr de vouloir initialiser la production ?')) {
            $this->warn('Initialisation annulée.');
            return 0;
        }

        $this->newLine();
        $this->info('🔧 Démarrage de l\'initialisation...');
        $this->newLine();

        try {
            // 1. Migrations
            if (!$this->option('skip-migrations')) {
                $this->runMigrations();
            }

            // 2. Seeding de production
            $this->runProductionSeeding();

            // 3. Optimisations
            if (!$this->option('skip-cache')) {
                $this->optimizeForProduction();
            }

            // 4. Vérifications finales
            $this->runFinalChecks();

            $this->newLine();
            $this->info('✅ Initialisation terminée avec succès !');
            $this->displayPostInstallationInstructions();

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de l\'initialisation : ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Vérifier l'environnement
     */
    private function checkEnvironment(): bool
    {
        $this->info('🔍 Vérification de l\'environnement...');

        // Vérifier le fichier .env
        if (!file_exists(base_path('.env'))) {
            $this->error('❌ Fichier .env non trouvé');
            $this->warn('   Copiez .env.example vers .env et configurez vos variables');
            return false;
        }

        // Vérifier la base de données
        try {
            DB::connection()->getPdo();
            $this->info('✅ Connexion à la base de données OK');
        } catch (\Exception $e) {
            $this->error('❌ Impossible de se connecter à la base de données');
            $this->error('   ' . $e->getMessage());
            return false;
        }

        // Avertissement pour l'environnement local
        if (app()->environment(['local', 'testing'])) {
            $this->warn('⚠️  Vous êtes en environnement ' . app()->environment());
            if (!$this->confirm('Continuer quand même ?')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Exécuter les migrations
     */
    private function runMigrations(): void
    {
        $this->info('🗄️  Exécution des migrations...');

        $this->call('migrate', [
            '--force' => true,
        ]);

        $this->info('✅ Migrations terminées');
    }

    /**
     * Exécuter le seeding de production
     */
    private function runProductionSeeding(): void
    {
        $this->info('🌱 Initialisation des données de production...');

        $this->call('db:seed', [
            '--class' => ProductionSeeder::class,
        ]);

        $this->info('✅ Données de production initialisées');
    }

    /**
     * Optimiser pour la production
     */
    private function optimizeForProduction(): void
    {
        $this->info('⚡ Optimisation pour la production...');

        // Cache de configuration
        $this->call('config:cache');
        $this->info('  ✅ Cache de configuration créé');

        // Cache des routes
        $this->call('route:cache');
        $this->info('  ✅ Cache des routes créé');

        // Cache des vues
        $this->call('view:cache');
        $this->info('  ✅ Cache des vues créé');

        // Optimisation de l'autoloader
        $this->info('  🔧 Optimisation de l\'autoloader...');
        exec('composer dump-autoload --optimize --no-dev');
        $this->info('  ✅ Autoloader optimisé');
    }

    /**
     * Vérifications finales
     */
    private function runFinalChecks(): void
    {
        $this->info('🔍 Vérifications finales...');

        // Vérifier que le Super Admin existe
        $superAdmin = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'Super Admin');
        })->first();

        if ($superAdmin) {
            $this->info('  ✅ Compte Super Admin créé : ' . $superAdmin->email);
        } else {
            $this->warn('  ⚠️  Aucun compte Super Admin trouvé');
        }

        // Vérifier les permissions
        $permissionsCount = \Spatie\Permission\Models\Permission::count();
        $rolesCount = \Spatie\Permission\Models\Role::count();

        $this->info("  ✅ {$permissionsCount} permissions créées");
        $this->info("  ✅ {$rolesCount} rôles créés");

        // Vérifier les modules et produits
        $modulesCount = \App\Models\Module::count();
        $productsCount = \App\Models\Product::count();

        $this->info("  ✅ {$modulesCount} modules créés");
        $this->info("  ✅ {$productsCount} produits créés");
    }

    /**
     * Afficher les instructions post-installation
     */
    private function displayPostInstallationInstructions(): void
    {
        $this->newLine();
        $this->info('🔐 INFORMATIONS IMPORTANTES :');
        $this->info('============================');
        $this->warn('• Connectez-vous avec : admin@batistack.com');
        $this->warn('• Le mot de passe temporaire a été affiché lors du seeding');
        $this->error('• CHANGEZ IMMÉDIATEMENT le mot de passe après la première connexion');

        $this->newLine();
        $this->info('📚 Prochaines étapes recommandées :');
        $this->info('===================================');
        $this->line('1. Tester la connexion admin');
        $this->line('2. Configurer les paramètres SMTP');
        $this->line('3. Configurer Stripe (si applicable)');
        $this->line('4. Configurer les sauvegardes automatiques');
        $this->line('5. Configurer la surveillance (logs, monitoring)');
        $this->line('6. Tester toutes les fonctionnalités critiques');

        $this->newLine();
        $this->info('🎉 Votre application BatiStack SaaS est prête !');
    }
}
