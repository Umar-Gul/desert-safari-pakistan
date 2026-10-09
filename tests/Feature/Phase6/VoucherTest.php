<?php

declare(strict_types=1);

namespace Tests\Feature\Phase6;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\Concerns\ActsAsStaff;
use Tests\TestCase;

class VoucherTest extends TestCase
{
    use ActsAsStaff, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_staff_can_download_the_pdf_voucher(): void
    {
        $booking = Booking::factory()->create();

        $response = $this->actingAs($this->staff('support'))
            ->get("/admin/bookings/{$booking->id}/voucher.pdf")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_editors_cannot_download_vouchers(): void
    {
        $booking = Booking::factory()->create();

        $this->actingAs($this->staff('editor'))->get("/admin/bookings/{$booking->id}/voucher.pdf")->assertForbidden();
    }

    public function test_customer_can_download_a_confirmed_voucher_with_a_signed_link(): void
    {
        $booking = Booking::factory()->confirmed()->create();
        $url = URL::temporarySignedRoute('booking.voucher', now()->addHour(), ['reference' => $booking->reference]);

        $response = $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');

        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_unsigned_links_and_pending_bookings_are_refused(): void
    {
        $confirmed = Booking::factory()->confirmed()->create();
        $pending = Booking::factory()->create();

        $this->get('/booking/'.$confirmed->reference.'/voucher.pdf')->assertForbidden();

        $this->get(URL::temporarySignedRoute('booking.voucher', now()->addHour(), ['reference' => $pending->reference]))
            ->assertNotFound();
    }

    public function test_track_page_offers_the_voucher_only_for_confirmed_bookings(): void
    {
        $confirmed = Booking::factory()->confirmed()->create();
        $pending = Booking::factory()->create();

        $this->post('/track-booking', ['reference' => $confirmed->reference, 'contact' => $confirmed->email])
            ->assertOk()->assertSee('Download voucher (PDF)');

        $this->post('/track-booking', ['reference' => $pending->reference, 'contact' => $pending->email])
            ->assertOk()->assertDontSee('Download voucher (PDF)');
    }
}