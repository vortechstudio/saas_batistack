<?php

namespace Database\Factories\Helpdesk;

use App\Models\Helpdesk\KbArticle;
use App\Models\Helpdesk\KbCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class KbArticleFactory extends Factory
{
    protected $model = KbArticle::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'slug' => $this->faker->slug(),
            'content' => $this->faker->word(),
            'is_published' => $this->faker->boolean(),
            'views_count' => $this->faker->randomNumber(),
            'helpful_count' => $this->faker->randomNumber(),
            'not_helpful_count' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'kb_category_id' => KbCategory::factory(),
            'user_id' => User::factory(),
        ];
    }
}
