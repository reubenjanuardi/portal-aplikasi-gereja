<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('portal.user.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('portal.user.view');
    }

    public function create(User $user): bool
    {
        return $user->can('portal.user.manage');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('portal.user.manage');
    }

    public function delete(User $user, User $model): bool
    {
        // Don't allow user to delete themselves
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('portal.user.manage');
    }

    public function resetPassword(User $user, User $model): bool
    {
        if (! $model->is_active) {
            return false;
        }

        return $user->can('portal.user.manage');
    }
}
