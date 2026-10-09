<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Addon;
use App\Models\Availability;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Booking;
use App\Models\CampType;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Desert;
use App\Models\Enquiry;
use App\Models\EventPackage;
use App\Models\Facility;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Highlight;
use App\Models\NewsletterSubscriber;
use App\Models\Payment;
use App\Models\Question;
use App\Models\QuickLink;
use App\Models\Review;
use App\Models\Tour;
use App\Models\TrustBadge;
use App\Models\User;
use App\Models\Vehicle;
use App\Policies\BookingPolicy;
use App\Policies\CatalogPolicy;
use App\Policies\FinancePolicy;
use App\Policies\ModerationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthorizationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // super_admin can do everything; null means "let the policy decide".
        Gate::before(fn (User $user) => $user->hasRole('super_admin') ? true : null);

        foreach ($this->policies() as $policy => $models) {
            foreach ($models as $model) {
                Gate::policy($model, $policy);
            }
        }

        Gate::define('manage-settings', fn (User $user) => $user->hasAnyRole(['super_admin', 'admin']));
        Gate::define('manage-users', fn (User $user) => $user->hasRole('super_admin'));
    }

    /** @return array<class-string, list<class-string>> */
    private function policies(): array
    {
        return [
            CatalogPolicy::class => [
                Category::class, Desert::class, Tour::class, Addon::class, CampType::class,
                Vehicle::class, Facility::class, Highlight::class, Faq::class,
                EventPackage::class, BlogCategory::class, Blog::class, GalleryItem::class,
                TrustBadge::class, QuickLink::class,
            ],
            ModerationPolicy::class => [
                Review::class, Question::class, Enquiry::class, NewsletterSubscriber::class,
            ],
            BookingPolicy::class => [Booking::class],
            FinancePolicy::class => [Payment::class, Coupon::class, Availability::class],
        ];
    }
}