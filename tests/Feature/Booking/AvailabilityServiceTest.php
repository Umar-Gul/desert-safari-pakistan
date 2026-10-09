<?php

declare(strict_types=1);

namespace Tests\Feature\Booking;

use App\Exceptions\BookingException;
use App\Models\Availability;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBookableTour;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use CreatesBookableTour, RefreshDatabase;

    private AvailabilityService $availability;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpBookingConfig();
        $this->availability = app(AvailabilityService::class);
    }

    public function test_open_date_with_enough_capacity_passes(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour, 5, 10);

        $result = $this->availability->check($tour, $date->date, 4);

        $this->assertTrue($result->is($date));
    }

    public function test_date_without_an_availability_row_is_not_bookable(): void
    {
        $tour = $this->makeTour();

        $this->expectException(BookingException::class);

        $this->availability->check($tour, now()->addDays(5), 1);
    }

    public function test_closed_date_is_rejected(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour, 5, 10, ['is_closed' => true]);

        $this->expectException(BookingException::class);

        $this->availability->check($tour, $date->date, 1);
    }

    public function test_not_enough_seats_is_rejected_with_remaining_count(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour, 5, 10, ['booked_count' => 8]);

        try {
            $this->availability->check($tour, $date->date, 3);
            $this->fail('Expected a BookingException.');
        } catch (BookingException $e) {
            $this->assertStringContainsString('Only 2 spots', $e->getMessage());
        }
    }

    public function test_past_date_is_rejected(): void
    {
        $tour = $this->makeTour();
        $date = Availability::factory()->create([
            'tour_id' => $tour->id,
            'date' => now()->subDay()->toDateString(),
            'capacity' => 10,
        ]);

        $this->expectException(BookingException::class);

        $this->availability->check($tour, $date->date, 1);
    }

    public function test_same_day_booking_closes_after_the_cutoff(): void
    {
        $tour = $this->makeTour();

        $this->travelTo(Carbon::parse('2026-10-07 15:00:00'));
        $date = Availability::factory()->create([
            'tour_id' => $tour->id,
            'date' => '2026-10-07',
            'capacity' => 10,
        ]);

        $this->expectException(BookingException::class);

        $this->availability->check($tour, $date->date, 1);
    }

    public function test_same_day_booking_is_allowed_before_the_cutoff(): void
    {
        $tour = $this->makeTour();

        $this->travelTo(Carbon::parse('2026-10-07 09:00:00'));
        $date = Availability::factory()->create([
            'tour_id' => $tour->id,
            'date' => '2026-10-07',
            'capacity' => 10,
        ]);

        $this->assertTrue($this->availability->check($tour, $date->date, 1)->is($date));
    }

    public function test_inactive_tour_is_rejected(): void
    {
        $tour = $this->makeTour(['is_active' => false]);
        $date = $this->openDate($tour);

        $this->expectException(BookingException::class);

        $this->availability->check($tour, $date->date, 1);
    }

    public function test_calendar_reports_remaining_and_open_flags(): void
    {
        $tour = $this->makeTour();
        $open = $this->openDate($tour, 3, 10, ['booked_count' => 4]);
        $full = $this->openDate($tour, 4, 10, ['booked_count' => 10]);
        $closed = $this->openDate($tour, 5, 10, ['is_closed' => true]);

        $calendar = $this->availability->calendar($tour, now(), now()->addDays(10));

        $this->assertSame(6, $calendar[$open->date->toDateString()]['remaining']);
        $this->assertTrue($calendar[$open->date->toDateString()]['open']);
        $this->assertFalse($calendar[$full->date->toDateString()]['open']);
        $this->assertFalse($calendar[$closed->date->toDateString()]['open']);
    }
}