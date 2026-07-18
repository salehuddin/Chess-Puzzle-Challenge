<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReviewPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isEditor();
    }

    public function view(User $user, Review $model): bool
    {
        return $user->isAdmin() || $user->isEditor();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Review $model): bool
    {
        return $user->isAdmin() || $user->isEditor();
    }

    public function delete(User $user, Review $model): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Review $model): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Review $model): bool
    {
        return $user->isAdmin();
    }
}
