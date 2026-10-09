<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentTransactionStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsStaff;
use Tests\TestCase;

class PaymentAdminTest extends TestCase
{
    use ActsAsStaff, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_support_can_record_a_partial_payment(): void
    {
        $booking = Booking::factory()->create(['grand_total' => 10000]);

        $this->actingAs($this->staff('support'))
            ->post("/admin/bookings/{$booking->id}/payments", [
                'method' => 'bank_transfer',
                'amount' => '4000',
                'received' => '1',
                'transaction_id' => 'TXN-1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(PaymentStatus::Partial, $booking->fresh()->payment_status);
    }

    public function test_paying_more_than_the_balance_shows_an_error(): void
    {
        $booking = Booking::factory()->create(['grand_total' => 10000]);

        $this->actingAs($this->staff('admin'))
            ->post("/admin/bookings/{$booking->id}/payments", ['method' => 'cash', 'amount' => '15000', 'received' => '1'])
            ->assertSessionHasErrors('booking');

        $this->assertSame(0, Payment::count());
    }

    public function test_pending_payment_can_be_marked_received(): void
    {
        $booking = Booking::factory()->create(['grand_total' => 10000]);
        $payment = app(PaymentService::class)->initiate($booking, PaymentMethod::BankTransfer);

        $this->actingAs($this->staff('support'))
            ->patch("/admin/payments/{$payment->id}/complete", ['transaction_id' => 'TXN-9'])
            ->assertSessionHasNoErrors();

        $this->assertSame(PaymentTransactionStatus::Completed, $payment->fresh()->status);
        $this->assertSame(PaymentStatus::Paid, $booking->fresh()->payment_status);
    }

    public function test_admin_can_refund_but_support_cannot(): void
    {
        $booking = Booking::factory()->create(['grand_total' => 10000]);
        $payment = Payment::factory()->create(['booking_id' => $booking->id, 'amount' => 10000]);

        $this->actingAs($this->staff('support'))->patch("/admin/payments/{$payment->id}/refund")->assertForbidden();

        $this->actingAs($this->staff('admin'))->patch("/admin/payments/{$payment->id}/refund")->assertSessionHasNoErrors();

        $this->assertSame(PaymentTransactionStatus::Refunded, $payment->fresh()->status);
        $this->assertSame(PaymentStatus::Refunded, $booking->fresh()->payment_status);
    }

    public function test_payments_list_is_admin_only(): void
    {
        Payment::factory()->create();

        $this->actingAs($this->staff('admin'))->get('/admin/payments')->assertOk();
        $this->actingAs($this->staff('support'))->get('/admin/payments')->assertForbidden();
    }
}