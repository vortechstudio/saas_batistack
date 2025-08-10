<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class ProductionSeeder extends Seeder
{
    /**
     * Seeder pour l'environnement de production
     * Contient uniquement les données essentielles et sécurisées
     */
    public function run(): void
    {
        $this->command->info('🚀 Initialisation des données de production...');

        // 1. Créer les permissions et rôles
        $this->createPermissionsAndRoles();

        // 2. Créer les modules de base
        $this->call(ModuleSeeder::class);

        // 3. Créer les options de base
        $this->call(OptionSeeder::class);

        // 4. Créer les produits
        $this->call(ProductSeeder::class);

        // 5. Créer le compte Super Admin initial
        $this->createSuperAdminUser();

        // 6. Nettoyer les données de test si elles existent
        $this->cleanTestData();

        $this->command->info('✅ Données de production initialisées avec succès !');
        $this->command->warn('⚠️  IMPORTANT : Changez immédiatement le mot de passe du Super Admin !');
    }

    /**
     * Créer les permissions et rôles de base
     */
    private function createPermissionsAndRoles(): void
    {
        $this->command->info('📋 Création des permissions et rôles...');

        // Créer les permissions
        $permissions = [
            // Gestion des utilisateurs
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            
            // Gestion des rôles
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            
            // Gestion des permissions
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',
            
            // Gestion des clients
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',
            
            // Gestion des produits
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            
            // Gestion des modules
            'modules.view',
            'modules.create',
            'modules.edit',
            'modules.delete',
            
            // Gestion des licences
            'licenses.view',
            'licenses.create',
            'licenses.edit',
            'licenses.delete',
            
            // Gestion des notifications
            'notifications.view',
            'notifications.create',
            'notifications.edit',
            'notifications.delete',
            
            // Journal d'audit
            'activity-log.view',
            
            // Paramètres système
            'settings.view',
            'settings.edit',
            
            // Tableau de bord
            'dashboard.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Créer les rôles
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $user = Role::firstOrCreate(['name' => 'User']);

        // Assigner toutes les permissions au Super Admin
        $superAdmin->syncPermissions(Permission::all());

        // Assigner des permissions spécifiques à l'Admin
        $adminPermissions = [
            'users.view', 'users.create', 'users.edit',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'modules.view', 'modules.create', 'modules.edit', 'modules.delete',
            'licenses.view', 'licenses.create', 'licenses.edit', 'licenses.delete',
            'notifications.view', 'notifications.create', 'notifications.edit', 'notifications.delete',
            'activity-log.view',
            'dashboard.view',
        ];
        $admin->syncPermissions($adminPermissions);

        // Assigner des permissions spécifiques au Manager
        $managerPermissions = [
            'customers.view', 'customers.create', 'customers.edit',
            'products.view',
            'modules.view',
            'licenses.view', 'licenses.create', 'licenses.edit',
            'notifications.view', 'notifications.create', 'notifications.edit',
            'dashboard.view',
        ];
        $manager->syncPermissions($managerPermissions);

        // Assigner des permissions de base à l'utilisateur
        $userPermissions = [
            'customers.view',
            'products.view',
            'modules.view',
            'licenses.view',
            'dashboard.view',
        ];
        $user->syncPermissions($userPermissions);
    }

    /**
     * Créer le compte Super Admin initial pour la production
     */
    private function createSuperAdminUser(): void
    {
        $this->command->info('👤 Création du compte Super Admin...');

        // Générer un mot de passe temporaire sécurisé
        $temporaryPassword = Str::random(16);

        $superAdminUser = User::firstOrCreate(
            ['email' => 'admin@batistack.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($temporaryPassword),
                'email_verified_at' => now(),
            ]
        );

        // Assigner le rôle Super Admin
        $superAdminUser->assignRole('Super Admin');

        // Afficher les informations de connexion (une seule fois)
        $this->command->info('');
        $this->command->info('🔐 INFORMATIONS DE CONNEXION SUPER ADMIN :');
        $this->command->info('Email: admin@batistack.com');
        $this->command->info('Mot de passe temporaire: ' . $temporaryPassword);
        $this->command->info('');
        $this->command->warn('⚠️  SÉCURITÉ : Notez ces informations et changez le mot de passe immédiatement !');
        $this->command->warn('⚠️  Ce mot de passe ne sera plus affiché après cette exécution.');
        $this->command->info('');
    }

    /**
     * Nettoyer les données de test qui ne doivent pas être en production
     */
    private function cleanTestData(): void
    {
        $this->command->info('🧹 Nettoyage des données de test...');

        // Supprimer les utilisateurs de test
        User::where('email', 'test@example.com')->delete();
        User::where('email', 'LIKE', '%@test.%')->delete();
        User::where('email', 'LIKE', '%@example.%')->delete();

        // Supprimer les notifications de test si elles existent
        if (class_exists(\App\Models\Notification::class)) {
            \App\Models\Notification::where('type', 'LIKE', '%Test%')->delete();
        }
    }

    /**
     * Vérifier que l'environnement est approprié
     */
    public function shouldRun(): bool
    {
        if (app()->environment(['local', 'testing'])) {
            $this->command->warn('⚠️  Ce seeder est conçu pour la production.');
            $this->command->warn('⚠️  Utilisez DatabaseSeeder pour les environnements de développement.');
            
            if (!$this->command->confirm('Voulez-vous continuer quand même ?')) {
                return false;
            }
        }

        return true;
    }
}