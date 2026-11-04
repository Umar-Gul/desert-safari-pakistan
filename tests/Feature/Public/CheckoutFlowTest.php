<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\BookingStatus;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\User;
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
        $this->actingAs(User::factory()->create());
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

    public function test_direct_booking_request_cannot_book_a_closed_date(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour, 5, 10, ['is_closed' => true]);

        $this->post(route('tours.checkout.store', $tour->slug), $this->submission($date))
            ->assertSessionHasErrors('booking');
        $this->assertSame(0, Booking::count());
        $this->assertSame(0, $date->fresh()->booked_count);
    }

    public function test_tour_date_picker_disables_closed_and_fully_booked_dates(): void
    {
        $tour = $this->makeTour();
        $closed = $this->openDate($tour, 5, 10, ['is_closed' => true]);
        $full = $this->openDate($tour, 6, 2);
        Booking::factory()->confirmed()->create([
            'tour_id' => $tour->id, 'tour_date' => $full->date->toDateString(),
            'adults' => 2, 'children' => 0,
        ]);

        $response = $this->get(route('tours.show', $tour->slug))->assertOk();
        $this->assertMatchesRegularExpression(
            '/<button[^>]*disabled[^>]*aria-label="[^\"]*'.preg_quote($closed->date->format('D, d M Y'), '/').'[^\"]*Closed/',
            $response->getContent(),
        );
        $this->assertMatchesRegularExpression(
            '/<button[^>]*disabled[^>]*aria-label="[^\"]*'.preg_quote($full->date->format('D, d M Y'), '/').'[^\"]*Fully Booked/',
            $response->getContent(),
        );
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

    public function test_guest_booking_is_saved_for_authentication_then_restored_without_creating_booking_early(): void
    {
        auth()->logout();
        $user = User::factory()->create(['email' => 'ali@example.com']);
        $tour = $this->makeTour(['title' => 'Morning Desert Safari Quad Biking']);
        $date = $this->openDate($tour);
        $submission = $this->submission($date, ['pickup_time' => '16:30', 'special_requests' => 'Vegetarian meal']);

        $this->post(route('tours.checkout.store', $tour->slug), $submission)
            ->assertRedirect(route('login'))
            ->assertSessionHas('pending_booking.data.customer_name', 'Ali Khan');
        $this->assertSame(0, Booking::count());
        $this->assertSame(0, $date->fresh()->booked_count);

        $failedLogin = $this->post(route('login'), ['email' => $user->email, 'password' => 'incorrect']);
        $failedLogin->assertSessionHasErrors('email')->assertSessionHas('pending_booking.data.pickup_time', '16:30');
        $this->assertGuest();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('tours.checkout', ['slug' => $tour->slug, ...$this->selection($date)], absolute: false));
        $this->assertAuthenticatedAs($user);
        $this->get(route('tours.checkout', $tour->slug).'?'.http_build_query($this->selection($date)))
            ->assertOk()->assertSee('Ali Khan')->assertSee('ali@example.com')->assertSee('value="16:30"', false)->assertSee('Vegetarian meal');

        $bookingResponse = $this->post(route('tours.checkout.store', $tour->slug), $this->submission($date))
            ->assertRedirect();
        $booking = Booking::query()->firstOrFail();
        $this->assertSame($user->id, $booking->user_id);
        $this->assertSame(3, $date->fresh()->booked_count);
        $bookingResponse->assertSessionMissing('pending_booking');
        $bookingResponse->assertSessionMissing('pending_bookings.'.$tour->slug);
    }

    public function test_guest_can_register_and_return_to_the_pending_checkout(): void
    {
        auth()->logout();
        $tour = $this->makeTour();
        $date = $this->openDate($tour);
        $submission = $this->submission($date, ['pickup_time' => '18:45', 'special_requests' => 'Wheelchair access']);
        $this->post(route('tours.checkout.store', $tour->slug), $submission)
            ->assertRedirect(route('login'));

        $this->get(route('register'))->assertOk();
        $this->post(route('register'), [
            'name' => 'Ali Khan', 'email' => 'ali@example.com',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ])->assertRedirect();

        $this->assertAuthenticated();
        $this->get(route('tours.checkout', $tour->slug).'?'.http_build_query($this->selection($date)))
            ->assertOk()->assertSee('Ali Khan')->assertSee('ali@example.com')
            ->assertSee('value="18:45"', false)->assertSee('Wheelchair access');
        $this->assertSame(0, Booking::count());
    }

    public function test_guest_book_now_get_preserves_selection_across_failed_and_successful_login(): void
    {
        auth()->logout();
        $user = User::factory()->create(['email' => 'booker@example.com']);
        $tour = $this->makeTour(['title' => 'Morning Desert Safari Quad Biking']);
        $date = $this->openDate($tour);
        $query = $this->selection($date, [
            'pickup_time' => '16:30',
            'pickup_location' => 'Hotel lobby',
            'special_requests' => 'Vegetarian meal',
        ]);
        $checkout = route('tours.checkout', ['slug' => $tour->slug, ...$query]);
        $resume = route('tours.checkout', ['slug' => $tour->slug, ...$this->selection($date)], absolute: false);

        $this->get($checkout)
            ->assertRedirect(route('login'))
            ->assertSessionHas('pending_booking.slug', $tour->slug)
            ->assertSessionHas('pending_booking.data.tour_date', $date->date->toDateString())
            ->assertSessionHas('pending_booking.data.adults', 2)
            ->assertSessionHas('pending_bookings.'.$tour->slug.'.tour_date', $date->date->toDateString())
            ->assertSessionHas('url.intended', $resume)
            ->assertSessionHas('pending_booking.data.pickup_time', '16:30');

        $this->post(route('login'), ['email' => $user->email, 'password' => 'incorrect'])
            ->assertSessionHasErrors('email')
            ->assertSessionHas('pending_booking.data.special_requests', 'Vegetarian meal')
            ->assertSessionHas('url.intended', $resume);
        $this->assertGuest();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect($resume);
        $this->assertAuthenticatedAs($user);

        $this->get($resume)
            ->assertOk()
            ->assertSee('value="16:30"', false)
            ->assertSee('Vegetarian meal')
            ->assertSee('Hotel lobby');
        $this->assertSame(0, Booking::count());
    }

    public function test_guest_book_now_get_returns_to_same_checkout_after_registration(): void
    {
        auth()->logout();
        $tour = $this->makeTour();
        $date = $this->openDate($tour);
        $query = $this->selection($date, ['pickup_time' => '18:45', 'special_requests' => 'Wheelchair access']);
        $checkout = route('tours.checkout', ['slug' => $tour->slug, ...$query]);
        $resume = route('tours.checkout', ['slug' => $tour->slug, ...$this->selection($date)], absolute: false);

        $this->get($checkout)->assertRedirect(route('login'));

        $this->post(route('register'), [
            'name' => 'Ali Khan', 'email' => 'new-booker@example.com',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ])->assertRedirect($resume);

        $this->assertAuthenticated();
        $this->get($resume)
            ->assertOk()
            ->assertSee('value="18:45"', false)
            ->assertSee('Wheelchair access');
        $this->assertSame(0, Booking::count());
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
