<?php

namespace App\Livewire;

use App\Models\Enrollment;
use App\Models\Fulfillment;
use App\Models\PuzzleProgress;
use App\Models\Sticker;
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

    public bool $isComplete = false;

    public bool $isFinalPuzzle = false;

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

            $remaining = $this->totalPuzzles - $this->completedPuzzles;
            $this->isFinalPuzzle = $remaining === 1;
            $this->completionToken = $this->generatePuzzleProofToken($nextPuzzle->id, $this->completedPuzzles);
        } else {
            $this->isComplete = true;
            $this->currentFen = null;
            $this->currentMoves = null;
            $this->currentPuzzleId = null;

            $this->finalizeIfEligible();
        }

        $this->orderedPuzzleIds = $challengePuzzles
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $this->solvedPuzzleIds = array_map('intval', $solvedPuzzleIds);

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
                $fulfillment->status = 'ready_to_ship';
            } elseif ($fulfillment->status === 'pending') {
                $fulfillment->status = 'ready_to_ship';
            }

            if (blank($fulfillment->address_snapshot)) {
                $fulfillment->address_snapshot = auth()->user()->addressSnapshot();
            }

            $fulfillment->save();

            $this->enrollment = $enrollment->fresh();
        });
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

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.puzzle-player');
    }
}
