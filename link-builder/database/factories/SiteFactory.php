<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Site>
 */
class SiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'name' => $this->faker->words(2, true) . "'s Site",
            'slug' => $this->faker->unique()->slug,
            'bio' => $this->faker->optional()->paragraph,
            'avatar_url' => $this->faker->optional()->imageUrl(200, 200, 'people'),
        ];
    }
}
