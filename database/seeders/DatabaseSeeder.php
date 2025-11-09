<?php

namespace Database\Seeders;

use App\Enum\Customer\CustomerTypeEnum;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'nom' => 'Admin',
            'prenom' => 'User',
            'email' => 'admin@'.config('batistack.domain'),
        ]);

        if (config('app.env') == 'local' || config('app.env') == 'testing') {
            $this->call([
                ProductSeeder::class,
                FeatureSeeder::class,
                CustomerSeeder::class,
            ]);
        } else {
            $user = User::first();
            $user->customer()->create([
                'type_compte' => fake()->randomElement(CustomerTypeEnum::array()->pluck('value')->toArray()),
                'user_id' => $user->id,
                'code_client' => 'CLI'.rand(100000,999999999),
                'entreprise' => fake()->company(),
                'adresse' => fake()->address(),
                'code_postal' => fake()->postcode(),
                'ville' => fake()->city(),
                'pays' => fake()->country(),
                'tel' => fake()->phoneNumber(),
                'portable' => fake()->phoneNumber(),
            ]);
            $this->call([
                ProductSeeder::class,
                FeatureSeeder::class,
            ]);
        }
    }
}
