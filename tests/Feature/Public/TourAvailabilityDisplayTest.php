<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\Availability;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\CreatesBookableTour;
use Tests\TestCase;

class TourAvailabilityDisplayTest extends TestCase
{
    use CreatesBookableTour, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->setUpBookingConfig();
    }

    public function test_closed_full_and_available_dates_are_rendered_from_shared_statuses(): void
    {
        $tour = $this->makeTour(['title' => 'Shared availability tour']);
        $closed = $this->openDate($tour, 3, 40, ['is_closed' => true]);
        $full = $this->openDate($tour, 4, 40, ['booked_count' => 40]);
        $available = $this->openDate($tour, 5, 40, ['booked_count' => 0]);

        $widget = $this->get(route('tours.show', $tour->slug))->assertOk()->getContent();
        $closedDate = $closed->date->toDateString();
        $fullDate = $full->date->toDateString();
        $availableDate = $available->date->toDateString();

        $this->assertMatchesRegularExpression('/<button[^>]*disabled[^>]*aria-label="[^"]*'.preg_quote(\Carbon\Carbon::parse($closedDate)->format('D, d M Y'), '/').'[^\"]*Closed/', $widget);
        $this->assertMatchesRegularExpression('/<button[^>]*disabled[^>]*aria-label="[^"]*'.preg_quote(\Carbon\Carbon::parse($fullDate)->format('D, d M Y'), '/').'[^\"]*Fully Booked/', $widget);
        $this->assertMatchesRegularExpression('/<button[^>]*aria-label="[^"]*'.preg_quote(\Carbon\Carbon::parse($availableDate)->format('D, d M Y'), '/').'[^\"]*40 left/', $widget);
        $this->assertStringContainsString('Next open dates', $widget);
        $this->assertStringContainsString("this.entry.remaining + ' spots left'", $widget);
        $this->assertStringNotContainsString('â€', $widget);

        $this->get('/tours')->assertOk()->assertSee('40 spots left');

        $this->postJson(route('tours.price', $tour->slug), [
            'tour_date' => $closedDate, 'adults' => 1, 'children' => 0,
        ])->assertUnprocessable()->assertJsonPath('message', 'This tour is not available on the selected date.');
        $this->postJson(route('tours.price', $tour->slug), [
            'tour_date' => $fullDate, 'adults' => 1, 'children' => 0,
        ])->assertUnprocessable()->assertJsonPath('message', 'This date is fully booked.');
        $this->postJson(route('tours.price', $tour->slug), [
            'tour_date' => $availableDate, 'adults' => 2, 'children' => 0,
        ])->assertOk()->assertJsonPath('remaining', 40);
    }

    public function test_guest_count_selection_does_not_reduce_displayed_remaining_capacity(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour, 3, 40, ['booked_count' => 0]);

        $content = $this->get(route('tours.show', $tour->slug))->assertOk()->getContent();

        $this->assertStringContainsString("this.entry.remaining + ' spots left'", $content);
        $this->assertStringContainsString('get guestLimit()', $content);
        $this->assertStringContainsString("this.entry.remaining", $content);
        $this->assertSame(40, app(AvailabilityService::class)->dateStatuses($tour, $date->date, $date->date)[$date->date->toDateString()]['remaining']);
    }

    public function test_past_and_after_cutoff_dates_are_closed_in_the_calendar_and_quote_endpoint(): void
    {
        $this->travelTo(Carbon::parse('2026-10-15 13:00:00'));
        $tour = $this->makeTour();
        $past = Availability::factory()->create(['tour_id' => $tour->id, 'date' => '2026-10-14', 'capacity' => 10]);
        $today = Availability::factory()->create(['tour_id' => $tour->id, 'date' => '2026-10-15', 'capacity' => 10]);

        $content = $this->get(route('tours.show', $tour->slug))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<button[^>]*disabled[^>]*aria-label="[^"]*Wed, 14 Oct 2026[^\"]*Closed/', $content);
        $this->assertMatchesRegularExpression('/<button[^>]*disabled[^>]*aria-label="[^"]*Thu, 15 Oct 2026[^\"]*Closed/', $content);
        $this->postJson(route('tours.price', $tour->slug), [
            'tour_date' => $today->date->toDateString(), 'adults' => 1, 'children' => 0,
        ])->assertUnprocessable()->assertJsonPath('message', 'Same-day bookings close at 12:00. Please choose another date or contact us.');
    }

    public function test_updating_availability_refreshes_calendar_and_listing_data(): void
    {
        $tour = $this->makeTour(['title' => 'Refresh schedule tour']);
        $date = $this->openDate($tour, 3, 10);
        Cache::put('tours.default_list', ['stale' => true], 600);

        $this->get(route('tours.show', $tour->slug))->assertOk()->assertSee('10 left');
        $date->update(['is_closed' => true]);

        $this->assertNull(Cache::get('tours.default_list'));
        $content = $this->get(route('tours.show', $tour->slug))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<button[^>]*disabled[^>]*aria-label="[^"]*Closed/', $content);
        $this->get('/tours')->assertOk()->assertSee('Refresh schedule tour')->assertSee('Closed');
    }
}
