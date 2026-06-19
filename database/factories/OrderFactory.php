<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => 'pending',
            'currency' => 'USD',
            'subtotal_amount' => 19.99,
            'discount_amount' => 0,
            'total_amount' => 19.99,
            'payment_provider' => null,
            'payment_intent_id' => null,
            'paid_at' => null,
            'metadata' => [],
        ];
    }

    /**
     * Mark the order as paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'payment_provider' => 'sandbox',
            'paid_at' => now(),
        ]);
    }
}
