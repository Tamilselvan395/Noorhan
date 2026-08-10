<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'HR']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('Super Admin') && $user->id !== $model->id;
    }
}