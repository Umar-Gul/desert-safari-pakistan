<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\CancellationQuote;
use App\DataTransferObjects\QuoteInput;
use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Events\BookingCancelled;
use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Tour;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class BookingService
{
    private const CUSTOMER_FIELDS = [
        'customer_name', 'email', 'phone', 'whatsapp', 'nationality',
        'pickup_time', 'pickup_location', 'pickup_address', 'city', 'special_requests',
    ];

    public function __construct(
        private readonly PricingService $pricing,
        private readonly AvailabilityService $availability,
        private readonly BookingRules $rules,
    ) {}

    /**
     * Create a pending booking. Safe against double submits (idempotency key)
     * and overbooking (row lock on the availability date).
     *
     * @param  array<string, mixed>  $customer
     */
    public function create(
        Tour $tour,
        QuoteInput $input,
        array $customer,
        string $idempotencyKey,
        ?int $userId = null,
        BookingSource $source = BookingSource::Web,
    ): Booking {
        if ($existing = Booking::withTrashed()->where('idempotency_key', $idempotencyKey)->first()) {
            return $existing;
        }

        try {
            $booking = DB::transaction(
                fn () => $this->createInTransaction($tour, $input, $customer, $idempotencyKey, $userId, $source)
            );
        } catch (UniqueConstraintViolationException) {
            // Two identical submits raced: return the one that won.
            return Booking::withTrashed()->where('idempotency_key', $idempotencyKey)->firstOrFail();
        }

        event(new BookingCreated($booking));

        return $booking;
    }

    public function confirm(Booking $booking): Booking
    {
        if ($booking->status !== BookingStatus::Pending) {
            throw new BookingException('Only pending bookings can be confirmed.');
        }

        $booking->update(['status' => BookingStatus::Confirmed]);

        event(new BookingConfirmed($booking));

        return $booking;
    }

    public function complete(Booking $booking): Booking
    {
        if ($booking->status !== BookingStatus::Confirmed) {
            throw new BookingException('Only confirmed bookings can be marked as completed.');
        }

        $booking->update(['status' => BookingStatus::Completed]);

        return $booking;
    }

    /** What cancelling this booking would cost right now. */
    public function cancellationQuote(Booking $booking, bool $waiveFee = false): CancellationQuote
    {
        $start = Carbon::parse($booking->tour_date->toDateString().' '.($booking->pickup_time ?: '00:00'));
        $hours = (float) now()->diffInHours($start, false);

        $isFree = $waiveFee || $hours >= $this->rules->freeCancellationHours();
        $percent = $isFree ? 0.0 : $this->rules->lateCancellationFeePercent();

        return new CancellationQuote(
            isFree: $isFree,
            feePercent: $percent,
            feeAmount: round((float) $booking->grand_total * $percent / 100, 2),
            hoursBeforeStart: $hours,
        );
    }

    /**
     * Cancel a pending/confirmed booking and release its seats.
     * Refunds are recorded separately (PaymentService / admin).
     */
    public function cancel(Booking $booking, string $by = 'customer', ?string $reason = null, bool $waiveFee = false): CancellationQuote
    {
        $quote = DB::transaction(function () use ($booking, $by, $reason, $waiveFee) {
            $locked = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if (! in_array($locked->status, [BookingStatus::Pending, BookingStatus::Confirmed], true)) {
                throw new BookingException('This booking can no longer be cancelled.');
            }

            $quote = $this->cancellationQuote($locked, $waiveFee);

            $note = "Cancelled by {$by}".($reason ? ": {$reason}" : '');

            $locked->update([
                'status' => BookingStatus::Cancelled,
                'cancelled_at' => now(),
                'admin_notes' => trim(($locked->admin_notes ? $locked->admin_notes."\n" : '').$note),
            ]);

            $this->availability->release($locked);

            return $quote;
        });

        $booking->refresh();

        event(new BookingCancelled($booking, $quote));

        return $quote;
    }

    /** "Track my booking": reference plus the email or phone used to book. */
    public function lookup(string $reference, string $contact): ?Booking
    {
        $booking = Booking::query()
            ->with(['tour', 'addons', 'payments'])
            ->where('reference', strtoupper(trim($reference)))
            ->first();

        if (! $booking) {
            return null;
        }

        $contact = trim($contact);
        $digits = fn (?string $value): string => (string) preg_replace('/\D+/', '', (string) $value);

        $emailMatch = strcasecmp((string) $booking->email, $contact) === 0;
        $contactDigits = $digits($contact);
        $phoneMatch = $contactDigits !== ''
            && in_array($contactDigits, [$digits($booking->phone), $digits($booking->whatsapp)], true);

        return ($emailMatch || $phoneMatch) ? $booking : null;
    }

    /** @param array<string, mixed> $customer */
    private function createInTransaction(
        Tour $tour,
        QuoteInput $input,
        array $customer,
        string $idempotencyKey,
        ?int $userId,
        BookingSource $source,
    ): Booking {
        $guests = $input->guests();

        $availability = $this->availability->lockAndCheck($tour, $input->tourDate, $guests);

        // Lock the coupon row so the usage limit holds under concurrency.
        if ($input->couponCode) {
            Coupon::query()->where('code', $input->couponCode)->lockForUpdate()->first();
        }

        $price = $this->pricing->calculate($tour, $input);

        if ($input->couponCode && $price->couponError) {
            throw new BookingException($price->couponError);
        }

        $booking = Booking::create([
            'reference' => $this->generateReference(),
            'idempotency_key' => $idempotencyKey,
            'tour_id' => $tour->id,
            'user_id' => $userId,
            ...Arr::only($customer, self::CUSTOMER_FIELDS),
            'tour_date' => $input->tourDate->toDateString(),
            'adults' => $input->adults,
            'children' => $input->children,
            'camp_type_id' => $input->campTypeId,
            'vehicle_id' => $input->vehicleId,
            'coupon_id' => $price->couponId,
            'subtotal' => $price->subtotal,
            'addons_total' => $price->addonsTotal,
            'discount_total' => $price->discountTotal,
            'tax' => $price->tax,
            'grand_total' => $price->grandTotal,
            'currency' => $price->currency,
            'status' => BookingStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
            'source' => $source,
        ]);

        foreach ($price->addonLines as $line) {
            $booking->addons()->create([
                'addon_id' => $line['addon_id'],
                'name_snapshot' => $line['name'],
                'price_snapshot' => $line['unit_price'],
                'quantity' => $line['quantity'],
            ]);
        }

        $this->availability->reserve($availability, $guests);

        if ($price->couponId) {
            Coupon::query()->whereKey($price->couponId)->increment('used_count');
        }

        return $booking->load('addons');
    }

    private function generateReference(): string
    {
        do {
            $reference = sprintf(
                '%s-%s-%06d',
                $this->rules->referencePrefix(),
                now()->format('Y'),
                random_int(0, 999999)
            );
        } while (Booking::withTrashed()->where('reference', $reference)->exists());

        return $reference;
    }
}