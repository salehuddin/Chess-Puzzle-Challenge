<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Order $model): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Order $model): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Order $model): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Order $model): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Order $model): bool
    {
        return $user->isAdmin();
    }
}
