<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\Sticker;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sticker>
 */
class StickerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'challenge_id' => Challenge::factory(),
            'unlocked_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * A sticker that was just unlocked (today).
     */
    public function justUnlocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'unlocked_at' => now(),
        ]);
    }
}
