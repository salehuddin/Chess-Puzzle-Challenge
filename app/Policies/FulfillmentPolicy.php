<?php

namespace App\Policies;

use App\Models\Fulfillment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FulfillmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isEditor() || $user->isFulfillment();
    }

    public function view(User $user, Fulfillment $model): bool
    {
        return $user->isAdmin() || $user->isEditor() || $user->isFulfillment();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEditor() || $user->isFulfillment();
    }

    public function update(User $user, Fulfillment $model): bool
    {
        return $user->isAdmin() || $user->isEditor() || $user->isFulfillment();
    }

    public function delete(User $user, Fulfillment $model): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Fulfillment $model): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Fulfillment $model): bool
    {
        return $user->isAdmin();
    }
}
