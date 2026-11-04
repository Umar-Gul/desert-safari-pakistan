<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BookingException;
use App\Enums\BookingStatus;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\Tour;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
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
     * Availability for every calendar day in the requested range. A missing schedule
     * row is explicitly closed, so the picker and backend use the same date rules.
     *
     * @return array<string, array{status:string, remaining:int, price_override:?float}>
     */
    public function dateStatuses(Tour $tour, CarbonInterface $from, CarbonInterface $to): array
    {
        $rows = Availability::query()
            ->where('tour_id', $tour->id)
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->get()
            ->keyBy(fn (Availability $availability) => $availability->date->toDateString());
        $occupied = $this->occupiedByDate([$tour->id], $from, $to);
        $statuses = [];

        foreach (CarbonPeriod::create(
            Carbon::parse($from->toDateString(), config('app.timezone')),
            Carbon::parse($to->toDateString(), config('app.timezone')),
        ) as $day) {
            $date = $day->toDateString();
            $availability = $rows->get($date);
            $key = $tour->id.'|'.$date;
            $reservation = $occupied[$key] ?? null;
            $remaining = $availability
                ? max(0, (int) $availability->capacity - (($reservation['has_bookings'] ?? false) ? $reservation['guests'] : (int) $availability->booked_count))
                : 0;
            $status = $this->dateStatus($tour, $day, $availability, $remaining);

            $statuses[$date] = [
                'status' => $status,
                'remaining' => $remaining,
                'price_override' => $availability?->price_override !== null ? (float) $availability->price_override : null,
            ];
        }

        return $statuses;
    }

    /**
     * Compatibility adapter for code that still needs the legacy calendar shape.
     *
     * @return Collection<string, array{remaining:int, open:bool, closed:bool, price_override:?float}>
     */
    public function calendar(Tour $tour, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return collect($this->dateStatuses($tour, $from, $to))->map(static fn (array $entry) => [
            'remaining' => $entry['remaining'],
            'open' => $entry['status'] === 'available',
            'closed' => $entry['status'] === 'closed',
            'price_override' => $entry['price_override'],
        ]);
    }

    /** Batch status calculation for a listing page; queries remain constant per page. */
    public function tourStatuses(Collection $tours, CarbonInterface $from, CarbonInterface $to): array
    {
        if ($tours->isEmpty()) {
            return [];
        }

        $tourIds = $tours->map(static fn (Tour $tour): int => (int) $tour->getKey())->all();
        $rows = Availability::query()
            ->whereIn('tour_id', $tourIds)
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->get()
            ->keyBy(fn (Availability $availability) => $availability->tour_id.'|'.$availability->date->toDateString());
        $occupied = $this->occupiedByDate($tourIds, $from, $to);

        $statuses = [];
        foreach ($tours as $tour) {
            $hasFullDate = false;
            $status = ['status' => 'closed', 'remaining' => 0];
            if ($tour->is_active) {
                foreach (CarbonPeriod::create(
                    Carbon::parse($from->toDateString(), config('app.timezone')),
                    Carbon::parse($to->toDateString(), config('app.timezone')),
                ) as $day) {
                    $date = $day->toDateString();
                    $availability = $rows->get($tour->id.'|'.$date);
                    $reservation = $occupied[$tour->id.'|'.$date] ?? null;
                    $remaining = $availability
                        ? max(0, (int) $availability->capacity - (($reservation['has_bookings'] ?? false) ? $reservation['guests'] : (int) $availability->booked_count))
                        : 0;
                    $dateStatus = $this->dateStatus($tour, $day, $availability, $remaining);

                    if ($dateStatus === 'available') {
                        $status = ['status' => 'available', 'remaining' => $remaining];
                        break;
                    }
                    if ($dateStatus === 'full') {
                        $hasFullDate = true;
                    }
                }
            }

            if ($status['status'] !== 'available' && $hasFullDate) {
                $status = ['status' => 'full', 'remaining' => 0];
            }
            $statuses[$tour->id] = $status;
        }

        return $statuses;
    }

    /** @return array{status:string, remaining:int} */
    public function tourStatus(Tour $tour, CarbonInterface $from, CarbonInterface $to): array
    {
        return $this->tourStatuses(collect([$tour]), $from, $to)[$tour->id];
    }

    private function dateStatus(Tour $tour, CarbonInterface $date, ?Availability $availability, int $remaining): string
    {
        if (! $tour->is_active || ! $availability || $availability->is_closed || ! $this->isBookingDateAllowed($date)) {
            return 'closed';
        }

        return $remaining <= 0 ? 'full' : 'available';
    }

    /** @param list<int> $tourIds @return array<string, array{guests:int, has_bookings:bool}> */
    private function occupiedByDate(array $tourIds, CarbonInterface $from, CarbonInterface $to): array
    {
        $statuses = $this->capacityStatuses();
        $placeholders = implode(', ', array_fill(0, count($statuses), '?'));

        return Booking::withTrashed()
            ->whereIn('tour_id', $tourIds)
            ->whereDate('tour_date', '>=', $from->toDateString())
            ->whereDate('tour_date', '<=', $to->toDateString())
            ->selectRaw("tour_id, tour_date, COUNT(*) as booking_count, COALESCE(SUM(CASE WHEN status IN ({$placeholders}) THEN adults + children ELSE 0 END), 0) as guests", $statuses)
            ->groupBy('tour_id', 'tour_date')
            ->get()
            ->mapWithKeys(static fn ($row) => [
                $row->tour_id.'|'.Carbon::parse($row->tour_date)->toDateString() => [
                    'guests' => (int) $row->guests,
                    'has_bookings' => (int) $row->booking_count > 0,
                ],
            ])
            ->all();
    }

    private function validate(Tour $tour, CarbonInterface $date, int $guests, bool $lock): Availability
    {
        if (! $tour->is_active) {
            throw new BookingException('This tour is not available for booking.');
        }

        $maximum = min(
            (int) config('booking.max_guests_per_booking', 50),
            $tour->max_group_size ?: (int) config('booking.max_guests_per_booking', 50),
        );
        if ($guests < 1 || $guests > $maximum) {
            throw new BookingException("The selected guest count exceeds this tour's group size limit.");
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

        $bookingRows = Booking::withTrashed()
            ->where('tour_id', $tour->id)
            ->whereDate('tour_date', $date->toDateString())
            ->get(['status', 'adults', 'children']);
        $occupied = $bookingRows->isEmpty()
            ? (int) $availability->booked_count
            : (int) $bookingRows->filter(fn (Booking $booking): bool => $booking->status instanceof BookingStatus
                ? $booking->status->holdsCapacity()
                : in_array((string) $booking->status, $this->capacityStatuses(), true))
                ->sum(fn (Booking $booking): int => (int) $booking->adults + (int) $booking->children);
        if ($lock && $bookingRows->isNotEmpty() && (int) $availability->booked_count !== $occupied) {
            $availability->update(['booked_count' => $occupied]);
        } else {
            $availability->booked_count = $occupied;
        }
        $remaining = max(0, (int) $availability->capacity - $occupied);

        if ($remaining < $guests) {
            throw new BookingException(
                $remaining > 0
                    ? "Only {$remaining} spots are left on this date."
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

        if (! $this->isBookingDateAllowed($day)) {
            throw new BookingException(
                'Same-day bookings close at '.$this->rules->sameDayCutoff().'. Please choose another date or contact us.'
            );
        }
    }

    private function isBookingDateAllowed(CarbonInterface $date): bool
    {
        $day = $date->copy()->startOfDay();

        if ($day->lt(today())) {
            return false;
        }

        if ($day->isSameDay(today())) {
            $cutoff = today()->setTimeFromTimeString($this->rules->sameDayCutoff());

            return now()->lt($cutoff);
        }

        return true;
    }

    /** @return list<string> */
    private function capacityStatuses(): array
    {
        return array_values(array_map(
            static fn (BookingStatus $status): string => $status->value,
            array_filter(BookingStatus::cases(), static fn (BookingStatus $status): bool => $status->holdsCapacity()),
        ));
    }
}
