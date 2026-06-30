<?php

namespace App\Livewire;

use App\Models\Bundle;
use App\Models\Challenge;
use App\Models\Enrollment;
use Illuminate\Support\Collection;
use Livewire\Component;

class ChallengeIndex extends Component
{
    public string $filter = 'all';

    public function render()
    {
        $challengesQuery = Challenge::query()->withCount('puzzles');

        if ($this->filter !== 'all') {
            $challengesQuery->where('name', 'like', "%{$this->filter}%");
        }

        $challenges = $challengesQuery->get();
        $bundles = Bundle::active()->with('challenges')->get();

        $enrollmentStatuses = $this->loadEnrollmentStatuses(
            $challenges->pluck('id')->merge($bundles->flatMap->challenges->pluck('id'))->unique()
        );

        return view('livewire.challenge-index', [
            'challenges' => $challenges,
            'bundles' => $bundles,
            'enrollmentStatuses' => $enrollmentStatuses,
        ])->layout('layouts.marketing');
    }

    /**
     * @param  Collection<int, int>  $challengeIds
     * @return array<int, string>
     */
    private function loadEnrollmentStatuses(Collection $challengeIds): array
    {
        $user = auth()->user();

        if (! $user || $challengeIds->isEmpty()) {
            return [];
        }

        return Enrollment::query()
            ->whereBelongsTo($user)
            ->whereIn('challenge_id', $challengeIds->all())
            ->with(['orderItem.order:id,status'])
            ->get()
            ->mapWithKeys(function (Enrollment $enrollment): array {
                $orderStatus = $enrollment->orderItem?->order?->status ?? 'pending';

                $status = match (true) {
                    $orderStatus === 'pending' => 'pending',
                    in_array($enrollment->status, ['completed'], true) => 'completed',
                    default => 'active',
                };

                return [$enrollment->challenge_id => $status];
            })
            ->all();
    }
}
