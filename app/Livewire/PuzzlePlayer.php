<?php

namespace App\Livewire;

use App\Models\Enrollment;
use App\Models\Fulfillment;
use App\Models\PuzzleProgress;
use App\Models\Review;
use App\Models\Sticker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PuzzlePlayer extends Component
{
    public Enrollment $enrollment;

    public $challenge;

    public ?string $currentFen = null;

    public ?array $currentMoves = null;

    public ?int $currentPuzzleId = null;

    /**
     * @var array<int, string>
     */
    public ?array $currentPuzzleThemes = null;

    public int $totalPuzzles = 0;

    public int $completedPuzzles = 0;

    /**
     * @var array<int, int>
     */
    public array $orderedPuzzleIds = [];

    /**
     * @var array<int, int>
     */
    public array $solvedPuzzleIds = [];

    /**
     * Chronological solve history for the "More info" panel.
     *
     * Each entry: ['sequence' => int, 'solved_at' => CarbonInterface|null]
     * Ordered oldest → newest so the latest solve appears at the bottom.
     *
     * @var array<int, array{sequence: int, solved_at: Carbon|null}>
     */
    public array $challengeProgress = [];

    public bool $showHistory = false;

    public bool $isComplete = false;

    public bool $isFinalPuzzle = false;

    public bool $medalRequestPending = false;

    public bool $reviewPending = false;

    public ?int $puzzleRating = null;

    public ?int $platformRating = null;

    public ?string $reviewTitle = null;

    public ?string $reviewBody = null;

    public ?string $completionToken = null;

    protected ?string $proofSessionKey = null;

    public function mount(Enrollment $enrollment)
    {
        if (auth()->id() !== $enrollment->user_id) {
            abort(403);
        }

        if (! in_array($enrollment->status, ['active', 'completed'], true)) {
            abort(403);
        }

        $this->enrollment = $enrollment;

        $this->ensureProofSessionNonce();

        $this->challenge = $this->enrollment->challenge()->with('puzzles')->first();
        $this->totalPuzzles = $this->challenge->puzzles->count();

        $this->loadCurrentPuzzle();
    }

    /**
     * Toggle the "More info" disclosure on the challenge info card.
     */
    public function toggleHistory(): void
    {
        $this->showHistory = ! $this->showHistory;
    }

    public function loadCurrentPuzzle()
    {
        $challengePuzzles = $this->challenge->puzzles;

        $solvedPuzzleIds = PuzzleProgress::where('user_id', auth()->id())
            ->where('challenge_id', $this->enrollment->challenge_id)
            ->whereNotNull('solved_at')
            ->pluck('puzzle_id')
            ->toArray();

        $this->completedPuzzles = count($solvedPuzzleIds);
        $this->isFinalPuzzle = false;
        $this->completionToken = null;

        $nextPuzzle = $challengePuzzles->first(function ($puzzle) use ($solvedPuzzleIds) {
            return ! in_array($puzzle->id, $solvedPuzzleIds);
        });

        if ($nextPuzzle) {
            $this->currentPuzzleId = $nextPuzzle->id;
            $this->currentFen = $nextPuzzle->fen;
            $this->currentMoves = $nextPuzzle->moves;
            $this->currentPuzzleThemes = is_array($nextPuzzle->themes) ? array_values(array_map('strval', $nextPuzzle->themes)) : [];

            $remaining = $this->totalPuzzles - $this->completedPuzzles;
            $this->isFinalPuzzle = $remaining === 1;
            $this->completionToken = $this->generatePuzzleProofToken($nextPuzzle->id, $this->completedPuzzles);
        } else {
            $this->isComplete = true;
            $this->currentFen = null;
            $this->currentMoves = null;
            $this->currentPuzzleId = null;
            $this->currentPuzzleThemes = null;

            $this->finalizeIfEligible();
            $this->medalRequestPending = $this->resolveMedalRequestPending();
            $this->reviewPending = $this->resolveReviewPending();
        }

        $this->orderedPuzzleIds = $challengePuzzles
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $this->solvedPuzzleIds = array_map('intval', $solvedPuzzleIds);

        // Build a puzzle_id → 1-based sequence map (matches the grid order).
        $sequenceMap = collect($this->orderedPuzzleIds)
            ->mapWithKeys(fn ($id, $index) => [$id => $index + 1]);

        $this->challengeProgress = PuzzleProgress::query()
            ->where('user_id', auth()->id())
            ->where('challenge_id', $this->enrollment->challenge_id)
            ->whereNotNull('solved_at')
            ->orderBy('solved_at')
            ->get(['puzzle_id', 'solved_at'])
            ->map(fn ($record) => [
                'sequence' => $sequenceMap->get((int) $record->puzzle_id, 0),
                'solved_at' => $record->solved_at,
            ])
            ->values()
            ->all();

        $this->dispatch('puzzle-loaded');
    }

    public function solvePuzzle(int $puzzleId, string $proofToken)
    {
        Log::debug('PuzzlePlayer solvePuzzle called', [
            'puzzle_id' => $puzzleId,
            'is_final_puzzle' => $this->isFinalPuzzle,
            'current_puzzle_id' => $this->currentPuzzleId,
        ]);

        if ($this->isFinalPuzzle) {
            Log::debug('PuzzlePlayer solvePuzzle aborted: final puzzle');

            return;
        }

        if (! $this->verifyPuzzleProofToken($puzzleId, $proofToken)) {
            Log::debug('PuzzlePlayer solvePuzzle aborted: token verification failed');

            return;
        }

        Log::debug('PuzzlePlayer solvePuzzle: token verified, marking solved');

        $this->markPuzzleSolved($puzzleId);

        $this->loadCurrentPuzzle();
    }

    public function completeChallenge(string $proofToken): void
    {
        if (! $this->isFinalPuzzle || ! $this->currentPuzzleId) {
            return;
        }

        if (! $this->verifyPuzzleProofToken($this->currentPuzzleId, $proofToken)) {
            return;
        }

        $this->markPuzzleSolved($this->currentPuzzleId);

        $this->loadCurrentPuzzle();
    }

    protected function markPuzzleSolved(int $puzzleId): void
    {
        if ($puzzleId !== $this->currentPuzzleId) {
            return;
        }

        PuzzleProgress::firstOrCreate([
            'user_id' => auth()->id(),
            'challenge_id' => $this->enrollment->challenge_id,
            'puzzle_id' => $puzzleId,
        ], [
            'solved_at' => now(),
        ]);
    }

    protected function finalizeIfEligible(): void
    {
        if ($this->totalPuzzles === 0 || $this->completedPuzzles !== $this->totalPuzzles) {
            return;
        }

        DB::transaction(function () {
            $enrollment = Enrollment::query()->lockForUpdate()->findOrFail($this->enrollment->id);

            if ($enrollment->status !== 'active') {
                $this->enrollment = $enrollment;

                return;
            }

            $enrollment->status = 'completed';
            $enrollment->completed_at = now();
            $enrollment->save();

            Sticker::firstOrCreate([
                'user_id' => auth()->id(),
                'challenge_id' => $enrollment->challenge_id,
            ], [
                'unlocked_at' => now(),
            ]);

            $fulfillment = Fulfillment::query()->firstOrNew([
                'enrollment_id' => $enrollment->id,
            ]);

            if (! $fulfillment->exists) {
                $fulfillment->status = 'pending';
            }

            $fulfillment->save();

            Review::firstOrCreate([
                'enrollment_id' => $enrollment->id,
            ], [
                'challenge_id' => $enrollment->challenge_id,
                'user_id' => auth()->id(),
                'status' => 'pending',
            ]);

            $this->enrollment = $enrollment->fresh();
        });
    }

    protected function resolveMedalRequestPending(): bool
    {
        $fulfillment = Fulfillment::where('enrollment_id', $this->enrollment->id)->first();

        return $fulfillment?->status === 'pending';
    }

    protected function resolveReviewPending(): bool
    {
        return Review::where('enrollment_id', $this->enrollment->id)
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Submit the player review for this challenge.
     *
     * Validates the two chess-piece ratings and optional free-form feedback,
     * then flips the pending Review record into the submitted state.
     */
    public function submitReview(): void
    {
        $validated = $this->validate([
            'puzzleRating' => ['required', 'integer', 'between:1,5'],
            'platformRating' => ['required', 'integer', 'between:1,5'],
            'reviewTitle' => ['nullable', 'string', 'max:120'],
            'reviewBody' => ['nullable', 'string', 'max:2000'],
        ]);

        $review = Review::where('enrollment_id', $this->enrollment->id)->first();

        abort_if(! $review || $review->user_id !== auth()->id(), 403);
        abort_if($review->status !== 'pending', 403);

        $review->update([
            'puzzle_rating' => $validated['puzzleRating'],
            'platform_rating' => $validated['platformRating'],
            'title' => $validated['reviewTitle'],
            'body' => $validated['reviewBody'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->reviewPending = false;
        $this->reset(['puzzleRating', 'platformRating', 'reviewTitle', 'reviewBody']);

        $this->dispatch('review-submitted');
    }

    protected function generatePuzzleProofToken(int $puzzleId, int $completedCount): string
    {
        $this->ensureProofSessionNonce();

        return Crypt::encryptString(json_encode([
            'enrollment_id' => $this->enrollment->id,
            'puzzle_id' => $puzzleId,
            'completed_count' => $completedCount,
            'issued_at' => now()->timestamp,
            'nonce' => session()->get($this->proofSessionKey),
        ], JSON_THROW_ON_ERROR));
    }

    protected function verifyPuzzleProofToken(int $puzzleId, string $proofToken): bool
    {
        $this->ensureProofSessionNonce();

        if ($puzzleId !== $this->currentPuzzleId || $proofToken === '') {
            Log::debug('PuzzlePlayer verify failed: puzzleId mismatch or empty token', [
                'puzzle_id' => $puzzleId,
                'current_puzzle_id' => $this->currentPuzzleId,
                'token_empty' => $proofToken === '',
            ]);

            return false;
        }

        try {
            $payload = json_decode(Crypt::decryptString($proofToken), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            Log::debug('PuzzlePlayer verify failed: token decrypt failed', ['error' => $e->getMessage()]);

            return false;
        }

        if (($payload['enrollment_id'] ?? null) !== $this->enrollment->id) {
            Log::debug('PuzzlePlayer verify failed: enrollment_id mismatch', [
                'payload_enrollment_id' => $payload['enrollment_id'] ?? null,
                'enrollment_id' => $this->enrollment->id,
            ]);

            return false;
        }

        if (($payload['puzzle_id'] ?? null) !== $puzzleId) {
            Log::debug('PuzzlePlayer verify failed: payload puzzle_id mismatch', [
                'payload_puzzle_id' => $payload['puzzle_id'] ?? null,
                'puzzle_id' => $puzzleId,
            ]);

            return false;
        }

        $sessionNonce = session()->get($this->proofSessionKey);
        if (($payload['nonce'] ?? null) !== $sessionNonce) {
            Log::debug('PuzzlePlayer verify failed: nonce mismatch', [
                'payload_nonce' => $payload['nonce'] ?? null,
                'session_nonce' => $sessionNonce,
                'proof_session_key' => $this->proofSessionKey,
            ]);

            return false;
        }

        $issuedAt = (int) ($payload['issued_at'] ?? 0);
        if ($issuedAt < now()->subHours(24)->timestamp) {
            Log::debug('PuzzlePlayer verify failed: token expired', ['issued_at' => $issuedAt]);

            return false;
        }

        $currentSolvedCount = PuzzleProgress::where('user_id', auth()->id())
            ->where('challenge_id', $this->enrollment->challenge_id)
            ->whereNotNull('solved_at')
            ->count();

        $tokenSolvedCount = (int) ($payload['completed_count'] ?? -1);
        if ($tokenSolvedCount !== $currentSolvedCount) {
            Log::debug('PuzzlePlayer verify failed: solved count mismatch', [
                'token_count' => $tokenSolvedCount,
                'db_count' => $currentSolvedCount,
            ]);

            return false;
        }

        return true;
    }

    protected function ensureProofSessionNonce(): void
    {
        if (! $this->proofSessionKey) {
            $this->proofSessionKey = 'puzzle-proof-nonce-'.$this->enrollment->id;
        }

        if (! session()->has($this->proofSessionKey)) {
            session()->put($this->proofSessionKey, Str::uuid()->toString());
        }
    }

    #[Layout('layouts.play')]
    public function render()
    {
        return view('livewire.puzzle-player');
    }
}
