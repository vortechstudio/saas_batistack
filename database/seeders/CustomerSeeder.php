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
     * Run the database seeds.
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
                'pays' => fake()->country(),
                'tel' => fake()->phoneNumber(),
                'portable' => fake()->phoneNumber(),
            ]);
            $customerService = app(CustomerService::class);
            $customerService->create($customer);
        }
    }
}
