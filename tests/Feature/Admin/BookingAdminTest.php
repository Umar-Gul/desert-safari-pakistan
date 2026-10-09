<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsStaff;
use Tests\Concerns\CreatesBookableTour;
use Tests\TestCase;

class BookingAdminTest extends TestCase
{
    use ActsAsStaff, CreatesBookableTour, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->setUpBookingConfig();
    }

    public function test_list_filters_by_status(): void
    {
        $confirmed = Booking::factory()->confirmed()->create();
        $pending = Booking::factory()->create();

        $this->actingAs($this->staff('support'))
            ->get('/admin/bookings?status=confirmed')
            ->assertOk()
            ->assertSee($confirmed->reference)
            ->assertDontSee($pending->reference);
    }

    public function test_editor_cannot_open_bookings(): void
    {
        $this->actingAs($this->staff('editor'))->get('/admin/bookings')->assertForbidden();
    }

    public function test_show_and_voucher_pages_render(): void
    {
        $booking = Booking::factory()->create(['customer_name' => 'Zainab Bibi']);
        $admin = $this->staff('admin');

        $this->actingAs($admin)->get("/admin/bookings/{$booking->id}")
            ->assertOk()->assertSee($booking->reference)->assertSee('Zainab Bibi');

        $this->actingAs($admin)->get("/admin/bookings/{$booking->id}/voucher")
            ->assertOk()->assertSee($booking->reference);
    }

    public function test_confirm_then_complete(): void
    {
        $booking = Booking::factory()->create();
        $admin = $this->staff('support');

        $this->actingAs($admin)->post("/admin/bookings/{$booking->id}/confirm")->assertRedirect();
        $this->assertSame(BookingStatus::Confirmed, $booking->fresh()->status);

        $this->actingAs($admin)->post("/admin/bookings/{$booking->id}/complete")->assertRedirect();
        $this->assertSame(BookingStatus::Completed, $booking->fresh()->status);
    }

    public function test_completing_a_pending_booking_shows_an_error(): void
    {
        $booking = Booking::factory()->create();

        $this->actingAs($this->staff('admin'))
            ->post("/admin/bookings/{$booking->id}/complete")
            ->assertSessionHasErrors('booking');

        $this->assertSame(BookingStatus::Pending, $booking->fresh()->status);
    }

    public function test_cancelling_releases_the_seats_and_keeps_a_note(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);
        $booking = app(BookingService::class)->create($tour, $this->quoteInput($date), $this->customer(), 'k1');

        $this->assertEquals(3, $date->fresh()->booked_count);

        $this->actingAs($this->staff('admin'))
            ->post("/admin/bookings/{$booking->id}/cancel", ['reason' => 'Customer asked', 'waive_fee' => '1'])
            ->assertRedirect();

        $booking->refresh();

        $this->assertSame(BookingStatus::Cancelled, $booking->status);
        $this->assertStringContainsString('Customer asked', (string) $booking->admin_notes);
        $this->assertEquals(0, $date->fresh()->booked_count);
    }

    public function test_notes_can_be_saved(): void
    {
        $booking = Booking::factory()->create();

        $this->actingAs($this->staff('support'))
            ->put("/admin/bookings/{$booking->id}/notes", ['admin_notes' => 'Call before pickup'])
            ->assertRedirect();

        $this->assertSame('Call before pickup', $booking->fresh()->admin_notes);
    }

    public function test_staff_can_create_a_manual_booking(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);

        $this->actingAs($this->staff('support'))->post('/admin/bookings', [
            'tour_id' => $tour->id,
            'tour_date' => $date->date->toDateString(),
            'adults' => 2,
            'children' => 1,
            'customer_name' => 'Ali Khan',
            'email' => 'ali@example.com',
            'phone' => '+92 300 1234567',
            'idempotency_key' => 'admin-key-1',
            'confirm_now' => '1',
        ])->assertSessionHasNoErrors();

        $booking = Booking::query()->firstOrFail();

        $this->assertSame(BookingSource::Admin, $booking->source);
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertEquals(23400, $booking->grand_total);
        $this->assertEquals(3, $date->fresh()->booked_count);
    }

    public function test_manual_booking_on_a_full_date_is_refused(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour, 5, 2);

        $this->actingAs($this->staff('admin'))->post('/admin/bookings', [
            'tour_id' => $tour->id,
            'tour_date' => $date->date->toDateString(),
            'adults' => 3,
            'customer_name' => 'Ali Khan',
            'email' => 'ali@example.com',
            'phone' => '+92 300 1234567',
            'idempotency_key' => 'admin-key-2',
        ])->assertSessionHasErrors('booking');

        $this->assertSame(0, Booking::count());
    }

    public function test_csv_export_neutralises_formulas(): void
    {
        Booking::factory()->create(['customer_name' => '=HYPERLINK("http://evil")']);

        $csv = $this->actingAs($this->staff('admin'))
            ->get('/admin/bookings/export')
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Reference,Status', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }
}