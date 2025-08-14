<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
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

            // Gestion des factures (AJOUTÉ)
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',

            // Gestion des sauvegardes (AJOUTÉ)
            'backups.view',
            'backups.create',
            'backups.edit',
            'backups.delete',

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
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete', // AJOUTÉ
            'backups.view', 'backups.create', 'backups.edit', 'backups.delete', // AJOUTÉ
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
            'invoices.view', 'invoices.create', 'invoices.edit', // AJOUTÉ
            'backups.view', // AJOUTÉ
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
            'invoices.view', // AJOUTÉ
            'dashboard.view',
        ];
        $user->syncPermissions($userPermissions);

        // Créer un utilisateur Super Admin par défaut
        $superAdminUser = User::firstOrCreate(
            ['email' => 'admin@batistack.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $superAdminUser->assignRole('Super Admin');

        $this->command->info('Rôles et permissions créés avec succès !');
        $this->command->info('Utilisateur Super Admin créé : admin@batistack.com / password');
    }
}
