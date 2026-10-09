<?php

declare(strict_types=1);

namespace Tests\Feature\Phase6;

use App\Enums\ReviewStatus;
use App\Models\Addon;
use App\Models\Availability;
use App\Models\Blog;
use App\Models\CampType;
use App\Models\Category;
use App\Models\Desert;
use App\Models\EventPackage;
use App\Models\Facility;
use App\Models\Faq;
use App\Models\Review;
use App\Models\Tour;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed_images' => false]);
        $this->withoutVite();
    }

    public function test_seeds_the_full_pakistan_catalogue(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(8, Category::count());
        $this->assertSame(4, Desert::count());
        $this->assertSame(12, Tour::count());
        $this->assertSame(4, CampType::count());
        $this->assertSame(5, Vehicle::count());
        $this->assertSame(10, Facility::count());
        $this->assertSame(8, Addon::count());
        $this->assertSame(5, EventPackage::count());
        $this->assertSame(6, Blog::count());
        $this->assertGreaterThanOrEqual(12, Faq::query()->where('scope', 'global')->count());
        $this->assertSame(10, Review::query()->where('status', ReviewStatus::Approved->value)->count());
        $this->assertSame(12 * 90, Availability::count());
        $this->assertNotNull(User::query()->where('email', config('site.seed_admin.email'))->first());

        // Varied prices and discounts, and every tour is fully described.
        $this->assertGreaterThan(5, Tour::query()->distinct()->count('base_price_adult'));
        $this->assertGreaterThan(0, Tour::query()->where('discount_percent', '>', 0)->count());

        foreach (Tour::query()->withCount(['highlights', 'itineraryItems', 'inclusions', 'exclusions', 'additionalInfos', 'faqs', 'addons'])->get() as $tour) {
            $this->assertGreaterThanOrEqual(3, $tour->highlights_count, $tour->title);
            $this->assertGreaterThanOrEqual(3, $tour->itinerary_items_count, $tour->title);
            $this->assertGreaterThanOrEqual(2, $tour->inclusions_count, $tour->title);
            $this->assertGreaterThanOrEqual(1, $tour->exclusions_count, $tour->title);
            $this->assertGreaterThanOrEqual(1, $tour->additional_infos_count, $tour->title);
            $this->assertGreaterThanOrEqual(2, $tour->faqs_count, $tour->title);
            $this->assertGreaterThanOrEqual(1, $tour->addons_count, $tour->title);
        }

        // Review ratings were rolled up onto the tours.
        $this->assertGreaterThan(0, Tour::query()->where('reviews_count_cached', '>', 0)->count());
    }

    public function test_seeding_twice_creates_no_duplicates(): void
    {
        $this->seed(DatabaseSeeder::class);
        $counts = [Category::count(), Tour::count(), Faq::count(), Review::count(), Blog::count(), Availability::count()];

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($counts, [Category::count(), Tour::count(), Faq::count(), Review::count(), Blog::count(), Availability::count()]);
    }

    public function test_seeded_content_is_family_friendly(): void
    {
        $this->seed(DatabaseSeeder::class);

        $text = collect()
            ->merge(Tour::all()->map(fn ($t) => $t->title.' '.$t->short_description.' '.$t->description))
            ->merge(Faq::all()->map(fn ($f) => $f->question.' '.$f->answer))
            ->merge(Blog::all()->map(fn ($b) => $b->title.' '.$b->content))
            ->merge(Facility::all()->map(fn ($f) => $f->name.' '.$f->description))
            ->merge(Addon::all()->map(fn ($a) => $a->name.' '.$a->description))
            ->implode(' ');

        $this->assertDoesNotMatchRegularExpression('/alcohol|shisha|hookah|\bbeer\b|\bwine\b/i', $text);
    }

    public function test_public_pages_render_with_the_seeded_catalogue(): void
    {
        $this->seed(DatabaseSeeder::class);
        $tour = Tour::query()->where('slug', 'cholistan-sunset-jeep-safari')->firstOrFail();

        $this->get('/')->assertOk()->assertSee('Cholistan Sunset Jeep Safari')->assertSee('Is the food halal?');
        $this->get('/tours')->assertOk()->assertSee('Thar Village and Desert Experience');
        $this->get('/tours/'.$tour->slug)->assertOk()->assertSee('Pick and drop by private vehicle')->assertSee('Is the trip suitable for children?');
        $this->get('/category/cholistan-desert-safari')->assertOk();
        $this->get('/deserts/thar-desert')->assertOk();
        $this->get('/blog')->assertOk()->assertSee('The Best Time to Visit the Cholistan Desert');
        $this->get('/events')->assertOk()->assertSee('School Trip Package');
        $this->get('/sitemap.xml')->assertOk()->assertSee('cholistan-sunset-jeep-safari', false);
    }
}