<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    private const ACTIONS = ['view', 'create', 'update', 'delete'];

    private const CONTENT_MODULES = [
        'tours', 'categories', 'deserts', 'addons', 'camp_types', 'vehicles',
        'facilities', 'highlights', 'faqs', 'event_packages', 'blogs', 'gallery',
    ];

    private const OPERATIONS_MODULES = [
        'bookings', 'availability', 'payments', 'coupons',
        'enquiries', 'questions', 'reviews',
    ];

    private const SYSTEM_MODULES = ['settings', 'users', 'roles'];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $all = [];
        foreach ([...self::CONTENT_MODULES, ...self::OPERATIONS_MODULES, ...self::SYSTEM_MODULES] as $module) {
            foreach (self::ACTIONS as $action) {
                $name = "{$module}.{$action}";
                Permission::findOrCreate($name, 'web');
                $all[] = $name;
            }
        }

        // super_admin: everything (also bypassed by Gate::before)
        Role::findOrCreate(RoleName::SuperAdmin->value, 'web')->syncPermissions($all);

        // admin: everything except users/roles management
        $admin = array_values(array_filter(
            $all,
            fn (string $p) => ! str_starts_with($p, 'users.') && ! str_starts_with($p, 'roles.')
        ));
        Role::findOrCreate(RoleName::Admin->value, 'web')->syncPermissions($admin);

        // editor: full control of content; moderate questions/reviews
        $editor = [];
        foreach (self::CONTENT_MODULES as $module) {
            foreach (self::ACTIONS as $action) {
                $editor[] = "{$module}.{$action}";
            }
        }
        array_push(
            $editor,
            'questions.view', 'questions.update',
            'reviews.view', 'reviews.update'
        );
        Role::findOrCreate(RoleName::Editor->value, 'web')->syncPermissions($editor);

        // support: handle customers and bookings
        Role::findOrCreate(RoleName::Support->value, 'web')->syncPermissions([
            'bookings.view', 'bookings.update',
            'availability.view',
            'payments.view', 'payments.create',
            'enquiries.view', 'enquiries.update',
            'questions.view', 'questions.update',
            'reviews.view', 'reviews.update',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}