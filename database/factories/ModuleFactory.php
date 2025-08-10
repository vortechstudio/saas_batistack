<?php

namespace Database\Factories;

use App\Enums\ModuleCategory;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Module>
 */
class ModuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $modules = [
            'tiers' => ['Tiers', 'Gestion des clients, fournisseurs et prospects'],
            'chantiers' => ['Chantiers', 'Gestion des projets et chantiers'],
            'commerces' => ['Commerces', 'Gestion commerciale et devis'],
            'rh' => ['Ressources Humaines', 'Gestion du personnel et paie'],
            'ged' => ['GED', 'Gestion électronique des documents'],
            'gpao' => ['GPAO', 'Gestion de production assistée par ordinateur'],
            'facturation' => ['Facturation & Paiements', 'Gestion de la facturation et des paiements'],
            'banques' => ['Banques & Caisses', 'Gestion bancaire et de trésorerie'],
            'comptabilite' => ['Comptabilité', 'Comptabilité en partie double'],
            'plannings' => ['Plannings', 'Gestion des plannings et ressources'],
        ];

        $moduleData = fake()->randomElement($modules);

        return [
            'key' => fake()->unique()->slug(2),
            'name' => $moduleData[0],
            'description' => $moduleData[1],
            'category' => fake()->randomElement(ModuleCategory::cases())->value,
            'base_price' => fake()->randomFloat(2, 10, 200),
            'is_active' => fake()->boolean(90), // 90% de chance d'être actif
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    /**
     * Module de base (core)
     */
    public function core(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => ModuleCategory::CORE->value,
            'base_price' => 0,
        ]);
    }

    /**
     * Module avancé
     */
    public function advanced(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => ModuleCategory::ADVANCED->value,
            'base_price' => fake()->randomFloat(2, 50, 150),
        ]);
    }

    /**
     * Module premium
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => ModuleCategory::PREMIUM->value,
            'base_price' => fake()->randomFloat(2, 100, 300),
        ]);
    }

    /**
     * Module inactif
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
