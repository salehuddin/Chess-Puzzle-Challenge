<?php

namespace Tests\Feature;

use App\Livewire\PuzzlePlayer;
use App\Models\Challenge;
use App\Models\Enrollment;
use App\Models\Puzzle;
use App\Models\PuzzleProgress;
use App\Models\User;
use App\Support\NavigationMode;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PuzzlePlayerNavigationModesTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function makePlayerSetup(NavigationMode $mode, int $puzzleCount = 3, array $extraChallengeAttrs = []): array
    {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create(array_merge([
            'name' => 'Test Challenge '.$mode->value,
            'puzzle_navigation_mode' => $mode->value,
        ], $extraChallengeAttrs));

        $puzzles = Puzzle::factory()->count($puzzleCount)->create();
        $challenge->puzzles()->attach(
            $puzzles->pluck('id')->mapWithKeys(fn ($id, $i) => [$id => ['sequence' => $i + 1]])->all()
        );

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'status' => 'active',
            'activated_at' => now(),
        ]);

        return [$user, $challenge, $puzzles, $enrollment];
    }

    public function test_strict_mode_is_the_default_for_new_challenges(): void
    {
        $challenge = Challenge::factory()->create()->fresh();

        $this->assertSame(NavigationMode::Strict, $challenge->puzzle_navigation_mode);
        $this->assertNull($challenge->effectiveSkipTokenCount());
        $this->assertNull($challenge->effectiveAutosolveThreshold());
    }

    public function test_strict_skip_uses_default_skip_token_count_when_unset(): void
    {
        $challenge = Challenge::factory()->create([
            'puzzle_navigation_mode' => NavigationMode::StrictSkip->value,
        ]);

        $this->assertSame(3, $challenge->effectiveSkipTokenCount());
    }

    public function test_strict_autosolve_uses_default_threshold_when_unset(): void
    {
        $challenge = Challenge::factory()->create([
            'puzzle_navigation_mode' => NavigationMode::StrictAutosolve->value,
        ]);

        $this->assertSame(5, $challenge->effectiveAutosolveThreshold());
    }

    public function test_enrollment_skip_helpers_return_null_in_non_skip_modes(): void
    {
        [$user, $challenge, $puzzles, $enrollment] = $this->makePlayerSetup(NavigationMode::Strict);

        $this->assertNull($enrollment->skipsRemaining());
        $this->assertSame(0, $enrollment->skipsUsed());
    }

    public function test_enrollment_skip_helpers_decrement_and_track_usage(): void
    {
        [$user, $challenge, $puzzles, $enrollment] = $this->makePlayerSetup(NavigationMode::StrictSkip);

        $this->assertSame(3, $enrollment->skipsRemaining());

        PuzzleProgress::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'puzzle_id' => $puzzles[0]->id,
            'skipped_at' => now(),
        ]);

        $this->assertSame(2, $enrollment->fresh()->skipsRemaining());
        $this->assertSame(1, $enrollment->fresh()->skipsUsed());
    }

    public function test_puzzle_progress_state_helpers(): void
    {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();
        $puzzles = Puzzle::factory()->count(3)->create();
        $challenge->puzzles()->attach($puzzles->pluck('id')->mapWithKeys(fn ($id, $i) => [$id => ['sequence' => $i + 1]])->all());

        $solved = PuzzleProgress::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'puzzle_id' => $puzzles[0]->id,
            'solved_at' => now(),
        ]);
        $this->assertTrue($solved->isSolved());
        $this->assertTrue($solved->isFullySolved());
        $this->assertFalse($solved->isSolvedWithHelp());
        $this->assertFalse($solved->isSkipped());

        $withHelp = PuzzleProgress::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'puzzle_id' => $puzzles[1]->id,
            'solved_at' => now(),
            'solved_with_help' => true,
        ]);
        $this->assertTrue($withHelp->isSolved());
        $this->assertFalse($withHelp->isFullySolved());
        $this->assertTrue($withHelp->isSolvedWithHelp());

        $skipped = PuzzleProgress::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'puzzle_id' => $puzzles[2]->id,
            'skipped_at' => now(),
        ]);
        $this->assertFalse($skipped->isSolved());
        $this->assertTrue($skipped->isSkipped());
    }

    public function test_strict_mode_blocks_skip_attempt_and_solve_with_help(): void
    {
        [$user, $challenge, $puzzles, $enrollment] = $this->makePlayerSetup(NavigationMode::Strict);

        $component = Livewire::actingAs($user)->test(PuzzlePlayer::class, ['enrollment' => $enrollment]);

        $token = $component->get('completionToken');

        $component->call('skipPuzzle', $puzzles[0]->id, $token);
        $component->call('solveWithHelp', $puzzles[0]->id, $token);

        $this->assertSame(0, PuzzleProgress::count());
    }

    public function test_free_mode_allows_selecting_arbitrary_unsolved_puzzle(): void
    {
        [$user, $challenge, $puzzles, $enrollment] = $this->makePlayerSetup(NavigationMode::Free);

        $component = Livewire::actingAs($user)->test(PuzzlePlayer::class, ['enrollment' => $enrollment]);

        // Default: current puzzle is the first one.
        $component->assertSet('currentPuzzleId', $puzzles[0]->id);

        // Jump to the third puzzle.
        $component->call('selectPuzzle', $puzzles[2]->id)
            ->assertSet('currentPuzzleId', $puzzles[2]->id)
            ->assertSet('currentFen', $puzzles[2]->fen);
    }

    public function test_free_mode_blocks_selecting_already_solved_puzzle(): void
    {
        [$user, $challenge, $puzzles, $enrollment] = $this->makePlayerSetup(NavigationMode::Free);

        // Mark puzzle 1 as already solved.
        PuzzleProgress::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'puzzle_id' => $puzzles[0]->id,
            'solved_at' => now(),
        ]);

        $component = Livewire::actingAs($user)->test(PuzzlePlayer::class, ['enrollment' => $enrollment]);

        // After mount, currentPuzzleId should be puzzle 2 (first unsolved).
        $component->assertSet('currentPuzzleId', $puzzles[1]->id);

        // Attempting to jump to puzzle 1 (already solved) is a no-op.
        $component->call('selectPuzzle', $puzzles[0]->id)
            ->assertNotSet('currentPuzzleId', $puzzles[0]->id);
    }

    public function test_strict_mode_blocks_freemode_selectpuzzle(): void
    {
        [$user, $challenge, $puzzles, $enrollment] = $this->makePlayerSetup(NavigationMode::Strict);

        $component = Livewire::actingAs($user)->test(PuzzlePlayer::class, ['enrollment' => $enrollment]);

        $component->call('selectPuzzle', $puzzles[2]->id)
            ->assertSet('currentPuzzleId', $puzzles[0]->id);
    }

    public function test_strict_skip_consumes_skip_token_on_skip(): void
    {
        [$user, $challenge, $puzzles, $enrollment] = $this->makePlayerSetup(NavigationMode::StrictSkip);

        $component = Livewire::actingAs($user)->test(PuzzlePlayer::class, ['enrollment' => $enrollment]);

        $token = $component->get('completionToken');
        $this->assertSame(3, $component->get('skipTokensTotal'));
        $this->assertSame(3, $component->get('skipTokensRemaining'));

        // Skip puzzle 1.
        $component->call('skipPuzzle', $puzzles[0]->id, $token);

        // First puzzle should now be skipped in DB, current puzzle should be puzzle 2.
        $this->assertNotNull(PuzzleProgress::where('puzzle_id', $puzzles[0]->id)->whereNotNull('skipped_at')->first());
        $component->assertSet('currentPuzzleId', $puzzles[1]->id);
        $component->assertSet('skipTokensRemaining', 2);
    }

    public function test_strict_skip_blocks_completion_when_only_skipped_puzzles_remain(): void
    {
        [$user, $challenge, $puzzles, $enrollment] = $this->makePlayerSetup(NavigationMode::StrictSkip, puzzleCount: 2);

        // Solve puzzle 1 normally.
        PuzzleProgress::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'puzzle_id' => $puzzles[0]->id,
            'solved_at' => now(),
        ]);

        // Skip puzzle 2.
        PuzzleProgress::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'puzzle_id' => $puzzles[1]->id,
            'skipped_at' => now(),
        ]);

        $component = Livewire::actingAs($user)->test(PuzzlePlayer::class, ['enrollment' => $enrollment]);

        // Should NOT be marked complete — puzzle 2 was skipped, not solved.
        $component->assertSet('isComplete', false)
            ->assertSet('needsCleanup', true)
            ->assertSet('currentPuzzleId', $puzzles[1]->id);

        // Enrollment status should still be 'active'.
        $this->assertSame('active', $enrollment->fresh()->status);
    }

    public function test_strict_skip_completes_after_revisiting_skipped_puzzle(): void
    {
        [$user, $challenge, $puzzles, $enrollment] = $this->makePlayerSetup(NavigationMode::StrictSkip, puzzleCount: 1);

        // Start with the single puzzle skipped.
        PuzzleProgress::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'puzzle_id' => $puzzles[0]->id,
            'skipped_at' => now(),
        ]);

        $component = Livewire::actingAs($user)->test(PuzzlePlayer::class, ['enrollment' => $enrollment]);

        $component->assertSet('needsCleanup', true)
            ->assertSet('isComplete', false)
            ->assertSet('currentPuzzleId', $puzzles[0]->id);

        // Now mark the puzzle solved properly.
        $token = $component->get('completionToken');
        $component->call('completeChallenge', $token);

        // Should now be complete.
        $this->assertSame('completed', $enrollment->fresh()->status);
    }

    public function test_strict_autosolve_marks_solved_with_help(): void
    {
        [$user, $challenge, $puzzles, $enrollment] = $this->makePlayerSetup(NavigationMode::StrictAutosolve);

        $component = Livewire::actingAs($user)->test(PuzzlePlayer::class, ['enrollment' => $enrollment]);

        $token = $component->get('completionToken');

        $component->call('solveWithHelp', $puzzles[0]->id, $token);

        $progress = PuzzleProgress::where('puzzle_id', $puzzles[0]->id)->first();
        $this->assertNotNull($progress);
        $this->assertNotNull($progress->solved_at);
        $this->assertTrue((bool) $progress->solved_with_help);

        // Should advance to the next puzzle.
        $component->assertSet('currentPuzzleId', $puzzles[1]->id);
    }

    public function test_record_attempt_increments_attempts_counter(): void
    {
        [$user, $challenge, $puzzles, $enrollment] = $this->makePlayerSetup(NavigationMode::StrictAutosolve);

        $component = Livewire::actingAs($user)->test(PuzzlePlayer::class, ['enrollment' => $enrollment]);
        $token = $component->get('completionToken');

        $component->call('recordAttempt', $puzzles[0]->id, $token)
            ->call('recordAttempt', $puzzles[0]->id, $token);

        $progress = PuzzleProgress::where('puzzle_id', $puzzles[0]->id)->first();
        $this->assertSame(2, $progress->attempts);
        $this->assertNull($progress->solved_at);
    }

    public function test_record_hint_increments_hints_used_counter(): void
    {
        [$user, $challenge, $puzzles, $enrollment] = $this->makePlayerSetup(NavigationMode::Strict);

        $component = Livewire::actingAs($user)->test(PuzzlePlayer::class, ['enrollment' => $enrollment]);
        $token = $component->get('completionToken');

        $component->call('recordHint', $puzzles[0]->id, $token)
            ->call('recordHint', $puzzles[0]->id, $token)
            ->call('recordHint', $puzzles[0]->id, $token);

        $progress = PuzzleProgress::where('puzzle_id', $puzzles[0]->id)->first();
        $this->assertSame(3, $progress->hints_used);
    }

    public function test_token_verification_rejects_puzzles_outside_the_current_puzzle(): void
    {
        [$user, $challenge, $puzzles, $enrollment] = $this->makePlayerSetup(NavigationMode::Strict);

        $component = Livewire::actingAs($user)->test(PuzzlePlayer::class, ['enrollment' => $enrollment]);
        $token = $component->get('completionToken');

        // Calling solvePuzzle with a different puzzleId should not create a progress row.
        $component->call('solvePuzzle', $puzzles[1]->id, $token);

        $this->assertSame(0, PuzzleProgress::count());
    }

    public function test_complete_challenge_with_help_flows_to_completion(): void
    {
        [$user, $challenge, $puzzles, $enrollment] = $this->makePlayerSetup(NavigationMode::StrictAutosolve, puzzleCount: 1);

        $component = Livewire::actingAs($user)->test(PuzzlePlayer::class, ['enrollment' => $enrollment]);
        $token = $component->get('completionToken');

        // Final puzzle → use completeChallengeWithHelp.
        $component->call('completeChallengeWithHelp', $token)
            ->assertSet('isComplete', true);

        $progress = PuzzleProgress::where('puzzle_id', $puzzles[0]->id)->first();
        $this->assertTrue($progress->solved_with_help);

        $this->assertSame('completed', $enrollment->fresh()->status);
    }

    public function test_skip_puzzle_blocks_when_out_of_skip_tokens(): void
    {
        [$user, $challenge, $puzzles, $enrollment] = $this->makePlayerSetup(
            NavigationMode::StrictSkip,
            2,
            ['skip_token_count' => 1],
        );

        // Use the only skip token.
        PuzzleProgress::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'puzzle_id' => $puzzles[0]->id,
            'skipped_at' => now(),
        ]);

        $component = Livewire::actingAs($user)->test(PuzzlePlayer::class, ['enrollment' => $enrollment]);

        // Should land on puzzle 2 (puzzle 1 was skipped → passed).
        $component->assertSet('currentPuzzleId', $puzzles[1]->id)
            ->assertSet('skipTokensRemaining', 0);

        $token = $component->get('completionToken');

        // Attempt to skip puzzle 2 — should be denied.
        $component->call('skipPuzzle', $puzzles[1]->id, $token)
            ->assertDispatched('skip-denied');

        // No new skipped row for puzzle 2.
        $this->assertNull(
            PuzzleProgress::where('puzzle_id', $puzzles[1]->id)->whereNotNull('skipped_at')->first()
        );
    }

    public function test_revisiting_a_skipped_puzzle_then_solving_marks_solved_clears_needs_cleanup(): void
    {
        [$user, $challenge, $puzzles, $enrollment] = $this->makePlayerSetup(NavigationMode::StrictSkip, 2);

        // Skip puzzle 1; solve puzzle 2 (via DB seed).
        PuzzleProgress::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'puzzle_id' => $puzzles[0]->id,
            'skipped_at' => now(),
        ]);
        PuzzleProgress::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'puzzle_id' => $puzzles[1]->id,
            'solved_at' => now(),
        ]);

        $component = Livewire::actingAs($user)->test(PuzzlePlayer::class, ['enrollment' => $enrollment]);

        // Should detect skipped-unsolved and load it for revisit.
        $component->assertSet('needsCleanup', true)
            ->assertSet('isComplete', false)
            ->assertSet('currentPuzzleId', $puzzles[0]->id);

        $token = $component->get('completionToken');

        // Solve the skipped-unsolved puzzle via the "final puzzle" flow,
        // because there's now exactly one unsolved puzzle remaining.
        $component->call('completeChallenge', $token)
            ->assertSet('isComplete', true);

        $progress = PuzzleProgress::where('puzzle_id', $puzzles[0]->id)->first();
        $this->assertNotNull($progress->solved_at);
        $this->assertSame('completed', $enrollment->fresh()->status);
    }
}
