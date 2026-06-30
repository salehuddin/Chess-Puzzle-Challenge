<?php

namespace App\Livewire;

use App\Models\Challenge;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\PuzzleProgress;
use App\Models\Sticker;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{
    public string $activeTab = 'challenges';

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
                'medal_request_pending' => $enrollment->status === 'completed' && $fulfillment?->status === 'pending',
            ];
        });
    }

    /**
     * @return array{challenges: Collection<int, Challenge>, earnedStickerChallengeIds: array<int, int>}
     */
    protected function buildCollectionData(): array
    {
        $challenges = Challenge::active()->orderBy('id')->get();

        $earnedStickerChallengeIds = Sticker::query()
            ->whereBelongsTo(auth()->user())
            ->pluck('challenge_id')
            ->all();

        return [
            'challenges' => $challenges,
            'earnedStickerChallengeIds' => $earnedStickerChallengeIds,
        ];
    }

    /**
     * @return Collection<int, Order>
     */
    protected function buildOrdersData(): Collection
    {
        return Order::query()
            ->whereBelongsTo(auth()->user())
            ->with(['items.enrollments:id,order_item_id,status,completed_at'])
            ->latest()
            ->get();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['challenges', 'collection', 'orders'], true) ? $tab : 'challenges';
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $cards = $this->buildCards();

        $pending = $cards->filter(fn ($card) => $card->status === 'pending');
        $active = $cards->filter(fn ($card) => in_array($card->status, ['active'], true));
        $completed = $cards->filter(fn ($card) => in_array($card->status, ['completed', 'shipped'], true));
        $pendingMedalRequests = $cards->filter(fn ($card) => $card->medal_request_pending);

        $collection = $this->buildCollectionData();
        $orders = $this->buildOrdersData();

        return view('livewire.dashboard', [
            'pendingCards' => $pending,
            'activeCards' => $active,
            'completedCards' => $completed,
            'pendingMedalRequests' => $pendingMedalRequests,
            'collectionChallenges' => $collection['challenges'],
            'earnedStickerChallengeIds' => $collection['earnedStickerChallengeIds'],
            'orders' => $orders,
        ]);
    }
}
