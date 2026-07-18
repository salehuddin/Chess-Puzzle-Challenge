<?php

namespace Database\Factories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'puzzle_rating' => fake()->numberBetween(1, 5),
            'platform_rating' => fake()->numberBetween(1, 5),
            'title' => fake()->optional(0.4)->sentence(4),
            'body' => fake()->optional(0.7)->paragraph(3),
            'is_public' => fake()->boolean(30),
            'is_featured' => fake()->boolean(10),
            'status' => 'submitted',
            'submitted_at' => now(),
        ];
    }

    /**
     * Review still awaiting submission by the player.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'puzzle_rating' => null,
            'platform_rating' => null,
            'title' => null,
            'body' => null,
            'is_public' => false,
            'is_featured' => false,
            'status' => 'pending',
            'submitted_at' => null,
        ]);
    }

    /**
     * Review approved for display on the public landing page.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
            'is_featured' => false,
            'status' => 'submitted',
            'submitted_at' => $attributes['submitted_at'] ?? now(),
        ]);
    }

    /**
     * Admin-curated featured review.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
            'is_featured' => true,
            'status' => 'submitted',
            'submitted_at' => $attributes['submitted_at'] ?? now(),
        ]);
    }
}
