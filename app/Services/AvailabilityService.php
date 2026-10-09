<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\Tour;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AvailabilityService
{
    public function __construct(private readonly BookingRules $rules) {}

    /** Read-only check (live quotes, date pickers). */
    public function check(Tour $tour, CarbonInterface $date, int $guests): Availability
    {
        return $this->validate($tour, $date, $guests, false);
    }

    /** Check and lock the row. Call inside a DB transaction. */
    public function lockAndCheck(Tour $tour, CarbonInterface $date, int $guests): Availability
    {
        return $this->validate($tour, $date, $guests, true);
    }

    public function reserve(Availability $availability, int $guests): void
    {
        $availability->increment('booked_count', $guests);
    }

    /** Give a booking's seats back. Call inside a DB transaction. */
    public function release(Booking $booking): void
    {
        $availability = Availability::query()
            ->where('tour_id', $booking->tour_id)
            ->whereDate('date', $booking->tour_date->toDateString())
            ->lockForUpdate()
            ->first();

        if (! $availability) {
            return;
        }

        $availability->update([
            'booked_count' => max(0, (int) $availability->booked_count - $booking->guests_count),
        ]);
    }

    /**
     * Per-date summary for the booking calendar.
     *
     * @return Collection<string, array{remaining:int, open:bool, price_override:?float}>
     */
    public function calendar(Tour $tour, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return Availability::query()
            ->where('tour_id', $tour->id)
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->orderBy('date')
            ->get()
            ->mapWithKeys(fn (Availability $a) => [
                $a->date->toDateString() => [
                    'remaining' => $a->remaining,
                    'open' => ! $a->is_closed && $a->remaining > 0,
                    'price_override' => $a->price_override !== null ? (float) $a->price_override : null,
                ],
            ]);
    }

    private function validate(Tour $tour, CarbonInterface $date, int $guests, bool $lock): Availability
    {
        if (! $tour->is_active) {
            throw new BookingException('This tour is not available for booking.');
        }

        $this->assertDateAllowed($date);

        $query = Availability::query()
            ->where('tour_id', $tour->id)
            ->whereDate('date', $date->toDateString());

        if ($lock) {
            $query->lockForUpdate();
        }

        $availability = $query->first();

        if (! $availability || $availability->is_closed) {
            throw new BookingException('This tour is not available on the selected date.');
        }

        if ($availability->remaining < $guests) {
            throw new BookingException(
                $availability->remaining > 0
                    ? "Only {$availability->remaining} spots are left on this date."
                    : 'This date is fully booked.'
            );
        }

        return $availability;
    }

    private function assertDateAllowed(CarbonInterface $date): void
    {
        $day = $date->copy()->startOfDay();

        if ($day->lt(today())) {
            throw new BookingException('You cannot book a date in the past.');
        }

        if ($day->isSameDay(today())) {
            $cutoff = today()->setTimeFromTimeString($this->rules->sameDayCutoff());

            if (now()->gte($cutoff)) {
                throw new BookingException(
                    'Same-day bookings close at '.$this->rules->sameDayCutoff().'. Please choose another date or contact us.'
                );
            }
        }
    }
}