<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer un utilisateur de test par défaut
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Seeders de base pour la structure
        $this->call([
            ModuleSeeder::class,
            OptionSeeder::class,
            ProductSeeder::class,
        ]);

        // Seeder pour les données de test de l'interface
        if (app()->environment(['local', 'testing'])) {
            $this->call([
                TestDataSeeder::class,
            ]);
        }

        // Utilisateur admin par défaut
        User::firstOrCreate(
            ['email' => 'admin@batistack.com'],
            [
                'name' => 'Admin Batistack',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
