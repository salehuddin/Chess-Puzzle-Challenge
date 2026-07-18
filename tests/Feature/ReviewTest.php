<?php

namespace Tests\Feature;

use App\Livewire\PuzzlePlayer;
use App\Models\Challenge;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_pending_review_row_is_created_when_challenge_completes(): void
    {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'status' => 'active',
            'activated_at' => now(),
        ]);

        $review = Review::factory()->pending()->create([
            'enrollment_id' => $enrollment->id,
            'challenge_id' => $challenge->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'enrollment_id' => $enrollment->id,
            'status' => 'pending',
        ]);

        $this->assertFalse($review->isSubmitted());
    }

    public function test_submit_review_flips_status_to_submitted_and_sets_submitted_at(): void
    {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'status' => 'active',
            'activated_at' => now(),
        ]);

        $review = Review::factory()->pending()->create([
            'enrollment_id' => $enrollment->id,
            'challenge_id' => $challenge->id,
            'user_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(PuzzlePlayer::class, ['enrollment' => $enrollment])
            ->set('puzzleRating', 4)
            ->set('platformRating', 5)
            ->set('reviewTitle', 'Loved it')
            ->set('reviewBody', 'Tough but rewarding.')
            ->call('submitReview')
            ->assertDispatched('review-submitted');

        $review->refresh();

        $this->assertSame('submitted', $review->status);
        $this->assertNotNull($review->submitted_at);
        $this->assertSame(4, $review->puzzle_rating);
        $this->assertSame(5, $review->platform_rating);
        $this->assertSame('Loved it', $review->title);
    }

    public function test_submit_review_rejects_invalid_ratings(): void
    {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'status' => 'active',
            'activated_at' => now(),
        ]);

        Review::factory()->pending()->create([
            'enrollment_id' => $enrollment->id,
            'challenge_id' => $challenge->id,
            'user_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(PuzzlePlayer::class, ['enrollment' => $enrollment])
            ->set('puzzleRating', 0)
            ->set('platformRating', 6)
            ->call('submitReview')
            ->assertHasErrors(['puzzleRating', 'platformRating']);

        $this->assertDatabaseHas('reviews', [
            'enrollment_id' => $enrollment->id,
            'status' => 'pending',
        ]);
    }

    public function test_submit_review_aborts_when_review_already_submitted(): void
    {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'status' => 'active',
            'activated_at' => now(),
        ]);

        $original = Review::factory()->create([
            'enrollment_id' => $enrollment->id,
            'challenge_id' => $challenge->id,
            'user_id' => $user->id,
            'puzzle_rating' => 2,
            'platform_rating' => 3,
            'status' => 'submitted',
            'submitted_at' => now()->subHour(),
        ]);

        try {
            Livewire::actingAs($user)
                ->test(PuzzlePlayer::class, ['enrollment' => $enrollment])
                ->set('puzzleRating', 3)
                ->set('platformRating', 4)
                ->call('submitReview');
        } catch (HttpException $e) {
            // Expected — submitReview aborts with 403 on already-submitted reviews.
            $this->assertSame(403, $e->getStatusCode());
        }

        $original->refresh();
        $this->assertSame('submitted', $original->status);
        $this->assertSame(2, $original->puzzle_rating);
    }

    public function test_review_scopes_filter_correctly(): void
    {
        $user = User::factory()->create();

        $c1 = Challenge::factory()->create();
        $c2 = Challenge::factory()->create();
        $c3 = Challenge::factory()->create();

        $enrollments = collect([$c1, $c2, $c3])->map(fn ($challenge) => Enrollment::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'status' => 'active',
            'activated_at' => now(),
        ]));

        Review::factory()->pending()->create([
            'enrollment_id' => $enrollments[0]->id,
            'challenge_id' => $c1->id,
            'user_id' => $user->id,
        ]);

        Review::factory()->public()->create([
            'enrollment_id' => $enrollments[1]->id,
            'challenge_id' => $c2->id,
            'user_id' => $user->id,
        ]);

        Review::factory()->featured()->create([
            'enrollment_id' => $enrollments[2]->id,
            'challenge_id' => $c3->id,
            'user_id' => $user->id,
        ]);

        $this->assertCount(2, Review::submitted()->get());
        $this->assertCount(2, Review::public()->get());
        $this->assertCount(1, Review::featured()->get());
        $this->assertTrue(Review::featured()->first()->isSubmitted());
    }
}
