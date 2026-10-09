<?php

declare(strict_types=1);

namespace Tests\Feature\Booking;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentTransactionStatus;
use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $payments;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payments = app(PaymentService::class);
    }

    private function booking(): Booking
    {
        return Booking::factory()->create(['grand_total' => 10000]);
    }

    public function test_partial_then_full_payment_updates_the_payment_status(): void
    {
        $booking = $this->booking();

        $this->payments->recordManual($booking, PaymentMethod::BankTransfer, 4000, 'TXN1');
        $this->assertSame(PaymentStatus::Partial, $booking->fresh()->payment_status);

        $this->payments->recordManual($booking, PaymentMethod::Cash, 6000);
        $this->assertSame(PaymentStatus::Paid, $booking->fresh()->payment_status);
    }

    public function test_paying_more_than_the_balance_is_rejected(): void
    {
        $booking = $this->booking();

        $this->expectException(BookingException::class);

        $this->payments->recordManual($booking, PaymentMethod::Cash, 10001);
    }

    public function test_cancelled_booking_rejects_payments(): void
    {
        $booking = Booking::factory()->cancelled()->create(['grand_total' => 10000]);

        $this->expectException(BookingException::class);

        $this->payments->recordManual($booking, PaymentMethod::Cash, 1000);
    }

    public function test_manual_gateway_creates_a_pending_payment_until_confirmed(): void
    {
        $booking = $this->booking();

        $payment = $this->payments->initiate($booking, PaymentMethod::BankTransfer);

        $this->assertSame(PaymentTransactionStatus::Pending, $payment->status);
        $this->assertEquals(10000, $payment->amount);
        $this->assertSame(PaymentStatus::Unpaid, $booking->fresh()->payment_status);

        $this->payments->markCompleted($payment, 'TXN-99');

        $this->assertSame(PaymentTransactionStatus::Completed, $payment->fresh()->status);
        $this->assertSame('TXN-99', $payment->fresh()->transaction_id);
        $this->assertSame(PaymentStatus::Paid, $booking->fresh()->payment_status);
    }

    public function test_refunds_move_the_booking_back_to_partial_then_refunded(): void
    {
        $booking = $this->booking();

        $first = $this->payments->recordManual($booking, PaymentMethod::BankTransfer, 4000);
        $second = $this->payments->recordManual($booking, PaymentMethod::Cash, 6000);
        $this->assertSame(PaymentStatus::Paid, $booking->fresh()->payment_status);

        $this->payments->markRefunded($second);
        $this->assertSame(PaymentStatus::Partial, $booking->fresh()->payment_status);

        $this->payments->markRefunded($first);
        $this->assertSame(PaymentStatus::Refunded, $booking->fresh()->payment_status);
    }

    public function test_only_completed_payments_can_be_refunded(): void
    {
        $booking = $this->booking();
        $pending = $this->payments->initiate($booking, PaymentMethod::BankTransfer, 3000);

        $this->expectException(BookingException::class);

        $this->payments->markRefunded($pending);
    }
}