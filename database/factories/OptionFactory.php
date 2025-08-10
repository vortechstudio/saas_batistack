<?php

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Enums\OptionType;
use App\Models\Option;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Option>
 */
class OptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $options = [
            'backup_retention' => ['Backup & Rétention', 'Sauvegarde automatique et rétention des données'],
            'bank_aggregation' => ['Agrégation bancaire', 'Connexion automatique aux comptes bancaires'],
            'ocr_invoices' => ['OCR Factures', 'Reconnaissance optique des factures'],
            'ocr_expenses' => ['OCR Notes de frais', 'Reconnaissance optique des notes de frais'],
            'premium_support' => ['Support Premium', 'Support prioritaire 24/7'],
            'extended_support' => ['Support Étendu', 'Support technique étendu'],
            'extra_storage' => ['Stockage supplémentaire', 'Espace de stockage additionnel'],
            'api_access' => ['Accès API', 'Accès complet à l\'API REST'],
        ];

        $optionData = fake()->randomElement($options);

        return [
            'key' => fake()->unique()->slug(2),
            'name' => $optionData[0],
            'description' => $optionData[1],
            'type' => fake()->randomElement(OptionType::cases())->value,
            'price' => fake()->randomFloat(2, 5, 100),
            'billing_cycle' => fake()->randomElement(BillingCycle::cases())->value,
            'is_active' => fake()->boolean(85), // 85% de chance d'être actif
        ];
    }

    /**
     * Option de type fonctionnalité
     */
    public function feature(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => OptionType::FEATURE->value,
            'price' => fake()->randomFloat(2, 10, 50),
        ]);
    }

    /**
     * Option de type support
     */
    public function support(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => OptionType::SUPPORT->value,
            'price' => fake()->randomFloat(2, 20, 100),
            'billing_cycle' => BillingCycle::MONTHLY->value,
        ]);
    }

    /**
     * Option de type stockage
     */
    public function storage(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => OptionType::STORAGE->value,
            'price' => fake()->randomFloat(2, 5, 30),
            'billing_cycle' => BillingCycle::MONTHLY->value,
        ]);
    }

    /**
     * Option mensuelle
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => BillingCycle::MONTHLY->value,
        ]);
    }

    /**
     * Option annuelle
     */
    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => BillingCycle::YEARLY->value,
            'price' => $attributes['price'] * 10, // Réduction pour l'annuel
        ]);
    }

    /**
     * Option inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
