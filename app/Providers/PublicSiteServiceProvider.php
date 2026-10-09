<?php

declare(strict_types=1);

namespace App\Providers;

use App\View\Composers\NavigationComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class PublicSiteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('quote', fn (Request $r) => Limit::perMinute(60)->by($r->ip()));
        RateLimiter::for('booking', fn (Request $r) => Limit::perMinute(10)->by($r->ip()));
        RateLimiter::for('forms', fn (Request $r) => Limit::perMinute(5)->by($r->ip()));
        RateLimiter::for('track', fn (Request $r) => Limit::perMinute(10)->by($r->ip()));

        View::composer(['layouts.partials.header', 'layouts.partials.footer'], NavigationComposer::class);
    }
}