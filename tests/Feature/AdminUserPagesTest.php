<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\Enrollment;
use App\Models\Fulfillment;
use App\Models\Order;
use App\Models\Puzzle;
use App\Models\PuzzleProgress;
use App\Models\Sticker;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminUserPagesTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected User $admin;

    protected User $target;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);

        $this->admin = User::factory()->create(['email' => 'admin@chess.test']);
        $this->admin->assignRole('super_admin');

        $this->target = $this->createUserWithActivity();
    }

    protected function createUserWithActivity(): User
    {
        $user = User::factory()->create([
            'name' => 'Jane Player',
            'email' => 'jane@chess.test',
            'address_line1' => '123 Knight Street',
            'city' => 'Rookville',
            'postcode' => '54321',
            'country' => 'MY',
        ]);

        $challenge = Challenge::factory()->create();
        $puzzles = Puzzle::factory()->count(3)->create();
        $puzzles->each(function (Puzzle $puzzle, int $index) use ($challenge): void {
            $challenge->puzzles()->attach($puzzle, ['sequence' => $index + 1]);
        });
        $puzzles = Puzzle::factory()->count(3)->create();
        $puzzles->each(function (Puzzle $puzzle, int $index) use ($challenge): void {
            $challenge->puzzles()->attach($puzzle, ['sequence' => $index + 1]);
        });

        Order::factory()->for($user)->paid()->create([
            'currency' => 'USD',
            'total_amount' => 29.00,
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'status' => 'active',
            'activated_at' => now(),
        ]);

        foreach ($puzzles as $puzzle) {
            PuzzleProgress::create([
                'user_id' => $user->id,
                'challenge_id' => $challenge->id,
                'puzzle_id' => $puzzle->id,
                'solved_at' => now(),
            ]);
        }

        Sticker::factory()->for($user)->for($challenge)->justUnlocked()->create();

        Fulfillment::create([
            'enrollment_id' => $enrollment->id,
            'status' => 'shipped',
            'courier' => 'DHL',
            'tracking_number' => 'TRACK123',
            'shipped_at' => now(),
        ]);

        return $user;
    }

    public function test_users_list_page_renders_with_stat_columns(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('Paid Orders')
            ->assertSee('Spent (USD)')
            ->assertSee('Completed')
            ->assertSee('Jane Player')
            ->assertSee('$29.00')
            ->assertSee('UsersOverview');
    }

    public function test_user_view_page_renders_with_infolist(): void
    {
        $this->actingAs($this->admin)
            ->get("/admin/users/{$this->target->id}")
            ->assertOk()
            ->assertSee('Account')
            ->assertSee('Address')
            ->assertSee('Jane Player')
            ->assertSee('jane@chess.test')
            ->assertSee('123 Knight Street')
            ->assertSee('Rookville');
    }

    public function test_user_view_page_includes_stat_and_timeline_widgets(): void
    {
        $this->actingAs($this->admin)
            ->get("/admin/users/{$this->target->id}")
            ->assertOk()
            ->assertSee('UserOverview')
            ->assertSee('UserActivityTimeline');
    }

    public function test_user_orders_page_renders(): void
    {
        $this->actingAs($this->admin)
            ->get("/admin/users/{$this->target->id}/orders")
            ->assertOk()
            ->assertSee('Order #');
    }

    public function test_user_enrollments_page_renders_with_progress(): void
    {
        $this->actingAs($this->admin)
            ->get("/admin/users/{$this->target->id}/enrollments")
            ->assertOk()
            ->assertSee('Challenge')
            ->assertSee('Progress');
    }

    public function test_user_puzzle_progress_page_renders(): void
    {
        $this->actingAs($this->admin)
            ->get("/admin/users/{$this->target->id}/progress")
            ->assertOk()
            ->assertSee('Puzzle #');
    }

    public function test_user_stickers_page_renders(): void
    {
        $this->actingAs($this->admin)
            ->get("/admin/users/{$this->target->id}/medals")
            ->assertOk()
            ->assertSee('Unlocked at');
    }

    public function test_user_activity_log_page_renders(): void
    {
        $this->actingAs($this->admin)
            ->get("/admin/users/{$this->target->id}/activity")
            ->assertOk();
    }

    public function test_editing_a_user_logs_an_activity_entry(): void
    {
        $this->target->update(['name' => 'Jane Updated']);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => User::class,
            'subject_id' => $this->target->id,
            'event' => 'updated',
            'description' => 'Updated user account',
        ]);
    }

    public function test_activity_log_page_shows_logged_changes(): void
    {
        $this->target->update(['name' => 'Jane Renamed']);

        $this->actingAs($this->admin)
            ->get("/admin/users/{$this->target->id}/activity")
            ->assertOk()
            ->assertSee('Updated user account');
    }

    public function test_non_admin_cannot_access_user_pages(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_user_model_stat_methods_return_correct_values(): void
    {
        $this->assertSame(1, $this->target->paidOrdersCount());
        $this->assertSame(29.0, $this->target->paidRevenue('USD'));
        $this->assertSame(0.0, $this->target->paidRevenue('MYR'));
        $this->assertSame(0, $this->target->completedChallengesCount());
        $this->assertSame(3, $this->target->solvedPuzzlesCount());
        $this->assertSame(1, $this->target->stickersCount());
    }
}
