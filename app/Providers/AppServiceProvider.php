<?php

declare(strict_types=1);

namespace App\Providers;

use App\View\Composers\SettingsComposer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Catch N+1 problems early (not in production).
        Model::preventLazyLoading(! $this->app->isProduction());

        // super_admin passes every authorization check.
        Gate::before(fn ($user) => $user->hasRole('super_admin') ? true : null);

               // Settings available in every view (pages, layouts, partials, components).
        View::composer('*', SettingsComposer::class);
    }
}