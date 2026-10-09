<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Enums\QuickLinkGroup;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\CampType;
use App\Models\Category;
use App\Models\Desert;
use App\Models\EventPackage;
use App\Models\Facility;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Highlight;
use App\Models\QuickLink;
use App\Models\Review;
use App\Models\Tour;
use App\Models\TrustBadge;
use App\Models\Vehicle;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // All sections in one cached payload; observers clear it on any content change.
        $data = Cache::remember(CacheKeys::HOME, 600, fn () => $this->load());

        return view('frontend.home', $data);
    }

    /** @return array<string, mixed> */
    private function load(): array
    {
        return [
            'trustBadges' => TrustBadge::query()->active()->with('media')->ordered()->get(),
            'activityLinks' => QuickLink::query()->active()->inSection(QuickLinkGroup::ActivityTypes)->ordered()->get(),
            'popularLinks' => QuickLink::query()->active()->inSection(QuickLinkGroup::PopularSearches)->ordered()->get(),

            'spotlight' => Tour::query()->active()->featured()
                ->with(['category:id,name,slug', 'media', 'highlights', 'inclusions', 'itineraryItems'])
                ->ordered()->first(),
            'categories' => Category::query()->active()->withActivitiesCount()->with('media')->ordered()->get(),
            'featured' => Tour::query()->active()->featured()->withCardRelations()->ordered()->limit(8)->get(),
            'bestSelling' => Tour::query()->active()->bestSelling()->withCardRelations()->ordered()->limit(8)->get(),

            'eventPackages' => EventPackage::query()->active()->with('media')->ordered()->limit(5)->get(),
            'vehicles' => Vehicle::query()->active()->with('media')->ordered()->limit(8)->get(),
            'campTypes' => CampType::query()->active()->with('media')->ordered()->limit(8)->get(),
            'deserts' => Desert::query()->active()->with('media')->ordered()->limit(8)->get(),
            'facilities' => Facility::query()->active()->with('media')->ordered()->limit(8)->get(),
            'highlights' => Highlight::query()->active()->with('media')->ordered()->limit(8)->get(),

            'faqs' => Faq::query()->active()->globalOnly()->ordered()->limit(12)->get(),
            'gallery' => GalleryItem::query()->active()->with('media')->ordered()->limit(8)->get(),
            'testimonials' => Review::query()->approved()->with('tour:id,title,slug')->latest('id')->limit(6)->get(),
            'blogs' => Blog::query()->published()->with(['media', 'category:id,name,slug'])->latest('published_at')->limit(3)->get(),

            'tourOptions' => Tour::query()->active()->ordered()->get(['id', 'title']),
        ];
    }
}