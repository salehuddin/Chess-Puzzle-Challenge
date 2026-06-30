<?php

namespace App\Policies;

use App\Models\Bundle;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BundlePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isEditor();
    }

    public function view(User $user, Bundle $model): bool
    {
        return $user->isAdmin() || $user->isEditor();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEditor();
    }

    public function update(User $user, Bundle $model): bool
    {
        return $user->isAdmin() || $user->isEditor();
    }

    public function delete(User $user, Bundle $model): bool
    {
        return $user->isAdmin() || $user->isEditor();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin() || $user->isEditor();
    }

    public function restore(User $user, Bundle $model): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Bundle $model): bool
    {
        return $user->isAdmin();
    }
}
