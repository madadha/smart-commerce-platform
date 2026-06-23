<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class ResourcePolicy
{
    protected const PREFIX = '';

    public function viewAny(User $user): bool
    {
        return $user->can(static::PREFIX.'.view');
    }

    public function view(User $user, Model $model): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can(static::PREFIX.'.manage');
    }

    public function update(User $user, Model $model): bool
    {
        return $user->can(static::PREFIX.'.manage');
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->can(static::PREFIX.'.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(static::PREFIX.'.manage');
    }

    public function restore(User $user, Model $model): bool
    {
        return $user->can(static::PREFIX.'.manage');
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return false;
    }
}
