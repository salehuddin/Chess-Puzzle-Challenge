<?php

namespace App\Livewire;

use App\Models\Enrollment;
use App\Models\PuzzleProgress;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * @return Collection<int, object>
     */
    protected function buildCards(): Collection
    {
        $user = auth()->user();

        $enrollments = Enrollment::query()
            ->whereBelongsTo($user)
            ->with([
                'challenge' => fn ($query) => $query
                    ->select('id', 'name', 'slug')
                    ->withCount('puzzles'),
                'fulfillment',
                'orderItem.order:id,user_id,status,created_at',
            ])
            ->latest()
            ->get();

        $challengeIds = $enrollments->pluck('challenge_id')->unique()->values();

        $solvedCounts = PuzzleProgress::query()
            ->where('user_id', $user->id)
            ->when($challengeIds->isNotEmpty(), fn ($query) => $query->whereIn('challenge_id', $challengeIds->all()))
            ->whereNotNull('solved_at')
            ->selectRaw('challenge_id, COUNT(*) as solved_count')
            ->groupBy('challenge_id')
            ->pluck('solved_count', 'challenge_id');

        return $enrollments->map(function (Enrollment $enrollment) use ($solvedCounts): object {
            $order = $enrollment->orderItem?->order;
            $fulfillment = $enrollment->fulfillment;

            $status = match (true) {
                $order?->status === 'pending' => 'pending',
                in_array($fulfillment?->status, ['shipped', 'delivered'], true) => 'shipped',
                $enrollment->status === 'completed' || $fulfillment?->status === 'ready_to_ship' => 'completed',
                default => 'active',
            };

            return (object) [
                'id' => $enrollment->id,
                'challenge' => $enrollment->challenge,
                'status' => $status,
                'created_at' => $order?->created_at ?? $enrollment->created_at,
                'completed_at' => $enrollment->completed_at,
                'tracking_url' => $fulfillment?->tracking_url,
                'courier' => $fulfillment?->courier,
                'solved_puzzles_count' => (int) ($solvedCounts[(string) $enrollment->challenge_id] ?? 0),
                'enrollment_id' => $enrollment->id,
                'order_id' => $order?->id,
            ];
        });
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $cards = $this->buildCards();

        $pending = $cards->filter(fn ($card) => $card->status === 'pending');
        $active = $cards->filter(fn ($card) => in_array($card->status, ['active'], true));
        $completed = $cards->filter(fn ($card) => in_array($card->status, ['completed', 'shipped'], true));

        return view('livewire.dashboard', [
            'pendingCards' => $pending,
            'activeCards' => $active,
            'completedCards' => $completed,
        ]);
    }
}
