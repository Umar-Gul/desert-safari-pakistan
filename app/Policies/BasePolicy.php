<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** Role-based policy. Subclasses only change the role lists. super_admin passes via Gate::before. */
abstract class BasePolicy
{
    /** @var list<string> */
    protected array $viewRoles = ['super_admin', 'admin', 'editor', 'support'];

    /** @var list<string> */
    protected array $writeRoles = ['super_admin', 'admin', 'editor'];

    /** @var list<string> */
    protected array $deleteRoles = ['super_admin', 'admin'];

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole($this->viewRoles);
    }

    public function view(User $user, Model $model): bool
    {
        return $user->hasAnyRole($this->viewRoles);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole($this->writeRoles);
    }

    public function update(User $user, Model $model): bool
    {
        return $user->hasAnyRole($this->writeRoles);
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->hasAnyRole($this->deleteRoles);
    }

    public function restore(User $user, Model $model): bool
    {
        return $user->hasAnyRole($this->deleteRoles);
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $user->hasRole('super_admin');
    }
}