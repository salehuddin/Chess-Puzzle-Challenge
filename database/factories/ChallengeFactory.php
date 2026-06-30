<?php

namespace Database\Factories;

use App\Models\Challenge;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Challenge>
 */
class ChallengeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(fake()->numberBetween(2, 4), true);
        $name = Str::title($name);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => 'CH-' . Str::upper(Str::random(8)),
            'description' => fake()->paragraph(2),
            'medal_artwork' => null,
            'sticker_artwork' => null,
            'rules' => [
                'order' => fake()->randomElement(['sequential', 'random']),
                'time_limit_minutes' => fake()->optional(0.3)->numberBetween(5, 30),
                'allow_undo' => true,
            ],
            'price_usd' => fake()->randomElement([9.99, 14.99, 19.99, 24.99]),
            'price_myr' => fake()->randomElement([39.90, 59.90, 79.90, 99.90]),
            'is_active' => true,
        ];
    }

    /**
     * Mark the challenge as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Challenge with sequential (ordered) puzzle rules.
     */
    public function sequential(): static
    {
        return $this->state(fn (array $attributes) => [
            'rules' => array_merge($attributes['rules'] ?? [], ['order' => 'sequential']),
        ]);
    }
}
