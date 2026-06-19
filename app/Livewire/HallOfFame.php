<?php

namespace App\Livewire;

use App\Models\Challenge;
use App\Models\Sticker;
use Livewire\Attributes\Layout;
use Livewire\Component;

class HallOfFame extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        $challenges = Challenge::active()->orderBy('id')->get();

        $earnedStickerChallengeIds = Sticker::query()
            ->whereBelongsTo(auth()->user())
            ->pluck('challenge_id')
            ->all();

        return view('livewire.hall-of-fame', [
            'challenges' => $challenges,
            'earnedStickerChallengeIds' => $earnedStickerChallengeIds,
        ]);
    }
}
