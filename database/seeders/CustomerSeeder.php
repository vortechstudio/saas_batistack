<?php

namespace Database\Seeders;

use App\Enum\Customer\CustomerTypeEnum;
use App\Models\Customer\Customer;
use App\Models\User;
use App\Services\Stripe\CustomerService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class CustomerSeeder extends Seeder
{
    /**
     * Remplit la base de données avec des utilisateurs et leurs clients associés, puis synchronise chaque client avec Stripe.
     *
     * La méthode purge les données Stripe existantes, crée dix utilisateurs, génère pour chacun un enregistrement Customer contenant
     * des valeurs factices (type de compte, code client, entreprise, adresse, code postal, ville, pays, téléphone) et synchronise
     * chaque client avec Stripe via le service CustomerService.
     */
    public function run(): void
    {
        Artisan::call('purge:stripe-data --force');
        User::factory(10)->create();

        foreach (User::all() as $user) {
            $customer = $user->customer()->create([
                'type_compte' => fake()->randomElement(CustomerTypeEnum::array()->pluck('value')->toArray()),
                'user_id' => $user->id,
                'code_client' => 'CLI'.rand(100000,999999999),
                'entreprise' => fake()->company(),
                'adresse' => fake()->address(),
                'code_postal' => fake()->postcode(),
                'ville' => fake()->city(),
                'pays' => fake()->countryCode(),
                'tel' => fake()->phoneNumber(),
                'portable' => fake()->phoneNumber(),
            ]);
            $customerService = app(CustomerService::class);
            $customerService->create($customer);
        }
    }
}