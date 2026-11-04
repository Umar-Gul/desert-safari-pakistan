<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\Blog;
use App\Enums\BookingStatus;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\CampType;
use App\Models\Category;
use App\Models\Desert;
use App\Models\Enquiry;
use App\Models\EventPackage;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Question;
use App\Models\Review;
use App\Models\TourExclusion;
use App\Models\TourHighlight;
use App\Models\TourInclusion;
use App\Models\TourItineraryItem;
use App\Models\TrustBadge;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBookableTour;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use CreatesBookableTour, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->setUpBookingConfig();
    }

    public function test_home_page_shows_the_main_sections(): void
    {
        $tour = $this->makeTour(['title' => 'Thar Sunrise Safari', 'is_featured' => true]);
        Faq::factory()->create(['question' => 'Is the dinner halal?']);
        Review::factory()->approved()->create(['tour_id' => $tour->id, 'comment' => 'Unforgettable night in the dunes.']);
        Blog::factory()->create(['title' => 'Best time for Cholistan']);
        TrustBadge::factory()->create(['title' => 'Local guides']);
        EventPackage::factory()->create(['title' => 'School Trip Package']);

        $response = $this->get('/')
            ->assertOk()
            ->assertSee('Thar Sunrise Safari')
            ->assertSee('Is the dinner halal?')
            ->assertSee('Unforgettable night')
            ->assertSee('Best time for Cholistan')
            ->assertSee('Local guides')
            ->assertSee('School Trip Package');

        $response
            ->assertDontSee('Explore by activity')
            ->assertDontSee('@if', false)
            ->assertDontSee('@foreach', false)
            ->assertDontSee('@once', false)
            ->assertDontSee('{{ $imageUrl }}', false);
    }

    public function test_navigation_shows_active_categories_from_the_database(): void
    {
        $category = Category::factory()->create([
            'name' => 'Database Safari',
            'slug' => 'database-safari',
            'show_in_menu' => true,
        ]);
        Category::factory()->inactive()->create([
            'name' => 'Inactive Safari',
            'slug' => 'inactive-safari',
            'show_in_menu' => true,
        ]);

        $content = $this->get('/')->assertOk()->getContent();

        $matched = preg_match('/<nav[^>]*aria-label="Safari categories"[^>]*>(.*?)<\/nav>/s', $content, $navigation);

        $this->assertSame(1, $matched, 'The header navigation should be rendered.');
        $this->assertStringContainsString('href="'.route('categories.show', $category->slug).'"', $navigation[1]);
        $this->assertStringContainsString($category->name, $navigation[1]);
        $this->assertStringNotContainsString('Inactive Safari', $navigation[1]);
    }

    public function test_tour_list_hides_inactive_tours_and_filters_by_price(): void
    {
        $this->makeTour(['title' => 'Budget Dune Ride', 'base_price_adult' => 2000, 'discount_percent' => 0]);
        $this->makeTour(['title' => 'Luxury Camp Night', 'base_price_adult' => 20000, 'discount_percent' => 0]);
        $this->makeTour(['title' => 'Hidden Draft Tour', 'is_active' => false]);

        $this->get('/tours')
            ->assertOk()
            ->assertSee('Budget Dune Ride')
            ->assertSee('Luxury Camp Night')
            ->assertDontSee('Hidden Draft Tour');

        $this->get('/tours?min_price=5000')->assertDontSee('Budget Dune Ride')->assertSee('Luxury Camp Night');
        $this->get('/tours?max_price=5000')->assertSee('Budget Dune Ride')->assertDontSee('Luxury Camp Night');
        $this->get('/tours?sort=price_desc')->assertSeeInOrder(['Luxury Camp Night', 'Budget Dune Ride']);
        $this->get('/tours?sort=price_asc')->assertSeeInOrder(['Budget Dune Ride', 'Luxury Camp Night']);
    }

    public function test_tour_listing_shows_availability_status_from_schedule_and_active_bookings(): void
    {
        $available = $this->makeTour(['title' => 'Available Safari']);
        $closed = $this->makeTour(['title' => 'Closed Safari']);
        $full = $this->makeTour(['title' => 'Fully Booked Safari']);
        $unscheduled = $this->makeTour(['title' => 'Unscheduled Safari']);

        Availability::factory()->create([
            'tour_id' => $available->id, 'date' => now()->addDays(5)->toDateString(),
            'capacity' => 10, 'booked_count' => 3,
        ]);
        Availability::factory()->closed()->create([
            'tour_id' => $closed->id, 'date' => now()->addDays(5)->toDateString(),
            'capacity' => 10,
        ]);
        $fullDate = Availability::factory()->create([
            'tour_id' => $full->id, 'date' => now()->addDays(5)->toDateString(),
            'capacity' => 3, 'booked_count' => 0,
        ]);
        Booking::factory()->confirmed()->create([
            'tour_id' => $full->id, 'tour_date' => $fullDate->date->toDateString(),
            'adults' => 2, 'children' => 1,
        ]);

        $this->get('/tours/')
            ->assertOk()
            ->assertSee('Available Safari')->assertSee('7 spots left')
            ->assertSee('Closed Safari')->assertSee('Closed')
            ->assertSee('Fully Booked Safari')->assertSee('Fully Booked')
            ->assertSee('Unscheduled Safari')->assertSee('Closed');
    }

    public function test_same_day_cutoff_is_consistent_across_listing_calendar_and_quote_validation(): void
    {
        $this->travelTo(Carbon::parse('2026-10-07 13:00:00'));
        $tour = $this->makeTour(['title' => 'Same Day Safari']);
        $date = Availability::factory()->create([
            'tour_id' => $tour->id,
            'date' => '2026-10-07',
            'capacity' => 10,
        ]);

        $this->get('/tours')
            ->assertOk()
            ->assertSee('Same Day Safari')
            ->assertSee('Closed');

        $datePicker = $this->get(route('tours.show', $tour->slug))->assertOk()->getContent();
        $this->assertMatchesRegularExpression(
            '/<button[^>]*disabled[^>]*aria-label="[^\"]*'.preg_quote($date->date->format('D, d M Y'), '/').'[^\"]*Closed/',
            $datePicker,
        );
        $this->assertStringNotContainsString('10 spots left', $datePicker);

        $this->postJson(route('tours.price', $tour->slug), [
            'tour_date' => $date->date->toDateString(),
            'adults' => 1,
            'children' => 0,
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Same-day bookings close at 12:00. Please choose another date or contact us.');
    }

    public function test_tour_page_lists_all_dates_with_shared_closed_full_and_available_statuses(): void
    {
        $tour = $this->makeTour(['title' => 'Date status safari']);
        $available = Availability::factory()->create([
            'tour_id' => $tour->id, 'date' => today()->addDays(2)->toDateString(),
            'capacity' => 8, 'booked_count' => 3,
        ]);
        $full = Availability::factory()->create([
            'tour_id' => $tour->id, 'date' => today()->addDays(3)->toDateString(),
            'capacity' => 4, 'booked_count' => 4,
        ]);
        $closed = Availability::factory()->closed()->create([
            'tour_id' => $tour->id, 'date' => today()->addDays(4)->toDateString(),
        ]);

        $content = $this->get(route('tours.show', $tour->slug))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<button[^>]*aria-label="[^\"]*'.preg_quote($available->date->format('D, d M Y'), '/').'[^\"]*5 left/', $content);
        $this->assertMatchesRegularExpression('/<button[^>]*disabled[^>]*aria-label="[^\"]*'.preg_quote($full->date->format('D, d M Y'), '/').'[^\"]*Fully Booked/', $content);
        $this->assertMatchesRegularExpression('/<button[^>]*disabled[^>]*aria-label="[^\"]*'.preg_quote($closed->date->format('D, d M Y'), '/').'[^\"]*Closed/', $content);
        $this->assertStringContainsString('Availability key', $content);
        $this->assertStringContainsString('no availability', strtolower($content));
    }

    public function test_price_filter_uses_the_discounted_price(): void
    {
        $this->makeTour(['title' => 'Discounted Jeep Safari', 'base_price_adult' => 10000, 'discount_percent' => 50]);

        $this->get('/tours?max_price=6000')->assertSee('Discounted Jeep Safari');
        $this->get('/tours?min_price=6000')->assertDontSee('Discounted Jeep Safari');
    }

    public function test_search_and_category_pages_list_matching_tours(): void
    {
        $camel = $this->makeTour(['title' => 'Camel Sunset Trek']);
        $quad = $this->makeTour(['title' => 'Quad Bike Rush']);

        $this->get('/search?q=Camel')->assertOk()->assertSee('Camel Sunset Trek')->assertDontSee('Quad Bike Rush');
        $this->get('/search')->assertOk()->assertSee('Search tours');

        $this->get('/category/'.$camel->category->slug)->assertOk()->assertSee('Camel Sunset Trek')->assertDontSee('Quad Bike Rush');
    }

    public function test_tour_page_shows_every_tab_of_content(): void
    {
        $tour = $this->makeTour(['title' => 'Derawar Fort Safari', 'description' => '<p>A day among forts and dunes.</p>']);
        TourHighlight::create(['tour_id' => $tour->id, 'text' => 'Pick and drop by SUV', 'sort_order' => 0]);
        TourItineraryItem::create(['tour_id' => $tour->id, 'time_label' => '5:00 PM', 'title' => 'Dune photo stop', 'sort_order' => 0]);
        TourInclusion::create(['tour_id' => $tour->id, 'text' => 'Traditional dinner', 'sort_order' => 0]);
        TourExclusion::create(['tour_id' => $tour->id, 'text' => 'Personal tips', 'sort_order' => 0]);
        $this->openDate($tour);

        $this->get('/tours/'.$tour->slug)
            ->assertOk()
            ->assertSee('Derawar Fort Safari')
            ->assertSee('A day among forts and dunes.')
            ->assertSee('Pick and drop by SUV')
            ->assertSee('Dune photo stop')
            ->assertSee('Traditional dinner')
            ->assertSee('Personal tips')
            ->assertSee('Cancellation policy');
    }

    public function test_inactive_and_unknown_tours_return_404(): void
    {
        $inactive = $this->makeTour(['is_active' => false]);

        $this->get('/tours/'.$inactive->slug)->assertNotFound();
        $this->get('/tours/does-not-exist')->assertNotFound();
    }

    public function test_only_approved_questions_and_reviews_are_public(): void
    {
        $tour = $this->makeTour();

        Question::factory()->approved()->create(['tour_id' => $tour->id, 'question' => 'Is parking available at camp?']);
        Question::factory()->create(['tour_id' => $tour->id, 'question' => 'Secret pending question text?']);
        Review::factory()->approved()->create(['tour_id' => $tour->id, 'comment' => 'Approved review about the dunes.']);
        Review::factory()->create(['tour_id' => $tour->id, 'comment' => 'Pending review nobody should see.']);

        $this->get('/tours/'.$tour->slug)
            ->assertOk()
            ->assertSee('Is parking available at camp?')
            ->assertDontSee('Secret pending question text?')
            ->assertSee('Approved review about the dunes.')
            ->assertDontSee('Pending review nobody should see.');
    }

    public function test_desert_camp_and_vehicle_pages_list_related_tours(): void
    {
        $desert = Desert::factory()->create(['name' => 'Cholistan Desert']);
        $tour = $this->makeTour(['title' => 'Derawar Fort Trip', 'desert_id' => $desert->id]);
        $camp = CampType::factory()->create(['name' => 'VIP Majlis Camp']);
        $vehicle = Vehicle::factory()->create(['name' => 'Toyota Land Cruiser']);
        $tour->campTypes()->attach($camp);
        $tour->vehicles()->attach($vehicle);

        $this->get('/deserts/'.$desert->slug)->assertOk()->assertSee('Cholistan Desert')->assertSee('Derawar Fort Trip');
        $this->get('/camps/'.$camp->slug)->assertOk()->assertSee('VIP Majlis Camp')->assertSee('Derawar Fort Trip');
        $this->get('/vehicles/'.$vehicle->slug)->assertOk()->assertSee('Toyota Land Cruiser')->assertSee('Derawar Fort Trip');

        $hidden = Vehicle::factory()->create(['is_active' => false]);
        $this->get('/vehicles/'.$hidden->slug)->assertNotFound();
    }

    public function test_blog_shows_published_posts_only(): void
    {
        $published = Blog::factory()->create(['title' => 'Packing list for the desert', 'content' => '<p>Bring sunscreen.</p>']);
        $draft = Blog::factory()->draft()->create(['title' => 'Unfinished draft post']);

        $this->get('/blog')->assertOk()->assertSee('Packing list for the desert')->assertDontSee('Unfinished draft post');
        $this->get('/blog/'.$published->slug)->assertOk()->assertSee('Bring sunscreen.');
        $this->get('/blog/'.$draft->slug)->assertNotFound();
        $this->get('/blog/category/'.$published->category->slug)->assertOk()->assertSee('Packing list for the desert');
    }

    public function test_gallery_and_events_pages_render(): void
    {
        GalleryItem::factory()->create(['category' => 'Dunes']);
        EventPackage::factory()->create(['title' => 'Corporate Retreat']);

        $this->get('/gallery')->assertOk()->assertSee('Dunes');
        $this->get('/gallery?category=Dunes')->assertOk();
        $this->get('/events')->assertOk()->assertSee('Corporate Retreat');
    }

    public function test_contact_form_stores_an_enquiry(): void
    {
        $this->post('/contact', [
            'name' => 'Sara Ahmed',
            'email' => 'sara@example.com',
            'subject' => 'School trip for 40 students',
            'message' => 'We would like a quote for a school trip in March.',
        ])->assertRedirect(route('contact'))->assertSessionHas('status');

        $this->assertSame(1, Enquiry::count());
        $this->assertSame('new', Enquiry::first()->status->value);
    }

    public function test_honeypot_blocks_bots(): void
    {
        $this->post('/contact', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'subject' => 'Cheap pills',
            'message' => 'Buy cheap pills now at my website.',
            'website' => 'http://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertSame(0, Enquiry::count());
    }

    public function test_question_and_review_wait_for_approval(): void
    {
        $tour = $this->makeTour();

        $this->post('/questions', [
            'tour_id' => $tour->id,
            'name' => 'Omar',
            'email' => 'omar@example.com',
            'question' => 'Can we bring our own food?',
        ])->assertRedirect(route('tours.show', ['slug' => $tour->slug, 'tab' => 'faq']));

        $this->assertSame('pending', Question::first()->status->value);

        $this->post(route('reviews.store', $tour->slug), [
            'name' => 'Sara',
            'country' => 'United Kingdom',
            'rating' => 5,
            'title' => 'Magical',
            'comment' => 'A wonderful evening in the dunes.',
        ])->assertRedirect(route('tours.show', ['slug' => $tour->slug, 'tab' => 'reviews']));

        $this->assertSame('pending', Review::first()->status->value);
        $this->assertSame(0, $tour->fresh()->reviews_count_cached);
    }

    public function test_review_needs_a_valid_rating_and_comment(): void
    {
        $tour = $this->makeTour();

        $this->post(route('reviews.store', $tour->slug), ['name' => 'Sara', 'rating' => 9, 'comment' => 'short'])
            ->assertSessionHasErrors(['rating', 'comment']);

        $this->assertSame(0, Review::count());
    }
}
