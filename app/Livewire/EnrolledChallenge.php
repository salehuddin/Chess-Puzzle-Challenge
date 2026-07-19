<?php

namespace App\Livewire;

use App\Models\Enrollment;
use App\Models\PuzzleProgress;
use App\Models\Review;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

class EnrolledChallenge extends Component
{
    public Enrollment $enrollment;

    public $challenge;

    public $fulfillment;

    public $review;

    public $sticker;

    public string $derivedStatus;

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
     * Chronological solve history.
     *
     * @var array<int, array{sequence: int, solved_at: Carbon|null}>
     */
    public array $challengeProgress = [];

    // Inline review form state
    public ?int $puzzleRating = null;

    public ?int $platformRating = null;

    public ?string $reviewTitle = null;

    public ?string $reviewBody = null;

    public function mount(Enrollment $enrollment)
    {
        if (auth()->id() !== $enrollment->user_id) {
            abort(403);
        }

        $this->enrollment = $enrollment;

        $this->enrollment->load([
            'challenge.puzzles',
            'fulfillment',
            'review',
            'sticker',
            'orderItem.order:id,user_id,status,created_at',
        ]);

        $this->challenge = $this->enrollment->challenge;
        $this->fulfillment = $this->enrollment->fulfillment;
        $this->review = $this->enrollment->review;
        $this->sticker = $this->enrollment->sticker;

        $this->derivedStatus = $this->enrollment->derivedStatus();

        $this->totalPuzzles = $this->challenge->puzzles->count();

        $this->loadProgress();

        if ($this->review && $this->review->status === 'pending') {
            $this->puzzleRating = $this->review->puzzle_rating;
            $this->platformRating = $this->review->platform_rating;
            $this->reviewTitle = $this->review->title;
            $this->reviewBody = $this->review->body;
        }
    }

    protected function loadProgress(): void
    {
        $challengePuzzles = $this->challenge->puzzles;

        $solvedPuzzleIds = PuzzleProgress::where('user_id', auth()->id())
            ->where('challenge_id', $this->enrollment->challenge_id)
            ->whereNotNull('solved_at')
            ->pluck('puzzle_id')
            ->toArray();

        $this->completedPuzzles = count($solvedPuzzleIds);

        $this->orderedPuzzleIds = $challengePuzzles
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $this->solvedPuzzleIds = array_map('intval', $solvedPuzzleIds);

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
    }

    /**
     * Submit the inline review form on this page.
     *
     * Mirrors PuzzlePlayer::submitReview() — same validation rules and
     * record transition — so admin tools see identical shape regardless
     * of which surface the player used to leave the review.
     */
    public function submitReview(): void
    {
        $validated = $this->validate([
            'puzzleRating' => ['required', 'integer', 'between:1,5'],
            'platformRating' => ['required', 'integer', 'between:1,5'],
            'reviewTitle' => ['nullable', 'string', 'max:120'],
            'reviewBody' => ['nullable', 'string', 'max:2000'],
        ]);

        $review = $this->enrollment->review;

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

        $this->review = $review->fresh();
        $this->reset(['puzzleRating', 'platformRating', 'reviewTitle', 'reviewBody']);

        $this->dispatch('review-submitted');
    }

    /**
     * After submitReview dispatches 'review-submitted', Alpine re-calls this
     * to refresh the read-only display from the freshly-updated row.
     */
    public function refreshReview(): void
    {
        $this->review = $this->enrollment->review?->fresh();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.enrolled-challenge');
    }
}
