<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\Order;
use App\Models\User;
use App\Services\CommerceHierarchyService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CommerceHierarchyTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_paid_order_generates_enrollment_and_completed_enrollment_generates_fulfillment(): void
    {
        $user = User::factory()->create([
            'address_line1' => '123 Main Street',
            'city' => 'Springfield',
            'postcode' => '12345',
            'country' => 'US',
        ]);

        $challenge = Challenge::factory()->create([
            'price_usd' => 29.00,
        ]);

        $order = Order::query()->create([
            'user_id' => $user->id,
            'status' => 'paid',
            'currency' => 'USD',
            'subtotal_amount' => 29.00,
            'discount_amount' => 0,
            'total_amount' => 29.00,
            'paid_at' => now(),
        ]);

        $service = app(CommerceHierarchyService::class);

        $service->syncFromSelection($order, 'challenge', $challenge->id);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'item_type' => 'challenge',
            'item_id' => $challenge->id,
        ]);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseMissing('fulfillments', [
            'status' => 'ready_to_ship',
        ]);

        $enrollment = $user->enrollments()->where('challenge_id', $challenge->id)->firstOrFail();
        $enrollment->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $service->syncFulfillmentForEnrollment($enrollment->fresh());

        $this->assertDatabaseHas('fulfillments', [
            'enrollment_id' => $enrollment->id,
            'status' => 'ready_to_ship',
        ]);
    }
}
