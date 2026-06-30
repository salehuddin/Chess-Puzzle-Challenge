<?php

namespace App\Policies;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChallengePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isEditor();
    }

    public function view(User $user, Challenge $model): bool
    {
        return $user->isAdmin() || $user->isEditor();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEditor();
    }

    public function update(User $user, Challenge $model): bool
    {
        return $user->isAdmin() || $user->isEditor();
    }

    public function delete(User $user, Challenge $model): bool
    {
        return $user->isAdmin() || $user->isEditor();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin() || $user->isEditor();
    }

    public function restore(User $user, Challenge $model): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Challenge $model): bool
    {
        return $user->isAdmin();
    }
}
