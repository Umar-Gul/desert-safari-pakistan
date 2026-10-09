<?php

declare(strict_types=1);

namespace Tests\Feature\Booking;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Events\BookingCancelled;
use App\Events\BookingCreated;
use App\Exceptions\BookingException;
use App\Models\Addon;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\Coupon;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Concerns\CreatesBookableTour;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use CreatesBookableTour, RefreshDatabase;

    private BookingService $bookings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpBookingConfig();
        $this->bookings = app(BookingService::class);
    }

    public function test_creates_a_pending_booking_with_a_price_snapshot_and_reserves_seats(): void
    {
        Event::fake([BookingCreated::class]);

        $tour = $this->makeTour();
        $date = $this->openDate($tour);
        $addon = Addon::factory()->create(['name' => 'Henna', 'price' => 500]);
        $tour->addons()->attach($addon->id);

        $booking = $this->bookings->create(
            $tour,
            $this->quoteInput($date, ['addons' => [$addon->id => 1]]),
            $this->customer(),
            'key-1',
        );

        $this->assertMatchesRegularExpression('/^DSP-\d{4}-\d{6}$/', $booking->reference);
        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertSame(PaymentStatus::Unpaid, $booking->payment_status);
        $this->assertEquals(26000, $booking->subtotal);
        $this->assertEquals(500, $booking->addons_total);
        $this->assertEquals(2600, $booking->discount_total);
        $this->assertEquals(23900, $booking->grand_total);
        $this->assertSame('Ali Khan', $booking->customer_name);
        $this->assertSame('Henna', $booking->addons->first()->name_snapshot);
        $this->assertEquals(3, $date->fresh()->booked_count);

        Event::assertDispatched(BookingCreated::class);
    }

    public function test_same_idempotency_key_returns_the_same_booking_once(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);
        $input = $this->quoteInput($date);

        $first = $this->bookings->create($tour, $input, $this->customer(), 'same-key');
        $second = $this->bookings->create($tour, $input, $this->customer(), 'same-key');

        $this->assertTrue($first->is($second));
        $this->assertSame(1, Booking::count());
        $this->assertEquals(3, $date->fresh()->booked_count);
    }

    public function test_overbooking_is_rejected_and_nothing_is_saved(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour, 5, 2);

        try {
            $this->bookings->create($tour, $this->quoteInput($date, ['adults' => 3, 'children' => 0]), $this->customer(), 'k');
            $this->fail('Expected a BookingException.');
        } catch (BookingException) {
            $this->assertSame(0, Booking::count());
            $this->assertEquals(0, $date->fresh()->booked_count);
        }
    }

    public function test_valid_coupon_is_applied_and_its_usage_counted(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);
        $coupon = Coupon::factory()->create(['code' => 'SAVE10']);

        $booking = $this->bookings->create($tour, $this->quoteInput($date, ['coupon_code' => 'SAVE10']), $this->customer(), 'k');

        $this->assertEquals(21060, $booking->grand_total);
        $this->assertSame($coupon->id, $booking->coupon_id);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_coupon_over_its_usage_limit_blocks_the_booking(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);
        Coupon::factory()->create(['code' => 'ONCE', 'usage_limit' => 1, 'used_count' => 1]);

        $this->expectException(BookingException::class);

        $this->bookings->create($tour, $this->quoteInput($date, ['coupon_code' => 'ONCE']), $this->customer(), 'k');
    }

    public function test_confirm_then_complete_follows_the_status_flow(): void
    {
        $tour = $this->makeTour();
        $booking = $this->bookings->create($tour, $this->quoteInput($this->openDate($tour)), $this->customer(), 'k');

        $this->bookings->confirm($booking);
        $this->assertSame(BookingStatus::Confirmed, $booking->fresh()->status);

        $this->bookings->complete($booking);
        $this->assertSame(BookingStatus::Completed, $booking->fresh()->status);
    }

    public function test_pending_booking_cannot_be_completed_directly(): void
    {
        $tour = $this->makeTour();
        $booking = $this->bookings->create($tour, $this->quoteInput($this->openDate($tour)), $this->customer(), 'k');

        $this->expectException(BookingException::class);

        $this->bookings->complete($booking);
    }

    public function test_early_cancellation_is_free_and_releases_the_seats(): void
    {
        Event::fake([BookingCancelled::class]);

        $tour = $this->makeTour();
        $date = $this->openDate($tour, 5);
        $booking = $this->bookings->create($tour, $this->quoteInput($date), $this->customer(), 'k');

        $quote = $this->bookings->cancel($booking);

        $this->assertTrue($quote->isFree);
        $this->assertEquals(0, $quote->feeAmount);
        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
        $this->assertNotNull($booking->fresh()->cancelled_at);
        $this->assertEquals(0, $date->fresh()->booked_count);

        Event::assertDispatched(BookingCancelled::class);
    }

    public function test_late_cancellation_charges_the_configured_fee(): void
    {
        $this->travelTo(Carbon::parse('2026-10-07 10:00:00'));

        $tour = $this->makeTour();
        $date = Availability::factory()->create([
            'tour_id' => $tour->id,
            'date' => '2026-10-08',
            'capacity' => 10,
            'booked_count' => 2,
        ]);
        $booking = Booking::factory()->create([
            'tour_id' => $tour->id,
            'tour_date' => '2026-10-08',
            'pickup_time' => '09:00',
            'adults' => 2,
            'children' => 0,
            'grand_total' => 10000,
        ]);

        $quote = $this->bookings->cancel($booking);

        // 23 hours before start is inside the 24 hour free window.
        $this->assertFalse($quote->isFree);
        $this->assertEquals(50, $quote->feePercent);
        $this->assertEquals(5000, $quote->feeAmount);
        $this->assertEquals(0, $date->fresh()->booked_count);
    }

    public function test_admin_can_waive_the_late_fee(): void
    {
        $this->travelTo(Carbon::parse('2026-10-07 10:00:00'));

        $booking = Booking::factory()->create([
            'tour_date' => '2026-10-08',
            'pickup_time' => '09:00',
            'grand_total' => 10000,
        ]);

        $quote = $this->bookings->cancel($booking, 'admin', 'Weather', waiveFee: true);

        $this->assertTrue($quote->isFree);
        $this->assertStringContainsString('Cancelled by admin: Weather', $booking->fresh()->admin_notes);
    }

    public function test_cancelling_twice_is_rejected(): void
    {
        $tour = $this->makeTour();
        $booking = $this->bookings->create($tour, $this->quoteInput($this->openDate($tour)), $this->customer(), 'k');

        $this->bookings->cancel($booking);

        $this->expectException(BookingException::class);

        $this->bookings->cancel($booking);
    }

    public function test_lookup_matches_reference_with_email_or_phone_only(): void
    {
        $tour = $this->makeTour();
        $booking = $this->bookings->create($tour, $this->quoteInput($this->openDate($tour)), $this->customer(), 'k');

        $this->assertTrue($this->bookings->lookup(strtolower($booking->reference), 'ALI@example.com')->is($booking));
        $this->assertTrue($this->bookings->lookup($booking->reference, '+92 300 1234567')?->is($booking) ?? false);
        $this->assertNull($this->bookings->lookup($booking->reference, 'someone@else.com'));
        $this->assertNull($this->bookings->lookup('DSP-2026-000000', 'ali@example.com'));
    }
}