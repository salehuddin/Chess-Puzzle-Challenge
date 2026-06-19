<?php

namespace App\Livewire;

use App\Models\Bundle;
use App\Models\Challenge;
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

        return view('livewire.challenge-index', [
            'challenges' => $challengesQuery->get(),
            'bundles' => Bundle::active()->with('challenges')->get(),
        ])->layout('layouts.marketing');
    }
}
