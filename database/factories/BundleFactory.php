<?php

namespace Database\Factories;

use App\Models\Bundle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Bundle>
 */
class BundleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(fake()->numberBetween(2, 3), true).' Bundle';
        $name = Str::title($name);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => 'BU-'.Str::upper(Str::random(8)),
            'description' => fake()->paragraph(2),
            'price_usd' => fake()->randomElement([24.99, 34.99, 44.99]),
            'price_myr' => fake()->randomElement([99.90, 139.90, 179.90]),
            'is_active' => true,
        ];
    }

    /**
     * Mark the bundle as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
