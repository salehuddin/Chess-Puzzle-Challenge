<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicProfileController extends Controller
{
    public function show(Request $request, User $user): View
    {
        if (! $user->isPubliclyViewable()) {
            abort(404);
        }

        $user->load([
            'stickers' => fn ($query) => $query
                ->whereNotNull('unlocked_at')
                ->with('challenge:id,name,slug,sticker_artwork'),
            'enrollments' => fn ($query) => $query
                ->where('status', 'completed')
                ->with('challenge:id,name,slug')
                ->latest('completed_at'),
        ]);

        return view('profile.show', [
            'user' => $user,
            'completedChallengesCount' => $user->completedChallengesCount(),
            'solvedPuzzlesCount' => $user->solvedPuzzlesCount(),
            'stickersCount' => $user->stickersCount(),
        ]);
    }
}
