<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminReviewsPageTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_reviews_list_page_renders_for_super_admin(): void
    {
        $admin = $this->createAdmin();
        $this->createReview();

        $this->actingAs($admin)
            ->get('/admin/reviews')
            ->assertOk();
    }

    public function test_reviews_edit_page_renders_with_moderation_fields(): void
    {
        $admin = $this->createAdmin();
        $review = $this->createReview();

        $this->actingAs($admin)
            ->get("/admin/reviews/{$review->id}/edit")
            ->assertOk()
            ->assertSee('Moderation')
            ->assertSee('Show on testimonials section');
    }

    public function test_editor_can_access_reviews_pages(): void
    {
        $editor = User::factory()->create(['email' => 'editor@chess.test']);
        $editor->assignRole('editor');

        $review = $this->createReview();

        $this->actingAs($editor)->get('/admin/reviews')->assertOk();
        $this->actingAs($editor)->get("/admin/reviews/{$review->id}/edit")->assertOk();
    }

    public function test_fulfillment_staff_is_forbidden_from_reviews(): void
    {
        $fulfillment = User::factory()->create(['email' => 'fulfillment@chess.test']);
        $fulfillment->assignRole('fulfillment');

        $review = $this->createReview();

        $this->actingAs($fulfillment)->get('/admin/reviews')->assertForbidden();
        $this->actingAs($fulfillment)->get("/admin/reviews/{$review->id}/edit")->assertForbidden();
    }

    public function test_non_staff_user_is_forbidden_from_reviews(): void
    {
        $user = User::factory()->create();

        $review = $this->createReview();

        $this->actingAs($user)->get('/admin/reviews')->assertForbidden();
        $this->actingAs($user)->get("/admin/reviews/{$review->id}/edit")->assertForbidden();
    }

    protected function createAdmin(): User
    {
        $admin = User::factory()->create(['email' => 'admin@chess.test']);
        $admin->assignRole('super_admin');

        return $admin;
    }

    protected function createReview(): Review
    {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'status' => 'completed',
            'activated_at' => now(),
            'completed_at' => now(),
        ]);

        return Review::factory()->create([
            'enrollment_id' => $enrollment->id,
            'challenge_id' => $challenge->id,
            'user_id' => $user->id,
        ]);
    }
}
