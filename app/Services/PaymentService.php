<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentTransactionStatus;
use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\Payments\ManualPaymentGateway;
use App\Services\Payments\PaymentGateway;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(private readonly Container $container) {}

    public function gatewayFor(PaymentMethod $method): PaymentGateway
    {
        $class = config("booking.payment_gateways.{$method->value}")
            ?? config('booking.payment_gateways.default', ManualPaymentGateway::class);

        return $this->container->make($class);
    }

    /** Start a payment through the method's gateway. Manual gateway returns a pending payment. */
    public function initiate(Booking $booking, PaymentMethod $method, ?float $amount = null): Payment
    {
        $amount ??= $booking->balanceDue();

        $this->assertPayable($booking, $amount);

        $result = $this->gatewayFor($method)->charge($booking, $method, $amount);

        return DB::transaction(function () use ($booking, $method, $amount, $result) {
            $payment = $booking->payments()->create([
                'method' => $method,
                'amount' => $amount,
                'currency' => $booking->currency,
                'status' => $result->status,
                'transaction_id' => $result->transactionId,
                'paid_at' => $result->status === PaymentTransactionStatus::Completed ? now() : null,
                'notes' => $result->message,
                'payload' => $result->payload ?: null,
            ]);

            $this->syncBookingPaymentStatus($booking);

            return $payment;
        });
    }

    /** Admin records money already received. */
    public function recordManual(
        Booking $booking,
        PaymentMethod $method,
        float $amount,
        ?string $transactionId = null,
        ?string $notes = null,
    ): Payment {
        $this->assertPayable($booking, $amount);

        return DB::transaction(function () use ($booking, $method, $amount, $transactionId, $notes) {
            $payment = $booking->payments()->create([
                'method' => $method,
                'amount' => $amount,
                'currency' => $booking->currency,
                'status' => PaymentTransactionStatus::Completed,
                'transaction_id' => $transactionId,
                'paid_at' => now(),
                'notes' => $notes,
            ]);

            $this->syncBookingPaymentStatus($booking);

            return $payment;
        });
    }

    /** Admin confirms a pending payment was received. */
    public function markCompleted(Payment $payment, ?string $transactionId = null): Payment
    {
        if ($payment->status === PaymentTransactionStatus::Completed) {
            return $payment;
        }

        if ($payment->status !== PaymentTransactionStatus::Pending) {
            throw new BookingException('Only pending payments can be marked as completed.');
        }

        $booking = $payment->booking;

        $this->assertPayable($booking, (float) $payment->amount);

        return DB::transaction(function () use ($payment, $booking, $transactionId) {
            $payment->update([
                'status' => PaymentTransactionStatus::Completed,
                'transaction_id' => $transactionId ?? $payment->transaction_id,
                'paid_at' => now(),
            ]);

            $this->syncBookingPaymentStatus($booking);

            return $payment;
        });
    }

    public function markRefunded(Payment $payment): Payment
    {
        if ($payment->status !== PaymentTransactionStatus::Completed) {
            throw new BookingException('Only completed payments can be refunded.');
        }

        return DB::transaction(function () use ($payment) {
            $payment->update(['status' => PaymentTransactionStatus::Refunded]);

            $this->syncBookingPaymentStatus($payment->booking);

            return $payment;
        });
    }

    /** Recalculate bookings.payment_status from completed/refunded payments. */
    public function syncBookingPaymentStatus(Booking $booking): void
    {
        $booking->unsetRelation('payments');

        $paid = $booking->paidAmount();
        $hasRefund = $booking->payments()
            ->where('status', PaymentTransactionStatus::Refunded->value)
            ->exists();

        $status = match (true) {
            $paid <= 0 => $hasRefund ? PaymentStatus::Refunded : PaymentStatus::Unpaid,
            $paid + 0.009 >= (float) $booking->grand_total => PaymentStatus::Paid,
            default => PaymentStatus::Partial,
        };

        $booking->update(['payment_status' => $status]);
    }

    private function assertPayable(Booking $booking, float $amount): void
    {
        if (in_array($booking->status, [BookingStatus::Cancelled, BookingStatus::Refunded], true)) {
            throw new BookingException('Payments cannot be added to a cancelled booking.');
        }

        if ($amount <= 0) {
            throw new BookingException('The payment amount must be greater than zero.');
        }

        if ($amount - $booking->balanceDue() > 0.01) {
            throw new BookingException('The payment amount is more than the balance due.');
        }
    }
}