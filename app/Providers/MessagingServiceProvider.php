<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Enquiry;
use App\Models\Question;
use App\Models\Review;
use App\Observers\AdminAlertObserver;
use App\Services\SeoService;
use Illuminate\Support\ServiceProvider;

class MessagingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SeoService::class);
    }

    public function boot(): void
    {
        // Staff alerts for anything that needs a reply or approval.
        Enquiry::observe(AdminAlertObserver::class);
        Review::observe(AdminAlertObserver::class);
        Question::observe(AdminAlertObserver::class);
    }
}