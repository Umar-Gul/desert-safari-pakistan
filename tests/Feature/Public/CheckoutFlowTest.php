<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\BookingStatus;
use App\Models\Availability;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBookableTour;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use CreatesBookableTour, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->setUpBookingConfig();
    }

    /** @return array<string, mixed> */
    private function selection(Availability $date, array $extra = []): array
    {
        return array_merge(['tour_date' => $date->date->toDateString(), 'adults' => 2, 'children' => 1], $extra);
    }

    /** @return array<string, mixed> */
    private function submission(Availability $date, array $extra = []): array
    {
        return array_merge($this->selection($date), [
            'customer_name' => 'Ali Khan',
            'email' => 'ali@example.com',
            'phone' => '+92 300 1234567',
            'city' => 'Bahawalpur',
            'terms' => '1',
            'idempotency_key' => 'key-123',
        ], $extra);
    }

    public function test_checkout_page_shows_the_server_calculated_total(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);

        $this->get(route('tours.checkout', $tour->slug).'?'.http_build_query($this->selection($date)))
            ->assertOk()
            ->assertSee('23,400')
            ->assertSee('No payment now');
    }

    public function test_checkout_sends_the_customer_back_when_the_date_is_closed(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour, 5, 10, ['is_closed' => true]);

        $this->get(route('tours.checkout', $tour->slug).'?'.http_build_query($this->selection($date)))
            ->assertRedirect(route('tours.show', $tour->slug))
            ->assertSessionHasErrors('booking');
    }

    public function test_an_invalid_coupon_is_dropped_with_a_warning(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);

        $this->get(route('tours.checkout', $tour->slug).'?'.http_build_query($this->selection($date, ['coupon_code' => 'NOPE'])))
            ->assertOk()
            ->assertSee('The coupon was not applied')
            ->assertSee('23,400');
    }

    public function test_booking_is_created_and_the_customer_reaches_the_thank_you_page(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);

        $response = $this->post(route('tours.checkout.store', $tour->slug), $this->submission($date))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $booking = Booking::query()->firstOrFail();

        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertEquals(23400, $booking->grand_total);
        $this->assertEquals(3, $date->fresh()->booked_count);

        $location = $response->headers->get('Location');
        $this->assertStringContainsString('signature=', (string) $location);

        $this->get($location)->assertOk()->assertSee($booking->reference)->assertSee('Confirm on WhatsApp');
    }

    public function test_thank_you_page_needs_a_signed_link(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);

        $this->post(route('tours.checkout.store', $tour->slug), $this->submission($date));
        $booking = Booking::query()->firstOrFail();

        $this->get('/booking/'.$booking->reference.'/thank-you')->assertForbidden();
    }

    public function test_double_submit_creates_a_single_booking(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);

        $this->post(route('tours.checkout.store', $tour->slug), $this->submission($date));
        $this->post(route('tours.checkout.store', $tour->slug), $this->submission($date));

        $this->assertSame(1, Booking::count());
        $this->assertEquals(3, $date->fresh()->booked_count);
    }

    public function test_terms_and_contact_details_are_required(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);

        $this->post(route('tours.checkout.store', $tour->slug), $this->submission($date, ['terms' => null, 'email' => '']))
            ->assertSessionHasErrors(['terms', 'email']);

        $this->assertSame(0, Booking::count());
    }

    public function test_a_full_date_shows_an_error_and_saves_nothing(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour, 5, 2);

        $this->post(route('tours.checkout.store', $tour->slug), $this->submission($date))
            ->assertSessionHasErrors('booking');

        $this->assertSame(0, Booking::count());
    }

    public function test_honeypot_blocks_booking_bots(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);

        $this->post(route('tours.checkout.store', $tour->slug), $this->submission($date, ['website' => 'http://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertSame(0, Booking::count());
    }
}