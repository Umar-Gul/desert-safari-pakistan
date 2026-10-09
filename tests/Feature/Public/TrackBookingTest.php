<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\BookingStatus;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBookableTour;
use Tests\TestCase;

class TrackBookingTest extends TestCase
{
    use CreatesBookableTour, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->setUpBookingConfig();
    }

    public function test_customer_can_find_a_booking_with_the_email_used(): void
    {
        $tour = $this->makeTour(['title' => 'Camel Sunset Trek']);
        $date = $this->openDate($tour);
        $booking = app(BookingService::class)->create($tour, $this->quoteInput($date), $this->customer(), 'k1');

        $this->get('/track-booking')->assertOk();

        $this->post('/track-booking', ['reference' => strtolower($booking->reference), 'contact' => 'ALI@example.com'])
            ->assertOk()
            ->assertSee($booking->reference)
            ->assertSee('Camel Sunset Trek')
            ->assertSee('Pending')
            ->assertSee('Cancel this booking');
    }

    public function test_wrong_contact_details_give_a_generic_error(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);
        $booking = app(BookingService::class)->create($tour, $this->quoteInput($date), $this->customer(), 'k1');

        $this->post('/track-booking', ['reference' => $booking->reference, 'contact' => 'someone@else.com'])
            ->assertSessionHasErrors('booking');
    }

    public function test_customer_can_cancel_and_the_seats_are_released(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);
        $booking = app(BookingService::class)->create($tour, $this->quoteInput($date), $this->customer(), 'k1');

        $this->assertEquals(3, $date->fresh()->booked_count);

        $this->post('/track-booking/cancel', ['reference' => $booking->reference, 'contact' => 'ali@example.com'])
            ->assertOk()
            ->assertSee('has been cancelled');

        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
        $this->assertEquals(0, $date->fresh()->booked_count);
    }

    public function test_cancelling_needs_the_right_contact_details(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);
        $booking = app(BookingService::class)->create($tour, $this->quoteInput($date), $this->customer(), 'k1');

        $this->post('/track-booking/cancel', ['reference' => $booking->reference, 'contact' => 'stranger@example.com'])
            ->assertSessionHasErrors('booking');

        $this->assertSame(BookingStatus::Pending, $booking->fresh()->status);
    }
}