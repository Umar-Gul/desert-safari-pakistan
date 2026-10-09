<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $config = config('site.seed_admin');

        $user = User::updateOrCreate(
            ['email' => $config['email']],
            [
                'name' => $config['name'],
                'password' => $config['password'],
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([RoleName::SuperAdmin->value]);
    }
}