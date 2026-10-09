<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\User;
use Spatie\Permission\Models\Role;

trait ActsAsStaff
{
    protected function staff(string $role): User
    {
        Role::findOrCreate($role, 'web');

        return User::factory()->create()->assignRole($role);
    }
}