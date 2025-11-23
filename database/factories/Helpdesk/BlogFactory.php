<?php

namespace Database\Factories\Helpdesk;

use App\Models\Helpdesk\Blog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BlogFactory extends Factory
{
    protected $model = Blog::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'slug' => $this->faker->slug(),
            'excerpt' => $this->faker->word(),
            'content' => $this->faker->word(),
            'featured_image' => $this->faker->word(),
            'is_featured' => $this->faker->boolean(),
            'is_published' => $this->faker->boolean(),
            'published_at' => Carbon::now(),
            'seo_title' => $this->faker->word(),
            'seo_description' => $this->faker->text(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
        ];
    }
}
