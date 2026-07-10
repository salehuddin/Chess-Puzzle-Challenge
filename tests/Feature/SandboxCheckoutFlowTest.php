<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\Order;
use App\Models\Puzzle;
use App\Models\User;
use App\Services\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SandboxCheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Settings::set('payments', ['sandbox_mode' => true]);
    }

    public function test_normal_user_can_enroll_and_pay_with_sandbox(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $challenge = Challenge::factory()->create([
            'price_usd' => 19.99,
            'is_active' => true,
        ]);

        Puzzle::factory()->count(3)->create()->each(function (Puzzle $puzzle) use ($challenge): void {
            $challenge->puzzles()->attach($puzzle, ['sequence' => $puzzle->id]);
        });

        $this->actingAs($user)
            ->get("/challenges/{$challenge->slug}/enroll")
            ->assertRedirectToRoute('checkout.show', Order::first());

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertSame('pending', $order->status);
        $this->assertSame($user->id, $order->user_id);

        $this->actingAs($user)
            ->get("/checkout/{$order->id}")
            ->assertOk()
            ->assertSee('Sandbox Payment Mode');

        $this->actingAs($user)
            ->post("/checkout/{$order->id}/pay")
            ->assertRedirectToRoute('play', $order->items->first()->enrollments->first());

        $order->refresh();
        $this->assertSame('paid', $order->status);
        $this->assertSame('sandbox', $order->payment_provider);
        $this->assertNotNull($order->paid_at);

        $enrollment = $order->items->first()->enrollments->first();
        $this->assertNotNull($enrollment);
        $this->assertSame('active', $enrollment->status);
        $this->assertNotNull($enrollment->activated_at);
    }

    public function test_user_cannot_pay_another_users_order(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get("/checkout/{$order->id}")
            ->assertForbidden();

        $this->actingAs($user)
            ->post("/checkout/{$order->id}/pay")
            ->assertForbidden();
    }

    public function test_sandbox_payment_is_disabled_when_setting_is_off(): void
    {
        Settings::set('payments', ['sandbox_mode' => false]);

        $user = User::factory()->create();
        $challenge = Challenge::factory()->create([
            'price_usd' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->get("/challenges/{$challenge->slug}/enroll");

        $response->assertRedirectToRoute('dashboard');
        $this->assertSame('pending', Order::first()->status);

        $this->actingAs($user)
            ->get('/checkout/'.Order::first()->id)
            ->assertRedirectToRoute('dashboard');
    }
}
