<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\BookingStatus;
use App\Enums\EnquiryStatus;
use App\Enums\PaymentTransactionStatus;
use App\Enums\ReviewStatus;
use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\Payment;
use App\Models\Question;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

/** Builds dashboard data. Each block is shown only if the user may view that module. */
final class DashboardStats
{
    /**
     * @return array{cards: list<array<string, mixed>>, chart: ?array{labels: list<string>, values: list<int>}, upcoming: ?Collection}
     */
    public function for(User $user): array
    {
        $cards = [];
        $chart = null;
        $upcoming = null;
        $month = [now()->startOfMonth(), now()->endOfMonth()];

        if ($user->can('viewAny', Booking::class)) {
            $cards[] = $this->card(
                'New bookings today',
                Booking::query()->whereDate('created_at', today())->count(),
                'admin.bookings.index',
            );
            $cards[] = $this->card(
                'Bookings this month',
                Booking::query()->whereBetween('created_at', $month)->count(),
                'admin.bookings.index',
            );
            $cards[] = $this->card(
                'Pending bookings',
                Booking::query()->where('status', BookingStatus::Pending->value)->count(),
                'admin.bookings.index',
                ['status' => 'pending'],
                'Waiting for confirmation',
            );
            $cards[] = $this->card(
                'Tours today',
                Booking::query()
                    ->whereDate('tour_date', today())
                    ->whereIn('status', $this->activeStatuses())
                    ->count(),
                'admin.bookings.index',
                [],
                'Pending and confirmed',
            );

            $chart = $this->bookingsChart();
            $upcoming = $this->upcomingBookings();
        }

        if ($user->can('viewAny', Payment::class)) {
            $revenue = (float) Payment::query()
                ->where('status', PaymentTransactionStatus::Completed->value)
                ->whereBetween('paid_at', $month)
                ->sum('amount');

            $cards[] = $this->card(
                'Revenue this month',
                config('booking.currency', 'PKR').' '.number_format($revenue),
                'admin.payments.index',
                [],
                'Completed payments',
            );
        }

        if ($user->can('viewAny', Review::class)) {
            $cards[] = $this->card(
                'New enquiries',
                Enquiry::query()->where('status', EnquiryStatus::New->value)->count(),
                'admin.enquiries.index',
            );
            $cards[] = $this->card(
                'Pending reviews',
                Review::query()->where('status', ReviewStatus::Pending->value)->count(),
                'admin.reviews.index',
            );
            $cards[] = $this->card(
                'Pending questions',
                Question::query()->where('status', ReviewStatus::Pending->value)->count(),
                'admin.questions.index',
            );
        }

        return compact('cards', 'chart', 'upcoming');
    }

    /** @return list<string> */
    private function activeStatuses(): array
    {
        return [BookingStatus::Pending->value, BookingStatus::Confirmed->value];
    }

    /** @return array{labels: list<string>, values: list<int>} */
    private function bookingsChart(int $days = 30): array
    {
        $from = now()->subDays($days - 1)->startOfDay();

        $counts = Booking::query()
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $values = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $from->copy()->addDays($i);
            $labels[] = $date->format('d M');
            $values[] = (int) ($counts[$date->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /** Pending and confirmed bookings starting within the next 7 days. */
    private function upcomingBookings(): Collection
    {
        return Booking::query()
            ->with('tour:id,title')
            ->whereDate('tour_date', '>=', today())
            ->whereDate('tour_date', '<=', today()->addDays(7))
            ->whereIn('status', $this->activeStatuses())
            ->orderBy('tour_date')
            ->orderBy('pickup_time')
            ->limit(8)
            ->get();
    }

    /** @param array<string, mixed> $query */
    private function card(string $label, int|string $value, ?string $route = null, array $query = [], ?string $hint = null): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'hint' => $hint,
            // Linked only once that module's routes exist.
            'href' => $route && Route::has($route) ? route($route, $query) : null,
        ];
    }
}