<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Middleware\EnsureStaff;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Staff area. Loaded from routes/web.php, so the "web" middleware applies.
| Phase 4C-4D add their routes inside this group.
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', EnsureStaff::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::middleware('can:manage-settings')->group(function () {
            Route::get('settings/{group?}', [SettingsController::class, 'edit'])->name('settings.edit');
            Route::put('settings/{group}', [SettingsController::class, 'update'])->name('settings.update');
        });

        // Config-driven CRUD modules (config/admin_resources.php).
        foreach (config('admin_resources', []) as $key => $definition) {
            $controller = ResourceController::class;

            Route::get($key, [$controller, 'index'])->defaults('resource', $key)->name("{$key}.index");
            Route::get("{$key}/create", [$controller, 'create'])->defaults('resource', $key)->name("{$key}.create");
            Route::post($key, [$controller, 'store'])->defaults('resource', $key)->name("{$key}.store");
            Route::get("{$key}/{record}/edit", [$controller, 'edit'])->defaults('resource', $key)->name("{$key}.edit");
            Route::put("{$key}/{record}", [$controller, 'update'])->defaults('resource', $key)->name("{$key}.update");
            Route::delete("{$key}/{record}", [$controller, 'destroy'])->defaults('resource', $key)->name("{$key}.destroy");

            if (! empty($definition['toggle'])) {
                Route::patch("{$key}/{record}/toggle", [$controller, 'toggle'])->defaults('resource', $key)->name("{$key}.toggle");
            }
        }

        // Phase 4C-4D routes go here.
    });